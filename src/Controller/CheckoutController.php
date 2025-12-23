<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Recu;
use App\Entity\Ticket;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/checkout')]
#[IsGranted('ROLE_CLIENT')]
class CheckoutController extends AbstractController
{
    #[Route('/', name: 'app_checkout', methods: ['GET', 'POST'])]
    public function index(Request $request, CartService $cartService): Response
    {
        $cartItems = $cartService->getCartWithDetails();
        $total     = $cartService->getTotal();

        if (empty($cartItems)) {
            $this->addFlash('warning', 'Votre panier est vide !');
            return $this->redirectToRoute('app_cart');
        }

        $session = $request->getSession();

        // Récupération des valeurs actuelles (si déjà appliquées)
        $promoCode          = $session->get('promo_code', '');
        $discountRate       = $session->get('discount_rate', 0);
        $discountAmount     = $total * $discountRate;
        $totalAfterDiscount = $total - $discountAmount;

        // Si formulaire soumis (POST)
        if ($request->isMethod('POST')) {
            $inputPromoCode = strtoupper(trim($request->request->get('promo_code', '')));

            // Réinitialiser si vide
            if (empty($inputPromoCode)) {
                $session->remove('promo_code');
                $session->remove('discount_rate');
                $promoCode      = '';
                $discountRate   = 0;
                $discountAmount = 0;
                $totalAfterDiscount = $total;
                $this->addFlash('info', 'Code promo retiré.');
            } else {
                // Vérifier le code promo
                switch ($inputPromoCode) {
                    case 'QATA3_10':
                        $discountRate = 0.10;
                        break;
                    case 'QATA3_20':
                        $discountRate = 0.20;
                        break;
                    case 'QATA3FREE':
                        $discountRate = 1.00;
                        break;
                    default:
                        $discountRate = 0;
                        $this->addFlash('warning', 'Code promo invalide ou expiré.');
                }

                if ($discountRate > 0) {
                    $promoCode          = $inputPromoCode;
                    $discountAmount     = $total * $discountRate;
                    $totalAfterDiscount = $total - $discountAmount;

                    // Stocker en session
                    $session->set('promo_code', $promoCode);
                    $session->set('discount_rate', $discountRate);

                    $this->addFlash('success', sprintf(
                        'Code promo appliqué : %s (-%d%%)',
                        $promoCode,
                        (int)($discountRate * 100)
                    ));
                } else {
                    $session->remove('promo_code');
                    $session->remove('discount_rate');
                    $promoCode = '';
                    $discountAmount = 0;
                    $totalAfterDiscount = $total;
                }
            }
        }

        return $this->render('checkout/index.html.twig', [
            'cart_items'          => $cartItems,
            'total'               => $total,
            'promo_code'          => $promoCode,
            'discount_rate'       => $discountRate,
            'discount_amount'     => $discountAmount,
            'total_after_discount'=> $totalAfterDiscount,
        ]);
    }

    #[Route('/process', name: 'app_checkout_process', methods: ['POST'])]
    public function process(
        Request $request,
        CartService $cartService,
        EntityManagerInterface $em
    ): Response {
        if (!$this->isGranted('ROLE_CLIENT')) {
            throw $this->createAccessDeniedException();
        }

        /** @var Client $user */
        $user = $this->getUser();

        $cartItems = $cartService->getCartWithDetails();
        $total     = $cartService->getTotal();

        if (empty($cartItems)) {
            $this->addFlash('warning', 'Votre panier est vide !');
            return $this->redirectToRoute('app_cart');
        }

        foreach ($cartItems as $item) {
            if ($item['quantite'] > $item['billet']->getQuantiteRestante()) {
                $this->addFlash(
                    'error',
                    'Quantité insuffisante pour ' . $item['billet']->getEvenement()->getTitre()
                );
                return $this->redirectToRoute('app_cart');
            }
        }

        // Récupérer la réduction depuis la session
        $session      = $request->getSession();
        $promoCode    = $session->get('promo_code', '');
        $discountRate = $session->get('discount_rate', 0);

        $discountAmount     = $total * $discountRate;
        $totalAfterDiscount = $total - $discountAmount;

        // Créer la commande avec le montant après réduction
        $commande = new Commande();
        $commande->setClient($user);
        $commande->setMontantTotal((string) $totalAfterDiscount);
        $commande->setStatutCommande('paid_demo');
        $commande->setModePaiement('demo');
        $commande->setEmailAcheteur($user->getEmail());

        // Si tu veux stocker le code promo dans la commande, ajoute un champ `codePromo` dans l'entité
        // $commande->setCodePromo($promoCode);

        $em->persist($commande);

        foreach ($cartItems as $item) {
            $ligneCommande = new LigneCommande();
            $ligneCommande->setCommande($commande);
            $ligneCommande->setTypeBillet($item['billet']);
            $ligneCommande->setQuantite($item['quantite']);
            $ligneCommande->setPrixUnitaire($item['billet']->getPrix());
            $ligneCommande->setSousTotal((string) $item['sousTotal']);

            $em->persist($ligneCommande);

            $item['billet']->setQuantiteRestante(
                $item['billet']->getQuantiteRestante() - $item['quantite']
            );

            for ($i = 0; $i < $item['quantite']; $i++) {
                $ticket = new Ticket();
                $ticket->setLigneCommande($ligneCommande);
                $ticket->setNomTitulaire($user->getNom() . ' ' . $user->getPrenom());

                $qrCodeData = sprintf(
                    'TICKET:%s|EVENT:%s|CLIENT:%s|DATE:%s',
                    $ticket->getCodeTicket(),
                    $item['billet']->getEvenement()->getId(),
                    $user->getId(),
                    date('Y-m-d H:i:s')
                );

                try {
                    $qrCode = new \Endroid\QrCode\QrCode($qrCodeData);
                    $writer = new \Endroid\QrCode\Writer\PngWriter();
                    $result = $writer->write($qrCode);
                    $ticket->setCodeQr(base64_encode($result->getString()));
                } catch (\Exception $e) {
                    $ticket->setCodeQr(base64_encode('TICKET-' . $ticket->getCodeTicket()));
                }

                $em->persist($ticket);
            }
        }

        $recu = new Recu();
        $recu->setCommande($commande);
        $recu->setMontantTotal((string) $totalAfterDiscount);

        $em->persist($recu);
        $em->flush();

        // Nettoyer le panier et la session promo
        $cartService->clear();
        $session->remove('promo_code');
        $session->remove('discount_rate');

        $this->addFlash('success', 'Paiement réussi (mode démonstration) ! Vos tickets sont disponibles.');

        return $this->redirectToRoute('app_checkout_success', ['id' => $commande->getId()]);
    }

    #[Route('/success/{id}', name: 'app_checkout_success')]
    public function success(int $id, EntityManagerInterface $em): Response
    {
        /** @var Client $user */
        $user     = $this->getUser();
        $commande = $em->getRepository(Commande::class)->find($id);

        if (!$commande || $commande->getClient() !== $user) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        return $this->render('checkout/success.html.twig', [
            'commande' => $commande,
        ]);
    }
}
