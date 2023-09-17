<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait ExcerptTrait
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }
}
