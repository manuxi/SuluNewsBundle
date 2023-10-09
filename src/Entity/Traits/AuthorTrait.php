<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Entity\Traits;

use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Component\Security\Authentication\UserInterface;

trait AuthorTrait
{

    protected ?ContactInterface $author = null;

    public function getAuthor(): ?ContactInterface
    {
        return $this->author;
    }

    public function setAuthor(?ContactInterface $author): self
    {
        $this->author = $author;
        return $this;
    }

}
