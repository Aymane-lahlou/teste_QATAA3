<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

class ClientRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Client) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setMotDePasse($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    // Trouver les clients actifs
    public function findActiveClients(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.statutCompte = :statut')
            ->setParameter('statut', 'actif')
            ->orderBy('c.dateInscription', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // Statistiques - Nombre de clients inscrits aujourd'hui
    public function countTodayRegistrations(): int
    {
        $today = new \DateTime('today');
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateInscription >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
