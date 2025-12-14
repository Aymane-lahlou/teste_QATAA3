<?php

namespace App\Controller;

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
    #[Route('/', name: 'app_checkout')]
    public function index(CartService $cartService): Response
    {
        $cartItems = $cartService->getCartWithDetails();
        $total = $cartService->getTotal();

        if (empty($cartItems)) {
            $this->addFlash('warning', 'Votre panier est vide !');
            return $this->redirectToRoute('app_cart');
        }

        return $this->render('checkout/index.html.twig', [
            'cart_items' => $cartItems,
            'total' => $total,
        ]);
    }

    #[Route('/process', name: 'app_checkout_process', methods: ['POST'])]
    public function process(
        Request $request,
        CartService $cartService,
        EntityManagerInterface $em
    ): Response {
        $cartItems = $cartService->getCartWithDetails();
        $total = $cartService->getTotal();

        if (empty($cartItems)) {
            $this->addFlash('warning', 'Votre panier est vide !');
            return $this->redirectToRoute('app_cart');
        }

        // Vérifier les quantités disponibles
        foreach ($cartItems as $item) {
            if ($item['quantite'] > $item['billet']->getQuantiteRestante()) {
                $this->addFlash('error', 'Quantité insuffisante pour ' . $item['billet']->getEvenement()->getTitre());
                return $this->redirectToRoute('app_cart');
            }
        }

        // Créer la commande
        $commande = new Commande();
        $commande->setClient($this->getUser());
        $commande->setMontantTotal((string)$total);
        $commande->setStatutCommande('paid_demo');
        $commande->setModePaiement('demo');
        $commande->setEmailAcheteur($this->getUser()->getEmail());

        $em->persist($commande);

        // Créer les lignes de commande et les tickets
        foreach ($cartItems as $item) {
            $ligneCommande = new LigneCommande();
            $ligneCommande->setCommande($commande);
            $ligneCommande->setTypeBillet($item['billet']);
            $ligneCommande->setQuantite($item['quantite']);
            $ligneCommande->setPrixUnitaire($item['billet']->getPrix());
            $ligneCommande->setSousTotal((string)$item['sousTotal']);

            $em->persist($ligneCommande);

            // Décrémenter la quantité restante
            $item['billet']->setQuantiteRestante(
                $item['billet']->getQuantiteRestante() - $item['quantite']
            );

            // Générer les tickets
            for ($i = 0; $i < $item['quantite']; $i++) {
                $ticket = new Ticket();
                $ticket->setLigneCommande($ligneCommande);
                $ticket->setNomTitulaire($this->getUser()->getNom() . ' ' . $this->getUser()->getPrenom());
                
                // Générer QR Code
                $qrCodeData = sprintf(
                    'TICKET:%s|EVENT:%s|CLIENT:%s|DATE:%s',
                    $ticket->getCodeTicket(),
                    $item['billet']->getEvenement()->getId(),
                    $this->getUser()->getId(),
                    date('Y-m-d H:i:s')
                );
                
                try {
                    // Version simplifiée
                    $qrCode = new \Endroid\QrCode\QrCode($qrCodeData);
                    $writer = new \Endroid\QrCode\Writer\PngWriter();
                    $result = $writer->write($qrCode);
                    
                    $ticket->setCodeQr(base64_encode($result->getString()));
                } catch (\Exception $e) {
                    // Fallback simple
                    $ticket->setCodeQr(base64_encode('TICKET-' . $ticket->getCodeTicket()));
                }
                
                $em->persist($ticket);
            }
        }

        // Créer le reçu
        $recu = new Recu();
        $recu->setCommande($commande);
        $recu->setMontantTotal((string)$total);

        $em->persist($recu);
        $em->flush();

        // Vider le panier
        $cartService->clear();

        $this->addFlash('success', 'Paiement réussi (mode démonstration) ! Vos tickets sont disponibles.');

        return $this->redirectToRoute('app_checkout_success', ['id' => $commande->getId()]);
    }

    #[Route('/success/{id}', name: 'app_checkout_success')]
    public function success(int $id, EntityManagerInterface $em): Response
    {
        $commande = $em->getRepository(Commande::class)->find($id);

        if (!$commande || $commande->getClient() !== $this->getUser()) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        return $this->render('checkout/success.html.twig', [
            'commande' => $commande,
        ]);
    }
}
