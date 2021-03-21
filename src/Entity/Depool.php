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
    public const CODE_HASHES = [
        'b4ad6c42427a12a65d9a0bffb0c2730dd9cdf830a086d94636dab7784e13eb38' => 1,
        'a46c6872712ec49e481a7f3fc1f42469d8bd6ef3fae906aa5b9927e5a3fb3b6b' => 3,
        '14e20e304f53e6da152eb95fffc993dbd28245a775d847eed043f7c78a503885' => 4,
    ];

    private const CONTRACT_VERSION_MAP = [
        1 => 1,
        3 => 2,
        4 => 3
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Net", inversedBy="depools")
     * @ORM\JoinColumn(name="net_id", referencedColumnName="id")
     */
    private $net;

    /**
     * @ORM\Column(type="integer")
     */
    private $version;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=67)
     */
    private $address;

    /**
     * @ORM\Column(type="json")
     */
    private $info;

    /**
     * @ORM\Column(type="json")
     */
    private $stakes;

    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $isDeleted = false;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdTs;

    /**
     * @ORM\OneToMany(targetEntity="DepoolRound", mappedBy="depool")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $rounds;

    /**
     * @ORM\OneToMany(targetEntity="DepoolEvent", mappedBy="depool")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $events;

    public function __construct(Net $net, int $version, string $address, array $info, array $stakes)
    {
        $this->net = $net;
        $this->version = $version;
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

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getNet(): Net
    {
        return $this->net;
    }

    public function compileStakes()
    {
        $stakes = $this->getStakes();
        $total = '0';
        $items = [];
        foreach ($stakes as $stake) {
            $total = bcadd($total, $stake['info']['total']);
            $items[] = [
                'address' => $stake['address'],
                'info' => [
                    'total' => $stake['info']['total'],
                    'withdrawValue' => $stake['info']['withdrawValue'],
                    'reinvest' => $stake['info']['reinvest'],
                    'reward' => $stake['info']['reward'],
                ],
            ];
        }

        return [
            'participantsNum' => count($stakes),
            'total' => $total,
            'items' => $items
        ];
    }

    public function compileParams()
    {
        return [
            'minStake' => $this->getInfo()['minStake'],
            'validatorAssurance' => $this->getInfo()['validatorAssurance'],
            'participantRewardFraction' => $this->getInfo()['participantRewardFraction'],
            'validatorRewardFraction' => $this->getInfo()['validatorRewardFraction'],
            'balanceThreshold' => $this->getInfo()['balanceThreshold'],
            'poolClosed' => $this->getInfo()['poolClosed'],
        ];
    }

    public function compileLink()
    {
        return sprintf(
            "https://%s/accounts/accountDetails?id=%s",
            $this->getNet()->getExplorer(),
            $this->getAddress()
        );
    }

    public function getName()
    {
        return $this->name;
    }

    public function setIsDeleted(): void
    {
        $this->isDeleted = true;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getVersionView(): int
    {
        return self::CONTRACT_VERSION_MAP[$this->version];
    }

    public function setInfo(array $info): void
    {
        $this->info = $info;
    }
}
