<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity\Traits;

use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

trait ExcerptTranslatableTrait
{
    private string $locale = 'en';

    abstract protected function getTranslation(string $locale);
    abstract protected function createTranslation(string $locale);

    public function copyToLocale(string $locale): self
    {
        if ($currentTranslation = $this->getTranslation($this->getLocale())) {
            $newTranslation = clone $currentTranslation;
            $newTranslation->setLocale($locale);
            $this->translations->set($locale, $newTranslation);
            $this->setLocale($locale);
        }
        return $this;
    }

    #[Serializer\VirtualProperty(name: "title")]
    public function getTitle(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getTitle();
    }

    public function setTitle(?string $title): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setTitle($title);
        return $this;
    }

    #[Serializer\VirtualProperty(name: "more")]
    public function getMore(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getMore();
    }

    public function setMore(?string $more): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setMore($more);
        return $this;
    }

    #[Serializer\VirtualProperty(name: "description")]
    public function getDescription(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getDescription();
    }

    public function setDescription(?string $description): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setDescription($description);
        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return ?CategoryInterface[]
     */
    #[Serializer\VirtualProperty(name: "categories")]
    public function getCategories(): ?array
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getCategoryIds();
    }

    public function addCategory(CategoryInterface $category): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->addCategory($category);
        return $this;
    }

    public function removeCategories(): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->removeCategories();
        return $this;
    }

    #[Serializer\VirtualProperty(name: "tags")]
    public function getTags(): array
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        return $translation->getTagNames();
    }

    public function addTag(TagInterface $tag): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->addTag($tag);
        return $this;
    }

    public function removeTags(): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->removeTags();
        return $this;
    }

    /**
     * Usually this method should be named getIcons() but since the VirtualProperty annotation seems not to work
     * properly in traits this method is renamed to match the property (icon) for now.
     */
    #[Serializer\VirtualProperty(name: "icon")]
    public function getIcon(): ?array
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getIconIds();
    }

    public function addIcon(MediaInterface $icon): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->addIcon($icon);
        return $this;
    }

    public function removeIcons(): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->removeIcons();
        return $this;
    }

    #[Serializer\VirtualProperty(name: "images")]
    public function getImages(): ?array
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getImageIds();
    }

    public function addImage(MediaInterface $image): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->addImage($image);
        return $this;
    }

    public function removeImages(): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->removeImages();
        return $this;
    }

}
