<?php

namespace App\Entity;

use App\Repository\ProjetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjetRepository::class)]
class Projet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 4000)]
    private ?string $description = null;

    /**
     * @var Collection<int, Language>
     */
    #[ORM\ManyToMany(targetEntity: Language::class, inversedBy: 'projets')]
    private Collection $Language;

    #[ORM\OneToMany(mappedBy: "projet", targetEntity: ImageProjet::class)]
    private Collection $images;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $lienGithub = null;

    #[ORM\Column(length: 255)]
    private ?string $file = null;

    public function __construct()
    {
        $this->Language = new ArrayCollection();
        $this->images = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Language>
     */
    public function getLanguage(): Collection
    {
        return $this->Language;
    }

    public function addLanguage(Language $language): static
    {
        if (!$this->Language->contains($language)) {
            $this->Language->add($language);
        }

        return $this;
    }

    public function removeLanguage(Language $language): static
    {
        $this->Language->removeElement($language);

        return $this;
    }

    public function getImages(): Collection
    {
        return $this->images;
    }


    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getLienGithub(): ?string
    {
        return $this->lienGithub;
    }

    public function setLienGithub(?string $lienGithub): static
    {
        $this->lienGithub = $lienGithub;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(string $file): static
    {
        $this->file = $file;

        return $this;
    }
}
