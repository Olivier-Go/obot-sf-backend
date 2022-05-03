<?php

namespace App\Entity;

use App\Repository\ParameterRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ParameterRepository::class)
 */
class Parameter
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $workerOrderDiff;

    /**
     * @ORM\Column(type="integer")
     */
    private $workerOrderSize;

    /**
     * @ORM\Column(type="boolean")
     */
    private $workerNotSendOrder;

    /**
     * @ORM\Column(type="boolean")
     */
    private $workerStopAfterTransaction;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $websocketOrderbook;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWorkerOrderDiff(): ?float
    {
        return $this->workerOrderDiff;
    }

    public function setWorkerOrderDiff(float $workerOrderDiff): self
    {
        $this->workerOrderDiff = $workerOrderDiff;

        return $this;
    }

    public function getWorkerOrderSize(): ?int
    {
        return $this->workerOrderSize;
    }

    public function setWorkerOrderSize(int $workerOrderSize): self
    {
        $this->workerOrderSize = $workerOrderSize;

        return $this;
    }

    public function getWorkerNotSendOrder(): ?bool
    {
        return $this->workerNotSendOrder;
    }

    public function setWorkerNotSendOrder(bool $workerNotSendOrder): self
    {
        $this->workerNotSendOrder = $workerNotSendOrder;

        return $this;
    }

    public function getWorkerStopAfterTransaction(): ?bool
    {
        return $this->workerStopAfterTransaction;
    }

    public function setWorkerStopAfterTransaction(bool $workerStopAfterTransaction): self
    {
        $this->workerStopAfterTransaction = $workerStopAfterTransaction;

        return $this;
    }

    public function getWebsocketOrderbook(): ?bool
    {
        return $this->websocketOrderbook;
    }

    public function setWebsocketOrderbook(?bool $websocketOrderbook): self
    {
        $this->websocketOrderbook = $websocketOrderbook;

        return $this;
    }
}
