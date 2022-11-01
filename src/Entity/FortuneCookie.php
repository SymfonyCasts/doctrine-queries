<?php

namespace App\Entity;

use App\Repository\FortuneCookieRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FortuneCookieRepository::class)]
class FortuneCookie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $fortune = null;

    #[ORM\Column]
    private ?int $numberPrinted = null;

    #[ORM\Column]
    private bool $discontinued = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(inversedBy: 'fortuneCookies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    /**
     * @param \DateTimeImmutable|null $createdAt
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFortune(): ?string
    {
        return $this->fortune;
    }

    public function setFortune(string $fortune): self
    {
        $this->fortune = $fortune;

        return $this;
    }

    public function getNumberPrinted(): ?int
    {
        return $this->numberPrinted;
    }

    public function setNumberPrinted(int $numberPrinted): self
    {
        $this->numberPrinted = $numberPrinted;

        return $this;
    }

    public function isDiscontinued(): bool
    {
        return $this->discontinued;
    }

    public function setDiscontinued(bool $discontinued): self
    {
        $this->discontinued = $discontinued;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
