<?php

namespace App\Entity;

use App\Repository\OrdersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrdersRepository::class)]
class Orders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $dateOrder = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, Shop>
     */
    #[ORM\ManyToMany(targetEntity: Shop::class, inversedBy: 'orders')]
    private Collection $shop;



    public function __construct()
    {
        $this->shop = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

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

    public function getDateOrder(): ?\DateTime{
        return $this->dateOrder;
    }

    public function setDateOrder(\DateTime $dateOrder): static
    {
        $this->dateOrder = $dateOrder;
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

    /**
     * @return Collection<int, Shop>
     */
    public function getShop(): Collection
    {
        return $this->shop;
    }

    public function addShop(Shop $shop): static
    {
        if (!$this->shop->contains($shop)) {
            $this->shop->add($shop);
        }

        return $this;
    }

    public function removeShop(Shop $shop): static
    {
        $this->shop->removeElement($shop);

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

}
