<?php

namespace Manuxi\SuluNewsBundle\Entity\Traits;

use DateTime;
use JMS\Serializer\Annotation as Serializer;

trait PublishedTranslatableTrait
{
    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    /**
     * @Serializer\VirtualProperty("published")
     */
    public function getPublished(): ?bool
    {
        return $this->isPublished();
    }

    public function isPublished(): ?bool
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }
        return $translation->isPublished();
    }

    public function setPublished(bool $published): self
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            $translation = $this->createTranslation($this->getLocale());
        }
        $translation->setPublished($published);
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="published_at")
     */
    public function getPublishedAt(): ?DateTime
    {
        $translation = $this->getTranslation($this->getLocale());
        if(!$translation) {
            return null;
        }
        return $translation->getPublishedAt();
    }

    public function setPublishedAt(?DateTime $date): self
    {
        $translation = $this->getTranslation($this->getLocale());
        if(!$translation) {
            $translation = $this->createTranslation($this->getLocale());
        }
        $translation->setPublishedAt($date);
        return $this;
    }
}
