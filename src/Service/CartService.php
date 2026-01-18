<?php

namespace App\Service;

use App\Entity\TypeBillet;
use App\Repository\TypeBilletRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private RequestStack $requestStack;
    private TypeBilletRepository $typeBilletRepository;

    public function __construct(RequestStack $requestStack, TypeBilletRepository $typeBilletRepository)
    {
        $this->requestStack = $requestStack;
        $this->typeBilletRepository = $typeBilletRepository;
    }

    public function add(int $billetId, int $quantite = 1): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        if (isset($cart[$billetId])) {
            $cart[$billetId] += $quantite;
        } else {
            $cart[$billetId] = $quantite;
        }

        $session->set('cart', $cart);
    }

    public function remove(int $billetId): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        if (isset($cart[$billetId])) {
            unset($cart[$billetId]);
        }

        $session->set('cart', $cart);
    }

    public function updateQuantity(int $billetId, int $quantite): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        if ($quantite > 0) {
            $cart[$billetId] = $quantite;
        } else {
            unset($cart[$billetId]);
        }

        $session->set('cart', $cart);
    }

    public function getCartWithDetails(): array
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        $cartWithDetails = [];

        foreach ($cart as $billetId => $quantite) {
            $billet = $this->typeBilletRepository->find($billetId);
            if ($billet) {
                $cartWithDetails[] = [
                    'billet' => $billet,
                    'quantite' => $quantite,
                    'sousTotal' => $billet->getPrix() * $quantite,
                ];
            }
        }

        return $cartWithDetails;
    }

    public function getTotal(): float
    {
        $cartWithDetails = $this->getCartWithDetails();
        $total = 0;

        foreach ($cartWithDetails as $item) {
            $total += $item['sousTotal'];
        }

        return $total;
    }

    public function getItemCount(): int
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        
        return array_sum($cart);
    }

    public function clear(): void
    {
        $session = $this->requestStack->getSession();
        $session->remove('cart');
    }
}
