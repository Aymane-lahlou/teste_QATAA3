<?php

namespace App\Repository;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    // Trouver ticket par code
    public function findByCode(string $code): ?Ticket
    {
        return $this->createQueryBuilder('t')
            ->where('t.codeTicket = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // Tickets d'un client
    public function findByClient(int $clientId): array
    {
        return $this->createQueryBuilder('t')
            ->join('t.ligneCommande', 'lc')
            ->join('lc.commande', 'c')
            ->where('c.client = :clientId')
            ->setParameter('clientId', $clientId)
            ->orderBy('t.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
