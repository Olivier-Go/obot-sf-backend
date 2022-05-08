<?php

namespace App\Entity;

use App\Repository\TickerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TickerRepository::class)
 */
class Ticker
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
    private $tvChartTickerId;

    /**
     * @ORM\ManyToOne(targetEntity=Market::class, inversedBy="tickers")
     */
    private $market;

    /**
     * @ORM\OneToMany(targetEntity=Balance::class, mappedBy="ticker")
     */
    private $balances;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $volume;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $last;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $average;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $low;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $high;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated;

    public function __construct()
    {
        $this->balances = new ArrayCollection();
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

    public function getTvChartTickerId(): ?string
    {
        return $this->tvChartTickerId;
    }

    public function setTvChartTickerId(?string $tvChartTickerId): self
    {
        $this->tvChartTickerId = $tvChartTickerId;

        return $this;
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
            $balance->setTicker($this);
        }

        return $this;
    }

    public function removeBalance(Balance $balance): self
    {
        if ($this->balances->removeElement($balance)) {
            // set the owning side to null (unless already changed)
            if ($balance->getTicker() === $this) {
                $balance->setTicker(null);
            }
        }

        return $this;
    }

    public function getVolume(): ?float
    {
        return $this->volume;
    }

    public function setVolume(?float $volume): self
    {
        $this->volume = $volume;

        return $this;
    }

    public function getLast(): ?float
    {
        return $this->last;
    }

    public function setLast(?float $last): self
    {
        $this->last = $last;

        return $this;
    }

    public function getAverage(): ?float
    {
        return $this->average;
    }

    public function setAverage(?float $average): self
    {
        $this->average = $average;

        return $this;
    }

    public function getLow(): ?float
    {
        return $this->low;
    }

    public function setLow(?float $low): self
    {
        $this->low = $low;

        return $this;
    }

    public function getHigh(): ?float
    {
        return $this->high;
    }

    public function setHigh(?float $high): self
    {
        $this->high = $high;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(?\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(?\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }
}
