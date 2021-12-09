<?php

namespace App\Entity;

use App\Repository\TickerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TickerRepository::class)
 */
class Ticker
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
    private $tvChartTickerId;

    /**
     * @ORM\ManyToOne(targetEntity=Market::class, inversedBy="tickers")
     */
    private $market;

    /**
     * @ORM\OneToMany(targetEntity=Opportunity::class, mappedBy="ticker")
     */
    private $opportunities;

    public function __construct()
    {
        $this->opportunities = new ArrayCollection();
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
            $opportunity->setTicker($this);
        }

        return $this;
    }

    public function removeOpportunity(Opportunity $opportunity): self
    {
        if ($this->opportunities->removeElement($opportunity)) {
            // set the owning side to null (unless already changed)
            if ($opportunity->getTicker() === $this) {
                $opportunity->setTicker(null);
            }
        }

        return $this;
    }
}
