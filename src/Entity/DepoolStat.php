<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class DepoolStat
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $depoolsNum;

    /**
     * @ORM\Column(type="integer")
     */
    private $membersNum;

    /**
     * @ORM\Column(type="integer")
     */
    private $assetsAmount;

    /**
     * @ORM\Column(type="decimal", precision=4, scale=2)
     */
    private $apy;

    /**
     * @ORM\Column(type="datetime", unique=true)
     */
    private $roundEndTs;

    public function __construct(int $depoolsNum, int $membersNum, int $assetsAmount, string $apy, \DateTime $roundEndTs)
    {
        $this->depoolsNum = $depoolsNum;
        $this->membersNum = $membersNum;
        $this->assetsAmount = $assetsAmount;
        $this->apy = $apy;
        $this->roundEndTs = $roundEndTs;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
