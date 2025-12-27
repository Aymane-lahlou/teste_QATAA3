<?php

namespace App\Command;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:cleanup:duplicate-emails', description: 'Remove duplicate email entries from clients')]
class CleanupDuplicateEmailsCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Nettoyage des emails en doublons...</info>');

        $em = $this->entityManager;
        $repository = $em->getRepository(Client::class);

        // Récupérer tous les clients
        $clients = $repository->findAll();
        $emailCounts = [];
        $duplicates = [];

        // Compter les occurrences de chaque email
        foreach ($clients as $client) {
            $email = $client->getEmail();
            if (!isset($emailCounts[$email])) {
                $emailCounts[$email] = [];
            }
            $emailCounts[$email][] = $client->getId();
        }

        // Identifier les doublons
        foreach ($emailCounts as $email => $ids) {
            if (count($ids) > 1) {
                $duplicates[$email] = $ids;
            }
        }

        if (empty($duplicates)) {
            $output->writeln('<info>✅ Aucun email en doublon trouvé</info>');
            return Command::SUCCESS;
        }

        // Supprimer les doublons (garder le premier)
        foreach ($duplicates as $email => $ids) {
            $output->writeln("<comment>Email en doublon: $email (IDs: " . implode(', ', $ids) . ")</comment>");
            
            // Garder le premier, supprimer les autres
            $toKeep = array_shift($ids);
            
            foreach ($ids as $idToDelete) {
                $client = $repository->find($idToDelete);
                if ($client) {
                    $em->remove($client);
                    $output->writeln("  <info>Suppression du client ID $idToDelete</info>");
                }
            }
        }

        $em->flush();
        $output->writeln('<info>✅ Nettoyage terminé avec succès</info>');

        return Command::SUCCESS;
    }
}
