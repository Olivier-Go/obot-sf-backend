<?php

namespace App\Entity;

use App\Repository\MarketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=MarketRepository::class)
 */
class Market
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"log"})
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
    private $buyOpportunities;

    /**
     * @ORM\OneToMany(targetEntity=Opportunity::class, mappedBy="sellMarket")
     */
    private $sellOpportunities;

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="market")
     */
    private $orders;

    /**
     * @ORM\OneToMany(targetEntity=Balance::class, mappedBy="market")
     */
    private $balances;

    public function __construct()
    {
        $this->tickers = new ArrayCollection();
        $this->buyOpportunities = new ArrayCollection();
        $this->sellOpportunities = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->balances = new ArrayCollection();
    }

    public function __toString(): ?string
    {
        return $this->name;
    }

    public function upperName(): ?string
    {
        return strtoupper($this->name);
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
    public function getBuyOpportunities(): Collection
    {
        return $this->buyOpportunities;
    }

    public function addBuyOpportunity(Opportunity $buyOpportunities): self
    {
        if (!$this->buyOpportunities->contains($buyOpportunities)) {
            $this->buyOpportunities[] = $buyOpportunities;
            $buyOpportunities->setBuyMarket($this);
        }

        return $this;
    }

    public function removeBuyOpportunity(Opportunity $buyOpportunity): self
    {
        if ($this->buyOpportunities->removeElement($buyOpportunity)) {
            // set the owning side to null (unless already changed)
            if ($buyOpportunity->getBuyMarket() === $this) {
                $buyOpportunity->setBuyMarket(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Opportunity[]
     */
    public function getSellOpportunities(): Collection
    {
        return $this->sellOpportunities;
    }

    public function addSellOpportunity(Opportunity $sellOpportunities): self
    {
        if (!$this->sellOpportunities->contains($sellOpportunities)) {
            $this->sellOpportunities[] = $sellOpportunities;
            $sellOpportunities->setSellMarket($this);
        }

        return $this;
    }

    public function removeSellOpportunity(Opportunity $sellOpportunity): self
    {
        if ($this->sellOpportunities->removeElement($sellOpportunity)) {
            // set the owning side to null (unless already changed)
            if ($sellOpportunity->getSellMarket() === $this) {
                $sellOpportunity->setSellMarket(null);
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

    /**
     * @return Collection|Balance[]
     */
    public function getBalances(): Collection
    {
        return $this->balances;
    }

    public function addBalance(Balance $balance): self
    {
        if (!$this->balances->contains($balance)) {
            $this->balances[] = $balance;
            $balance->setMarket($this);
        }

        return $this;
    }

    public function removeBalance(Balance $balance): self
    {
        if ($this->balances->removeElement($balance)) {
            // set the owning side to null (unless already changed)
            if ($balance->getMarket() === $this) {
                $balance->setMarket(null);
            }
        }

        return $this;
    }
}
