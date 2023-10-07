<?php

namespace Manuxi\SuluNewsBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\RouteBundle\Model\RouteInterface;

trait RouteTrait
{

    private ?RouteInterface $route = null;

    public function getRoute(): ?RouteInterface
    {
        return $this->route ?? '';
    }

    public function setRoute(RouteInterface $route): self
    {
        $this->route = $route;
        return $this;
    }

}
