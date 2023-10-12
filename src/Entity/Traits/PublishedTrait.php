<?php

namespace Manuxi\SuluNewsBundle\Entity\Traits;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

trait PublishedTrait
{
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $published = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $publishedAt = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $publishedState = null;

    public function isPublished(): ?bool
    {
        return $this->published ?? false;
    }

    public function getPublished(): ?bool
    {
        return $this->published ?? false;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;
        $this->publishedState = $published;
        if($published === true){
            $this->setPublishedAt(new DateTime());
        } else {
            $this->setPublishedAt(null);
        }
        return $this;
    }

    public function getPublishedAt(): ?DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?DateTime $publishedAt): self
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    public function getPublishedState(): ?int
    {
        return $this->publishedState;
    }

    public function setPublishedState(?int $publishedState): self
    {
        $this->publishedState = $publishedState;
        return $this;
    }
}
