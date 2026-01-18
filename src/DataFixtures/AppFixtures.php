<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Evenement;
use App\Entity\TypeBillet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Créer Responsable
        $responsable = new Client();
        $responsable->setNom('Admin')
            ->setPrenom('Responsable')
            ->setEmail('responsable@qataa3.ma')
            ->setMotDePasse($this->passwordHasher->hashPassword($responsable, 'responsable123'))
            ->setTelephone('0612345678')
            ->setStatutCompte('actif')
            ->setRoles(['ROLE_RESPONSABLE']);
        $manager->persist($responsable);

        // Créer Owner
        $owner = new Client();
        $owner->setNom('Owner')
            ->setPrenom('Principal')
            ->setEmail('owner@qataa3.ma')
            ->setMotDePasse($this->passwordHasher->hashPassword($owner, 'owner123'))
            ->setTelephone('0612345679')
            ->setStatutCompte('actif')
            ->setRoles(['ROLE_OWNER']);
        $manager->persist($owner);

        // Créer Client test
        $client = new Client();
        $client->setNom('Alami')
            ->setPrenom('Hassan')
            ->setEmail('client@test.ma')
            ->setMotDePasse($this->passwordHasher->hashPassword($client, 'client123'))
            ->setTelephone('0612345680')
            ->setStatutCompte('actif');
        $manager->persist($client);

        // Créer Événements
        $event1 = new Evenement();
        $event1->setTitre('الصوماق الجنراص')
            ->setDescription('Festival of Lights - Concert exceptionnel')
            ->setDateEvenement(new \DateTime('2024-11-30 20:00:00'))
            ->setLieu('Théâtre Mohammed V, Rabat')
            ->setCategorie('Concert')
            ->setStatut('actif')
            ->setImage('event1.jpg');
        $manager->persist($event1);

        $event2 = new Evenement();
        $event2->setTitre('Tech Conference 2024')
            ->setDescription('Conférence sur les nouvelles technologies')
            ->setDateEvenement(new \DateTime('2025-01-15 09:00:00'))
            ->setLieu('Centre de conférences, Casablanca')
            ->setCategorie('Conférence')
            ->setStatut('actif')
            ->setImage('event2.jpg');
        $manager->persist($event2);

        $event3 = new Evenement();
        $event3->setTitre('Festival Mawazine')
            ->setDescription('Plus grand festival de musique au Maroc')
            ->setDateEvenement(new \DateTime('2025-06-20 18:00:00'))
            ->setLieu('Scène OLM Souissi, Rabat')
            ->setCategorie('Festival')
            ->setStatut('actif')
            ->setImage('event3.jpg');
        $manager->persist($event3);

        // Créer Types de Billets pour event1
        $standardEvent1 = new TypeBillet();
        $standardEvent1->setEvenement($event1)
            ->setNomType('Standard')
            ->setPrix('200.00')
            ->setQuantiteTotale(500)
            ->setQuantiteRestante(500)
            ->setStatut('actif');
        $manager->persist($standardEvent1);

        $vipEvent1 = new TypeBillet();
        $vipEvent1->setEvenement($event1)
            ->setNomType('VIP')
            ->setPrix('500.00')
            ->setQuantiteTotale(100)
            ->setQuantiteRestante(100)
            ->setStatut('actif');
        $manager->persist($vipEvent1);

        // Créer Types de Billets pour event2
        $standardEvent2 = new TypeBillet();
        $standardEvent2->setEvenement($event2)
            ->setNomType('Standard')
            ->setPrix('150.00')
            ->setQuantiteTotale(300)
            ->setQuantiteRestante(300)
            ->setStatut('actif');
        $manager->persist($standardEvent2);

        $gratuitEvent2 = new TypeBillet();
        $gratuitEvent2->setEvenement($event2)
            ->setNomType('Gratuit')
            ->setPrix('0.00')
            ->setQuantiteTotale(50)
            ->setQuantiteRestante(50)
            ->setStatut('actif');
        $manager->persist($gratuitEvent2);

        // Créer Types de Billets pour event3
        $standardEvent3 = new TypeBillet();
        $standardEvent3->setEvenement($event3)
            ->setNomType('Standard')
            ->setPrix('300.00')
            ->setQuantiteTotale(1000)
            ->setQuantiteRestante(1000)
            ->setStatut('actif');
        $manager->persist($standardEvent3);

        $vipEvent3 = new TypeBillet();
        $vipEvent3->setEvenement($event3)
            ->setNomType('VIP')
            ->setPrix('800.00')
            ->setQuantiteTotale(200)
            ->setQuantiteRestante(200)
            ->setStatut('actif');
        $manager->persist($vipEvent3);

        $manager->flush();
    }
}
