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
    private $workerSendOrder;


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

    public function getWorkerSendOrder(): ?bool
    {
        return $this->workerSendOrder;
    }

    public function setWorkerSendOrder(bool $workerSendOrder): self
    {
        $this->workerSendOrder = $workerSendOrder;

        return $this;
    }
}
