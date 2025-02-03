<?php

namespace App\Entity;

use App\Repository\ServicesRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
#[ORM\Entity(repositoryClass: ServicesRepository::class)]
class Services
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(length: 255)]
    private ?string $per = null;


    #[ORM\ManyToMany(targetEntity: Reservation::class, mappedBy: 'services')]
    private Collection $reservations;

    /**
     * @var Collection<int, AffordablePackage>
     */
    #[ORM\ManyToMany(targetEntity: AffordablePackage::class, mappedBy: 'services')]
    private Collection $affordablePackages;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->affordablePackages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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

    public function getPer(): ?string{
        return $this->per;
    }

    public function setPer(string $per): static
    {
        $this->per = $per;
        return $this;
    }

    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function setReservations(Collection $reservations): void
    {
        $this->reservations = $reservations;
    }

    /**
     * @return Collection<int, AffordablePackage>
     */
    public function getAffordablePackages(): Collection
    {
        return $this->affordablePackages;
    }

    public function addAffordablePackage(AffordablePackage $affordablePackage): static
    {
        if (!$this->affordablePackages->contains($affordablePackage)) {
            $this->affordablePackages->add($affordablePackage);
            $affordablePackage->addService($this);
        }

        return $this;
    }

    public function removeAffordablePackage(AffordablePackage $affordablePackage): static
    {
        if ($this->affordablePackages->removeElement($affordablePackage)) {
            $affordablePackage->removeService($this);
        }

        return $this;
    }

}
