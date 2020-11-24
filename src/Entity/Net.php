<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Net
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    public $id;

    /**
     * @ORM\Column(type="string", unique=true, length=67)
     */
    public $server;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    public $explorer;

    /**
     * @ORM\OneToMany(targetEntity="Depool", mappedBy="net")
     */
    public $depools;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getServer()
    {
        return $this->server;
    }

    public function getExplorer()
    {
        return $this->explorer;
    }
}
