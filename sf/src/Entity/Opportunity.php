<?php

namespace App\Entity;

use App\Repository\OpportunityRepository;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OpportunityRepository::class)
 */
class Opportunity
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
    private $ticker;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $direction;

    /**
     * @ORM\ManyToOne(targetEntity=Market::class, inversedBy="buyOpportunities", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $buyMarket;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4)
     */
    private $buyPrice;

    /**
     * @ORM\ManyToOne(targetEntity=Market::class, inversedBy="sellOpportunities", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $sellMarket;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4)
     */
    private $sellPrice;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4)
     */
    private $size;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4)
     */
    private $priceDiff;

    /**
     * @ORM\Column(type="datetime")
     */
    private $received;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $logs;

    /**
     * @ORM\OneToOne(targetEntity=Order::class, cascade={"persist"})
     */
    private $buyOrder;

    /**
     * @ORM\OneToOne(targetEntity=Order::class, cascade={"persist"})
     */
    private $sellOrder;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicker(): ?string
    {
        return $this->ticker;
    }

    public function setTicker(string $ticker): self
    {
        $this->ticker = $ticker;

        return $this;
    }

    public function getDirection(): ?string
    {
        return $this->direction;
    }

    public function setDirection(string $direction): self
    {
        $this->direction = $direction;

        return $this;
    }

    public function getBuyMarket(): ?Market
    {
        return $this->buyMarket;
    }

    public function setBuyMarket(?Market $buyMarket): self
    {
        $this->buyMarket = $buyMarket;

        return $this;
    }

    public function getBuyPrice(): ?string
    {
        return $this->buyPrice;
    }

    public function setBuyPrice(string $buyPrice): self
    {
        $this->buyPrice = $buyPrice;

        return $this;
    }

    public function getSellMarket(): ?Market
    {
        return $this->sellMarket;
    }

    public function setSellMarket(?Market $sellMarket): self
    {
        $this->sellMarket = $sellMarket;

        return $this;
    }

    public function getSellPrice(): ?string
    {
        return $this->sellPrice;
    }

    public function setSellPrice(string $sellPrice): self
    {
        $this->sellPrice = $sellPrice;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(string $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getPriceDiff(): ?string
    {
        return $this->priceDiff;
    }

    public function setPriceDiff(string $priceDiff): self
    {
        $this->priceDiff = $priceDiff;

        return $this;
    }

    public function getReceived(): ?\DateTimeInterface
    {
        return $this->received;
    }

    public function setReceived(\DateTimeInterface $received): self
    {
        $this->received = $received;

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

    public function getLogs(): ?string
    {
        return $this->logs;
    }

    public function setLogs(?string $logs): self
    {
        $this->logs = $logs;

        return $this;
    }

    public function getBuyOrder(): ?Order
    {
        return $this->buyOrder;
    }

    public function setBuyOrder(?Order $buyOrder): self
    {
        $this->buyOrder = $buyOrder;

        return $this;
    }

    public function getSellOrder(): ?Order
    {
        return $this->sellOrder;
    }

    public function setSellOrder(?Order $sellOrder): self
    {
        $this->sellOrder = $sellOrder;

        return $this;
    }
}
