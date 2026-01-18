<?php

namespace App\Entity;

use App\Repository\RecuRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecuRepository::class)]
class Recu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'recu')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commande $commande = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $numeroRecu = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateRecu = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $montantTotal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fichierPdf = null;

    public function __construct()
    {
        $this->dateRecu = new \DateTimeImmutable();
        $this->numeroRecu = $this->generateNumeroRecu();
    }

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): self
    {
        $this->commande = $commande;
        return $this;
    }

    public function getNumeroRecu(): ?string
    {
        return $this->numeroRecu;
    }

    public function setNumeroRecu(string $numeroRecu): self
    {
        $this->numeroRecu = $numeroRecu;
        return $this;
    }

    public function getDateRecu(): ?\DateTimeImmutable
    {
        return $this->dateRecu;
    }

    public function setDateRecu(\DateTimeImmutable $dateRecu): self
    {
        $this->dateRecu = $dateRecu;
        return $this;
    }

    public function getMontantTotal(): ?string
    {
        return $this->montantTotal;
    }

    public function setMontantTotal(string $montantTotal): self
    {
        $this->montantTotal = $montantTotal;
        return $this;
    }

    public function getFichierPdf(): ?string
    {
        return $this->fichierPdf;
    }

    public function setFichierPdf(?string $fichierPdf): self
    {
        $this->fichierPdf = $fichierPdf;
        return $this;
    }

    // Méthode pour générer un numéro de reçu unique
    private function generateNumeroRecu(): string
    {
        return 'REC-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    }
}
