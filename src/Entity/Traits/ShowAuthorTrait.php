<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait ShowAuthorTrait
{

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private ?bool $showAuthor = null;

    public function getShowAuthor(): ?bool
    {
        return $this->showAuthor;
    }

    public function setShowAuthor(?bool $showAuthor): self
    {
        $this->showAuthor = $showAuthor;
        return $this;
    }

}
