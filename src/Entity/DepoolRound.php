<?php

namespace App\Entity;

use App\Repository\DepoolRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class DepoolRound
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\Column(type="bigint")
     */
    private $rid;

    /**
     * @ORM\ManyToOne(targetEntity="Depool", inversedBy="rounds")
     * @ORM\JoinColumn(name="depool_id", referencedColumnName="id")
     */
    private $depool;

    /**
     * @ORM\Column(type="json")
     */
    private $data;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdTs;

    public function __construct(Depool $depool, array $data)
    {
        $this->depool = $depool;
        $this->data = $data;
        $this->createdTs = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedTs(): ?\DateTimeInterface
    {
        return $this->createdTs;
    }
}
