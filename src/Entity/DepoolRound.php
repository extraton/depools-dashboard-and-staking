<?php

namespace App\Entity;

use App\Repository\DepoolRoundRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DepoolRoundRepository::class)
 * @ORM\Table(
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="depool_id__rid",
 *            columns={"depool_id", "rid"})
 *    }
 * )
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
     * @ORM\ManyToOne(targetEntity="Depool", inversedBy="rounds")
     * @ORM\JoinColumn(name="depool_id", referencedColumnName="id")
     */
    private $depool;

    /**
     * @ORM\Column(type="bigint")
     */
    private $rid;

    /**
     * @ORM\Column(type="json")
     */
    private $data;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdTs;

    public function __construct(Depool $depool, int $rid, array $data)
    {
        $this->depool = $depool;
        $this->rid = $rid;
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

    public function getRid()
    {
        return $this->rid;
    }
}
