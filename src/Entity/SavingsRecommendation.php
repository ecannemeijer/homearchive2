<?php

namespace App\Entity;

use App\Repository\SavingsRecommendationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SavingsRecommendationRepository::class)]
#[ORM\Table(name: 'savings_recommendations')]
class SavingsRecommendation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Subscription::class, inversedBy: 'savingsRecommendations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Subscription $subscription = null;

    #[ORM\ManyToOne(targetEntity: Offer::class, inversedBy: 'savingsRecommendations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Offer $offer = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $monthlySavings = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $yearlySavings = null;

    #[ORM\Column(length: 50)]
    private ?string $status = 'pending'; // pending, accepted, rejected, expired

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $recommendedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $respondedAt = null;

    public function __construct()
    {
        $this->recommendedAt = new \DateTime();
        $this->status = 'pending';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): static
    {
        $this->subscription = $subscription;
        return $this;
    }

    public function getOffer(): ?Offer
    {
        return $this->offer;
    }

    public function setOffer(?Offer $offer): static
    {
        $this->offer = $offer;
        return $this;
    }

    public function getMonthlySavings(): ?string
    {
        return $this->monthlySavings;
    }

    public function setMonthlySavings(string $monthlySavings): static
    {
        $this->monthlySavings = $monthlySavings;
        return $this;
    }

    public function getYearlySavings(): ?string
    {
        return $this->yearlySavings;
    }

    public function setYearlySavings(string $yearlySavings): static
    {
        $this->yearlySavings = $yearlySavings;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getRecommendedAt(): ?\DateTimeInterface
    {
        return $this->recommendedAt;
    }

    public function setRecommendedAt(\DateTimeInterface $recommendedAt): static
    {
        $this->recommendedAt = $recommendedAt;
        return $this;
    }

    public function getRespondedAt(): ?\DateTimeInterface
    {
        return $this->respondedAt;
    }

    public function setRespondedAt(?\DateTimeInterface $respondedAt): static
    {
        $this->respondedAt = $respondedAt;
        return $this;
    }
}
