<?php

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
#[ORM\Table(name: 'subscriptions')]
#[ORM\HasLifecycleCallbacks]
class Subscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'subscriptions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null; // 'subscription' or 'insurance'

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $cost = null;

    #[ORM\Column(length: 50)]
    private ?string $frequency = 'monthly'; // 'monthly' or 'yearly'

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $billingDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isMonthlyCancelable = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $passwordEncrypted = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $websiteUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isActive = true;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $renewalReminder = 7;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'subscription', orphanRemoval: true)]
    private Collection $documents;

    #[ORM\OneToMany(targetEntity: SavingsRecommendation::class, mappedBy: 'subscription', orphanRemoval: true)]
    private Collection $savingsRecommendations;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->documents = new ArrayCollection();
        $this->savingsRecommendations = new ArrayCollection();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getCost(): ?string
    {
        return $this->cost;
    }

    public function setCost(string $cost): static
    {
        $this->cost = $cost;
        return $this;
    }

    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    public function setFrequency(string $frequency): static
    {
        $this->frequency = $frequency;
        return $this;
    }

    public function getBillingDate(): ?int
    {
        return $this->billingDate;
    }

    public function setBillingDate(?int $billingDate): static
    {
        $this->billingDate = $billingDate;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function isMonthlyCancelable(): ?bool
    {
        return $this->isMonthlyCancelable;
    }

    public function setIsMonthlyCancelable(bool $isMonthlyCancelable): static
    {
        $this->isMonthlyCancelable = $isMonthlyCancelable;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getPasswordEncrypted(): ?string
    {
        return $this->passwordEncrypted;
    }

    public function setPasswordEncrypted(?string $passwordEncrypted): static
    {
        $this->passwordEncrypted = $passwordEncrypted;
        return $this;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl(?string $websiteUrl): static
    {
        $this->websiteUrl = $websiteUrl;
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

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getRenewalReminder(): ?int
    {
        return $this->renewalReminder;
    }

    public function setRenewalReminder(int $renewalReminder): static
    {
        $this->renewalReminder = $renewalReminder;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setSubscription($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): static
    {
        if ($this->documents->removeElement($document)) {
            if ($document->getSubscription() === $this) {
                $document->setSubscription(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SavingsRecommendation>
     */
    public function getSavingsRecommendations(): Collection
    {
        return $this->savingsRecommendations;
    }

    public function addSavingsRecommendation(SavingsRecommendation $savingsRecommendation): static
    {
        if (!$this->savingsRecommendations->contains($savingsRecommendation)) {
            $this->savingsRecommendations->add($savingsRecommendation);
            $savingsRecommendation->setSubscription($this);
        }

        return $this;
    }

    public function removeSavingsRecommendation(SavingsRecommendation $savingsRecommendation): static
    {
        if ($this->savingsRecommendations->removeElement($savingsRecommendation)) {
            if ($savingsRecommendation->getSubscription() === $this) {
                $savingsRecommendation->setSubscription(null);
            }
        }

        return $this;
    }

    public function getMonthlyCost(): float
    {
        $cost = (float) $this->cost;
        return $this->frequency === 'yearly' ? round($cost / 12, 2) : $cost;
    }
}
