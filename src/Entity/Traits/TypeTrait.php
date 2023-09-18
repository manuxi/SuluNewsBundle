<?php

namespace Manuxi\SuluNewsBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

trait TypeTrait
{

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $type = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }
}
