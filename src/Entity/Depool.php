<?php

namespace App\Entity;

use App\Repository\DepoolRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="depool",
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="net_id__address",
 *            columns={"net_id", "address"})
 *    }
 * )
 * @ORM\Entity(repositoryClass=DepoolRepository::class)
 */
class Depool
{
    public const CODE_HASH = 'b4ad6c42427a12a65d9a0bffb0c2730dd9cdf830a086d94636dab7784e13eb38';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    public $id;

    /**
     * @ORM\ManyToOne(targetEntity="Net", inversedBy="depools")
     * @ORM\JoinColumn(name="net_id", referencedColumnName="id")
     */
    private $net;

    /**
     * @ORM\Column(type="string", length=67)
     */
    public $address;

    /**
     * @ORM\Column(type="json")
     */
    public $info;

    /**
     * @ORM\Column(type="json")
     */
    public $stakes;

    /**
     * @ORM\Column(type="datetime")
     */
    public $createdTs;

    /**
     * @ORM\OneToMany(targetEntity="DepoolRound", mappedBy="depool")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    public $rounds;

    public function __construct(Net $net, string $address, array $info, array $stakes)
    {
        $this->net = $net;
        $this->address = $address;
        $this->info = $info;
        $this->stakes = $stakes;
        $this->createdTs = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getCreatedTs(): ?\DateTimeInterface
    {
        return $this->createdTs;
    }

    /**
     * @return Collection|DepoolRound[]
     */
    public function getRounds(): Collection
    {
        return $this->rounds;
    }

    public function setRounds($rounds): void
    {
        $this->rounds = $rounds;
    }

    public function getInfo(): array
    {
        return $this->info;
    }

    public function setStakes(array $stakes): void
    {
        $this->stakes = $stakes;
    }

    public function getStakes(): array
    {
        return $this->stakes;
    }
}
