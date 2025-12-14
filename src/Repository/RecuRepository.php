<?php

namespace App\Repository;

use App\Entity\Recu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RecuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recu::class);
    }

    // Reçus d'un client
    public function findByClient(int $clientId): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.commande', 'c')
            ->where('c.client = :clientId')
            ->setParameter('clientId', $clientId)
            ->orderBy('r.dateRecu', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
