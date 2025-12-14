<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LigneCommande $ligneCommande = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $codeTicket = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $codeQr = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomTitulaire = null;

    #[ORM\Column(length: 20)]
    private ?string $statutTicket = 'valide';

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCreation = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
        $this->codeTicket = $this->generateUniqueCode();
    }

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLigneCommande(): ?LigneCommande
    {
        return $this->ligneCommande;
    }

    public function setLigneCommande(?LigneCommande $ligneCommande): self
    {
        $this->ligneCommande = $ligneCommande;
        return $this;
    }

    public function getCodeTicket(): ?string
    {
        return $this->codeTicket;
    }

    public function setCodeTicket(string $codeTicket): self
    {
        $this->codeTicket = $codeTicket;
        return $this;
    }

    public function getCodeQr(): ?string
    {
        return $this->codeQr;
    }

    public function setCodeQr(string $codeQr): self
    {
        $this->codeQr = $codeQr;
        return $this;
    }

    public function getNomTitulaire(): ?string
    {
        return $this->nomTitulaire;
    }

    public function setNomTitulaire(?string $nomTitulaire): self
    {
        $this->nomTitulaire = $nomTitulaire;
        return $this;
    }

    public function getStatutTicket(): ?string
    {
        return $this->statutTicket;
    }

    public function setStatutTicket(string $statutTicket): self
    {
        $this->statutTicket = $statutTicket;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    // Méthode pour générer un code unique
    private function generateUniqueCode(): string
    {
        return 'TKT-' . strtoupper(uniqid() . bin2hex(random_bytes(4)));
    }
}
