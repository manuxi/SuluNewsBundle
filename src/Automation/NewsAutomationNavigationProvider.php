<?php

namespace AppBundle\Admin;

use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationProviderInterface;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;

use Sulu\Bundle\AutomationBundle\Admin\AutomationAdmin;

class NewsAutomationNavigationProvider implements NavigationProviderInterface
{
    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        $navigationItem = new NavigationItem('sulu_automation.automation');
        $navigationItem->setId('tab-automation');
        $navigationItemCollection->add($navigationItem);
    }
}