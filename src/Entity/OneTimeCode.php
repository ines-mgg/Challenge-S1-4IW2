<?php

namespace App\Entity;

use App\Repository\OneTimeCodeRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Random\RandomException;
use Symfony\Component\Console\Attribute\AsCommand;

#[ORM\Entity(repositoryClass: OneTimeCodeRepository::class)]
class OneTimeCode
{
    public function __construct()
    {
        $this->created_at = new DateTimeImmutable();
        $this->expires_at = new DateTimeImmutable('+ 10minutes');
        $this->used = false;
        $this->code = rand(100000, 999999);
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $code;

    #[ORM\Column]
    private \DateTimeImmutable $expires_at;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private bool $used;

    #[ORM\ManyToOne(inversedBy: 'oneTimeCodes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(int $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expires_at;
    }

    public function setExpiresAt(\DateTimeImmutable $expires_at): static
    {
        $this->expires_at = $expires_at;

        return $this;
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

    public function isUsed(): ?bool
    {
        return $this->used;
    }

    public function setUsed(bool $used): static
    {
        $this->used = $used;

        return $this;
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
}
