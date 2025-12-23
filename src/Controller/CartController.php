<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart')]
#[IsGranted('ROLE_CLIENT')]
class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart', methods: ['GET', 'POST'])]
    public function index(Request $request, CartService $cartService): Response
    {
        $cartItems = $cartService->getCartWithDetails();
        $total     = $cartService->getTotal();

        $session = $request->getSession();

        // Récupération des valeurs actuelles
        $promoCode          = $session->get('promo_code', '');
        $discountRate       = $session->get('discount_rate', 0);
        $discountAmount     = $total * $discountRate;
        $totalAfterDiscount = $total - $discountAmount;

        // Si formulaire promo soumis (POST)
        if ($request->isMethod('POST') && $request->request->has('promo_code')) {
            $inputPromoCode = strtoupper(trim($request->request->get('promo_code', '')));

            // Si champ vide, retirer le code
            if (empty($inputPromoCode)) {
                $session->remove('promo_code');
                $session->remove('discount_rate');
                $promoCode          = '';
                $discountRate       = 0;
                $discountAmount     = 0;
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
                    $promoCode          = '';
                    $discountAmount     = 0;
                    $totalAfterDiscount = $total;
                }
            }
        }

        return $this->render('cart/index.html.twig', [
            'cart_items'          => $cartItems,
            'total'               => $total,
            'promo_code'          => $promoCode,
            'discount_rate'       => $discountRate,
            'discount_amount'     => $discountAmount,
            'total_after_discount'=> $totalAfterDiscount,
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(int $id, Request $request, CartService $cartService): Response
    {
        $quantite = $request->request->getInt('quantite', 1);
        $cartService->add($id, $quantite);

        $this->addFlash('success', 'Billet ajouté au panier !');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/remove/{id}', name: 'app_cart_remove')]
    public function remove(int $id, CartService $cartService): Response
    {
        $cartService->remove($id);
        $this->addFlash('success', 'Billet retiré du panier !');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(int $id, Request $request, CartService $cartService): Response
    {
        $quantite = $request->request->getInt('quantite');
        $cartService->updateQuantity($id, $quantite);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/clear', name: 'app_cart_clear')]
    public function clear(CartService $cartService, Request $request): Response
    {
        $cartService->clear();
        
        // Retirer aussi le code promo
        $session = $request->getSession();
        $session->remove('promo_code');
        $session->remove('discount_rate');
        
        $this->addFlash('success', 'Panier vidé !');

        return $this->redirectToRoute('app_cart');
    }
}
