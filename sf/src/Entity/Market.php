<?php

namespace App\Entity;

use App\Repository\MarketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MarketRepository::class)
 */
class Market
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $apiKey;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $apiSecret;

    /**
     * @ORM\OneToMany(targetEntity=Ticker::class, mappedBy="market")
     */
    private $tickers;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $apiPassword;

    /**
     * @ORM\OneToMany(targetEntity=Opportunity::class, mappedBy="buyMarket")
     */
    private $opportunities;

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="market")
     */
    private $orders;

    public function __construct()
    {
        $this->tickers = new ArrayCollection();
        $this->opportunities = new ArrayCollection();
        $this->orders = new ArrayCollection();
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

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getApiSecret(): ?string
    {
        return $this->apiSecret;
    }

    public function setApiSecret(?string $apiSecret): self
    {
        $this->apiSecret = $apiSecret;

        return $this;
    }

    /**
     * @return Collection|Ticker[]
     */
    public function getTickers(): Collection
    {
        return $this->tickers;
    }

    public function addTicker(Ticker $ticker): self
    {
        if (!$this->tickers->contains($ticker)) {
            $this->tickers[] = $ticker;
            $ticker->setMarket($this);
        }

        return $this;
    }

    public function removeTicker(Ticker $ticker): self
    {
        if ($this->tickers->removeElement($ticker)) {
            // set the owning side to null (unless already changed)
            if ($ticker->getMarket() === $this) {
                $ticker->setMarket(null);
            }
        }

        return $this;
    }

    public function getApiPassword(): ?string
    {
        return $this->apiPassword;
    }

    public function setApiPassword(?string $apiPassword): self
    {
        $this->apiPassword = $apiPassword;

        return $this;
    }

    /**
     * @return Collection|Opportunity[]
     */
    public function getOpportunities(): Collection
    {
        return $this->opportunities;
    }

    public function addOpportunity(Opportunity $opportunity): self
    {
        if (!$this->opportunities->contains($opportunity)) {
            $this->opportunities[] = $opportunity;
            $opportunity->setBuyMarket($this);
        }

        return $this;
    }

    public function removeOpportunity(Opportunity $opportunity): self
    {
        if ($this->opportunities->removeElement($opportunity)) {
            // set the owning side to null (unless already changed)
            if ($opportunity->getBuyMarket() === $this) {
                $opportunity->setBuyMarket(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setMarket($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getMarket() === $this) {
                $order->setMarket(null);
            }
        }

        return $this;
    }
}
