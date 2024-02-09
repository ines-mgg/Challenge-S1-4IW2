<?php

namespace App\Entity;

use App\Repository\PrestationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrestationRepository::class)]
class Prestation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 45)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\ManyToOne(inversedBy: 'prestations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?float $tva = null;

    #[ORM\OneToMany(mappedBy: 'prestation', targetEntity: InvoicePrestation::class)]
    private Collection $invoicePrestations;

    public function __construct()
    {
        $this->invoicePrestations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTva(): ?float
    {
        return $this->tva;
    }

    public function setTva(?float $tva): static
    {
        $this->tva = $tva;

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
            $invoicePrestation->setPrestation($this);
        }

        return $this;
    }

    public function removeInvoicePrestation(InvoicePrestation $invoicePrestation): static
    {
        if ($this->invoicePrestations->removeElement($invoicePrestation)) {
            // set the owning side to null (unless already changed)
            if ($invoicePrestation->getPrestation() === $this) {
                $invoicePrestation->setPrestation(null);
            }
        }

        return $this;
    }
}
