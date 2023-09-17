<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity\Traits;

use DateTime;
use JMS\Serializer\Annotation as Serializer;

trait TimestampableTranslatableTrait
{
    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    /**
     * @Serializer\VirtualProperty(name="created")
     */
    public function getCreated(): ?DateTime
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getCreated();
    }

    /**
     * @Serializer\VirtualProperty(name="changed")
     */
    public function getChanged(): ?DateTime
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getChanged();
    }
}
