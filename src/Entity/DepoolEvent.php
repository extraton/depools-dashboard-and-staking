<?php

namespace App\Entity;

use App\Repository\DepoolEventRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DepoolEventRepository::class)
 * @ORM\Table(
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="depool_id__eid",
 *            columns={"depool_id", "eid"})
 *    }
 * )
 */
class DepoolEvent
{
    public const NAME_ROUND_COMPLETE = 'RoundCompleted';
    public const REWARD_FIELD_NAME_BY_VERSION = [
        1 => 'rewards',
        3 => 'rewards',
        4 => 'participantReward',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Depool", inversedBy="events")
     * @ORM\JoinColumn(name="depool_id", referencedColumnName="id")
     */
    private $depool;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $eid;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * @ORM\Column(type="json")
     */
    private $data;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdTs;

    public function __construct(Depool $depool, string $eid, string $name, array $data, \DateTime $createdTs)
    {
        $this->depool = $depool;
        $this->eid = $eid;
        $this->name = $name;
        $this->data = $data;
        $this->createdTs = $createdTs;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedTs(): ?\DateTime
    {
        return $this->createdTs;
    }

    public function getDepool(): Depool
    {
        return $this->depool;
    }

    public function getEid(): string
    {
        return $this->eid;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
