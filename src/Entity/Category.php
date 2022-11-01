<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 20)]
    private ?string $iconKey = null;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: FortuneCookie::class)]
    private Collection $fortuneCookies;

    public function __construct()
    {
        $this->fortuneCookies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIconKey(): ?string
    {
        return $this->iconKey;
    }

    public function setIconKey(string $iconKey): self
    {
        $this->iconKey = $iconKey;

        return $this;
    }

    /**
     * @return Collection<int, FortuneCookie>
     */
    public function getFortuneCookies(): Collection
    {
        return $this->fortuneCookies;
    }

    public function addFortuneCookie(FortuneCookie $fortuneCookie): self
    {
        if (!$this->fortuneCookies->contains($fortuneCookie)) {
            $this->fortuneCookies->add($fortuneCookie);
            $fortuneCookie->setCategory($this);
        }

        return $this;
    }

    public function removeFortuneCookie(FortuneCookie $fortuneCookie): self
    {
        if ($this->fortuneCookies->removeElement($fortuneCookie)) {
            // set the owning side to null (unless already changed)
            if ($fortuneCookie->getCategory() === $this) {
                $fortuneCookie->setCategory(null);
            }
        }

        return $this;
    }
}
