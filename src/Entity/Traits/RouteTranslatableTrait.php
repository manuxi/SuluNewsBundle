<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity\Traits;

use JMS\Serializer\Annotation as Serializer;

trait RouteTranslatableTrait
{
    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    #[Serializer\VirtualProperty(name: "route")]
    public function getRoute(): ?string
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getRoute();
    }

    public function setRoute(string $route): self
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            $translation = $this->createTranslation($this->getLocale());
        }

        $translation->setRoute($route);
        return $this;
    }
}
