<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $logo = null;

    #[ORM\Column]
    private ?int $TVA = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $license_validity = null;

    #[ORM\Column]
    private ?int $id_license = null;

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

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getTVA(): ?int
    {
        return $this->TVA;
    }

    public function setTVA(int $TVA): static
    {
        $this->TVA = $TVA;

        return $this;
    }

    public function getLicenseValidity(): ?\DateTimeInterface
    {
        return $this->license_validity;
    }

    public function setLicenseValidity(\DateTimeInterface $license_validity): static
    {
        $this->license_validity = $license_validity;

        return $this;
    }

    public function getIdLicense(): ?int
    {
        return $this->id_license;
    }

    public function setIdLicense(int $id_license): static
    {
        $this->id_license = $id_license;

        return $this;
    }
}
