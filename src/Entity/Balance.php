<?php

namespace App\Entity;

use App\Repository\BalanceRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\UX\Turbo\Attribute\Broadcast;

/**
 * @Broadcast()
 * @ORM\Entity(repositoryClass=BalanceRepository::class)
 */
class Balance
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"log"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Market::class, inversedBy="balances")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"log"})
     */
    private $market;

    /**
     * @ORM\ManyToOne(targetEntity=Ticker::class, inversedBy="balances")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"log"})
     */
    private $ticker;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"log"})
     */
    private $currency;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"log"})
     */
    private $total;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"log"})
     */
    private $available;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"log"})
     */
    private $hold;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     * @Groups({"log"})
     */
    private $created;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     * @Groups({"log"})
     */
    private $updated;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
    return 'Market: '.strtoupper($this->getMarket()->getName()).' / Currency: '.$this->getCurrency().' / Total: '.($this->getTotal() ? $this->getTotal() : 0).' / Available: '.($this->getAvailable() ? $this->getAvailable() : 0).' / Hold: '.($this->getHold() ? $this->getHold() : 0);
    }

    public function getMarket(): ?Market
    {
        return $this->market;
    }

    public function setMarket(?Market $market): self
    {
        $this->market = $market;

        return $this;
    }

    public function getTicker(): ?Ticker
    {
        return $this->ticker;
    }

    public function setTicker(?Ticker $ticker): self
    {
        $this->ticker = $ticker;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total = 0): self
    {
        $this->total = $total;

        return $this;
    }

    public function getAvailable(): ?float
    {
        return $this->available;
    }

    public function setAvailable(?float $available = 0): self
    {
        $this->available = $available;

        return $this;
    }

    public function getHold(): ?float
    {
        return $this->hold;
    }

    public function setHold(?float $hold = 0): self
    {
        $this->hold = $hold;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }
}
