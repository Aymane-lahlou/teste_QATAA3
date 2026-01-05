<?php
// src/Repository/EventRepository.php
namespace App\Repository;

use App\Entity\Event;
use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * Compte les événements futurs pour une ville
     */
    public function countUpcomingByCity(City $city): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.city = :city')
            ->andWhere('e.startDate >= :now')
            ->andWhere('e.status = :status')
            ->setParameter('city', $city)
            ->setParameter('now', new \DateTime())
            ->setParameter('status', 'published')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve tous les événements futurs par ville
     */
    public function findUpcomingByCity(City $city): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.city = :city')
            ->andWhere('e.startDate >= :now')
            ->andWhere('e.status = :status')
            ->setParameter('city', $city)
            ->setParameter('now', new \DateTime())
            ->setParameter('status', 'published')
            ->orderBy('e.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
