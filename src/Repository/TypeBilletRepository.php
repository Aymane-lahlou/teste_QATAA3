<?php

namespace App\Repository;

use App\Entity\TypeBillet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TypeBilletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeBillet::class);
    }

    // Trouver billets disponibles pour un événement
    public function findAvailableByEvent(int $eventId): array
    {
        return $this->createQueryBuilder('tb')
            ->where('tb.evenement = :eventId')
            ->andWhere('tb.statut = :statut')
            ->andWhere('tb.quantiteRestante > 0')
            ->setParameter('eventId', $eventId)
            ->setParameter('statut', 'actif')
            ->getQuery()
            ->getResult();
    }

    // Total billets restants
    public function getTotalRemainingTickets(): int
    {
        return $this->createQueryBuilder('tb')
            ->select('SUM(tb.quantiteRestante)')
            ->where('tb.statut = :statut')
            ->setParameter('statut', 'actif')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }
}
