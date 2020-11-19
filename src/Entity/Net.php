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
}
