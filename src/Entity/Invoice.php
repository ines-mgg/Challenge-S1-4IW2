<?php

namespace App\Entity;

use App\Repository\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column]
    private ?float $total = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $closing_date = null;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: InvoicePrestation::class)]
    private Collection $invoicePrestations;

    public function __construct()
    {
        $this->invoicePrestations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getClosingDate(): ?\DateTimeInterface
    {
        return $this->closing_date;
    }

    public function setClosingDate(?\DateTimeInterface $closing_date): static
    {
        $this->closing_date = $closing_date;

        return $this;
    }

    /**
     * @return Collection<int, InvoicePrestation>
     */
    public function getInvoicePrestations(): Collection
    {
        return $this->invoicePrestations;
    }

    public function addInvoicePrestation(InvoicePrestation $invoicePrestation): static
    {
        if (!$this->invoicePrestations->contains($invoicePrestation)) {
            $this->invoicePrestations->add($invoicePrestation);
            $invoicePrestation->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoicePrestation(InvoicePrestation $invoicePrestation): static
    {
        if ($this->invoicePrestations->removeElement($invoicePrestation)) {
            // set the owning side to null (unless already changed)
            if ($invoicePrestation->getInvoice() === $this) {
                $invoicePrestation->setInvoice(null);
            }
        }

        return $this;
    }
}
