<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Tests\Unit\Admin;

use Manuxi\SuluNewsBundle\Admin\NewsAdmin;
use Manuxi\SuluNewsBundle\Admin\SettingsAdmin;
use Manuxi\SuluNewsBundle\Entity\NewsSettings;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\FormViewBuilder;
use Sulu\Bundle\AdminBundle\Admin\View\ResourceTabViewBuilder;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

class SettingsAdminTest extends TestCase
{
    private SettingsAdmin $settingsAdmin;

    private ViewBuilderFactoryInterface|MockObject $viewBuilderFactory;
    private SecurityCheckerInterface|MockObject $securityChecker;

    protected function setUp(): void
    {
        $this->viewBuilderFactory = $this->createMock(ViewBuilderFactoryInterface::class);
        $this->securityChecker = $this->createMock(SecurityCheckerInterface::class);

        $this->settingsAdmin = new SettingsAdmin(
            $this->viewBuilderFactory,
            $this->securityChecker
        );
    }

    public function testGetConfigKeyReturnsCorrectValue(): void
    {
        // Act
        $configKey = $this->settingsAdmin->getConfigKey();

        // Assert
        $this->assertEquals('sulu_news.config.title', $configKey);
    }

    public function testConstantsHaveCorrectValues(): void
    {
        // Assert
        $this->assertEquals('sulu_news.config', SettingsAdmin::TAB_VIEW);
        $this->assertEquals('sulu_news.config.form', SettingsAdmin::FORM_VIEW);
        $this->assertEquals('sulu_news.config.title.navi', SettingsAdmin::NAV_ITEM);
    }

    public function testGetSecurityContextsReturnsCorrectStructure(): void
    {
        // Act
        $contexts = $this->settingsAdmin->getSecurityContexts();

        // Assert
        $this->assertIsArray($contexts);
        $this->assertArrayHasKey('Sulu', $contexts);
        $this->assertArrayHasKey('News', $contexts['Sulu']);
        $this->assertArrayHasKey(NewsSettings::SECURITY_CONTEXT, $contexts['Sulu']['News']);
    }

    public function testGetSecurityContextsContainsViewAndEditPermissions(): void
    {
        // Act
        $contexts = $this->settingsAdmin->getSecurityContexts();
        $permissions = $contexts['Sulu']['News'][NewsSettings::SECURITY_CONTEXT];

        // Assert
        $this->assertCount(2, $permissions);
        $this->assertContains(PermissionTypes::VIEW, $permissions);
        $this->assertContains(PermissionTypes::EDIT, $permissions);
    }

    public function testGetSecurityContextsDoesNotContainOtherPermissions(): void
    {
        // Act
        $contexts = $this->settingsAdmin->getSecurityContexts();
        $permissions = $contexts['Sulu']['News'][NewsSettings::SECURITY_CONTEXT];

        // Assert
        $this->assertNotContains(PermissionTypes::ADD, $permissions);
        $this->assertNotContains(PermissionTypes::DELETE, $permissions);
        $this->assertNotContains(PermissionTypes::LIVE, $permissions);
    }

    public function testConfigureNavigationItemsDoesNothingWhenNoEditPermission(): void
    {
        // Arrange
        $navigationItemCollection = $this->createMock(NavigationItemCollection::class);

        $this->securityChecker
            ->expects($this->once())
            ->method('hasPermission')
            ->with(NewsSettings::SECURITY_CONTEXT, PermissionTypes::EDIT)
            ->willReturn(false);

        $navigationItemCollection
            ->expects($this->never())
            ->method('get');

        // Act
        $this->settingsAdmin->configureNavigationItems($navigationItemCollection);
    }

    public function testConfigureNavigationItemsAddsChildWhenHasEditPermission(): void
    {
        // Arrange
        $navigationItemCollection = $this->createMock(NavigationItemCollection::class);
        $parentNavigationItem = $this->createMock(NavigationItem::class);

        $this->securityChecker
            ->expects($this->once())
            ->method('hasPermission')
            ->with(NewsSettings::SECURITY_CONTEXT, PermissionTypes::EDIT)
            ->willReturn(true);

        $navigationItemCollection
            ->expects($this->once())
            ->method('get')
            ->with(NewsAdmin::NAV_ITEM)
            ->willReturn($parentNavigationItem);

        $parentNavigationItem
            ->expects($this->once())
            ->method('addChild');

        // Act
        $this->settingsAdmin->configureNavigationItems($navigationItemCollection);
    }

    public function testConfigureNavigationItemsCreatesSettingsItemWithCorrectProperties(): void
    {
        // Arrange
        $navigationItemCollection = $this->createMock(NavigationItemCollection::class);
        $parentNavigationItem = $this->createMock(NavigationItem::class);

        $this->securityChecker
            ->method('hasPermission')
            ->willReturn(true);

        $navigationItemCollection
            ->method('get')
            ->with(NewsAdmin::NAV_ITEM)
            ->willReturn($parentNavigationItem);

        $capturedChild = null;
        $parentNavigationItem
            ->expects($this->once())
            ->method('addChild')
            ->willReturnCallback(function($child) use (&$capturedChild) {
                $capturedChild = $child;
            });

        // Act
        $this->settingsAdmin->configureNavigationItems($navigationItemCollection);

        // Assert
        $this->assertInstanceOf(NavigationItem::class, $capturedChild);
    }

    public function testConfigureViewsDoesNothingWhenNoEditPermission(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);

        $this->securityChecker
            ->expects($this->once())
            ->method('hasPermission')
            ->with(NewsSettings::SECURITY_CONTEXT, PermissionTypes::EDIT)
            ->willReturn(false);

        $viewCollection
            ->expects($this->never())
            ->method('add');

        // Act
        $this->settingsAdmin->configureViews($viewCollection);
    }

    public function testConfigureViewsAddsTabViewWhenHasEditPermission(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $resourceTabViewBuilder = $this->createMock(ResourceTabViewBuilder::class);

        $this->securityChecker
            ->method('hasPermission')
            ->willReturn(true);

        $this->mockResourceTabViewBuilder($resourceTabViewBuilder);

        $this->viewBuilderFactory
            ->expects($this->once())
            ->method('createResourceTabViewBuilder')
            ->with(SettingsAdmin::TAB_VIEW, '/news-settings/:id')
            ->willReturn($resourceTabViewBuilder);

        $viewCollection
            ->expects($this->exactly(2))
            ->method('add');

        // Act
        $this->settingsAdmin->configureViews($viewCollection);
    }

    public function testConfigureViewsAddsFormViewWhenHasEditPermission(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $resourceTabViewBuilder = $this->createMock(ResourceTabViewBuilder::class);
        $formViewBuilder = $this->createMock(FormViewBuilder::class);

        $this->securityChecker
            ->method('hasPermission')
            ->willReturn(true);

        $this->mockResourceTabViewBuilder($resourceTabViewBuilder);
        $this->mockFormViewBuilder($formViewBuilder);

        $this->viewBuilderFactory
            ->method('createResourceTabViewBuilder')
            ->willReturn($resourceTabViewBuilder);

        $this->viewBuilderFactory
            ->expects($this->once())
            ->method('createFormViewBuilder')
            ->with(SettingsAdmin::FORM_VIEW, '/config')
            ->willReturn($formViewBuilder);

        $viewCollection
            ->expects($this->exactly(2))
            ->method('add');

        // Act
        $this->settingsAdmin->configureViews($viewCollection);
    }

    public function testConfigureViewsUsesCorrectResourceKey(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $resourceTabViewBuilder = $this->createMock(ResourceTabViewBuilder::class);
        $formViewBuilder = $this->createMock(FormViewBuilder::class);

        $this->securityChecker
            ->method('hasPermission')
            ->willReturn(true);

        $resourceTabViewBuilder
            ->expects($this->once())
            ->method('setResourceKey')
            ->with(NewsSettings::RESOURCE_KEY)
            ->willReturnSelf();

        $resourceTabViewBuilder->method('setAttributeDefault')->willReturnSelf();

        $this->mockFormViewBuilder($formViewBuilder);

        $this->viewBuilderFactory
            ->method('createResourceTabViewBuilder')
            ->willReturn($resourceTabViewBuilder);

        $this->viewBuilderFactory
            ->method('createFormViewBuilder')
            ->willReturn($formViewBuilder);

        $viewCollection->method('add')->willReturnSelf();

        // Act
        $this->settingsAdmin->configureViews($viewCollection);
    }

    public function testConfigureViewsUsesCorrectFormKey(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $resourceTabViewBuilder = $this->createMock(ResourceTabViewBuilder::class);
        $formViewBuilder = $this->createMock(FormViewBuilder::class);

        $this->securityChecker
            ->method('hasPermission')
            ->willReturn(true);

        $this->mockResourceTabViewBuilder($resourceTabViewBuilder);

        $formViewBuilder
            ->expects($this->once())
            ->method('setFormKey')
            ->with(NewsSettings::FORM_KEY)
            ->willReturnSelf();

        $formViewBuilder->method('setResourceKey')->willReturnSelf();
        $formViewBuilder->method('setTabTitle')->willReturnSelf();
        $formViewBuilder->method('addToolbarActions')->willReturnSelf();
        $formViewBuilder->method('setParent')->willReturnSelf();

        $this->viewBuilderFactory
            ->method('createResourceTabViewBuilder')
            ->willReturn($resourceTabViewBuilder);

        $this->viewBuilderFactory
            ->method('createFormViewBuilder')
            ->willReturn($formViewBuilder);

        $viewCollection->method('add')->willReturnSelf();

        // Act
        $this->settingsAdmin->configureViews($viewCollection);
    }

    public function testConfigureViewsSetsAttributeDefaultToDash(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $resourceTabViewBuilder = $this->createMock(ResourceTabViewBuilder::class);
        $formViewBuilder = $this->createMock(FormViewBuilder::class);

        $this->securityChecker
            ->method('hasPermission')
            ->willReturn(true);

        $resourceTabViewBuilder
            ->expects($this->once())
            ->method('setAttributeDefault')
            ->with('id', '-')
            ->willReturnSelf();

        $resourceTabViewBuilder->method('setResourceKey')->willReturnSelf();

        $this->mockFormViewBuilder($formViewBuilder);

        $this->viewBuilderFactory
            ->method('createResourceTabViewBuilder')
            ->willReturn($resourceTabViewBuilder);

        $this->viewBuilderFactory
            ->method('createFormViewBuilder')
            ->willReturn($formViewBuilder);

        $viewCollection->method('add')->willReturnSelf();

        // Act
        $this->settingsAdmin->configureViews($viewCollection);
    }

    public function testConfigureViewsAddsToolbarActionToFormView(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $resourceTabViewBuilder = $this->createMock(ResourceTabViewBuilder::class);
        $formViewBuilder = $this->createMock(FormViewBuilder::class);

        $this->securityChecker
            ->method('hasPermission')
            ->willReturn(true);

        $this->mockResourceTabViewBuilder($resourceTabViewBuilder);

        $formViewBuilder
            ->expects($this->once())
            ->method('addToolbarActions')
            ->willReturnCallback(function($actions) use ($formViewBuilder) { // ← FIX: add use()!
                $this->assertIsArray($actions);
                $this->assertCount(1, $actions);
                return $formViewBuilder;
            });

        $formViewBuilder->method('setResourceKey')->willReturnSelf();
        $formViewBuilder->method('setFormKey')->willReturnSelf();
        $formViewBuilder->method('setTabTitle')->willReturnSelf();
        $formViewBuilder->method('setParent')->willReturnSelf();

        $this->viewBuilderFactory
            ->method('createResourceTabViewBuilder')
            ->willReturn($resourceTabViewBuilder);

        $this->viewBuilderFactory
            ->method('createFormViewBuilder')
            ->willReturn($formViewBuilder);

        $viewCollection->method('add')->willReturnSelf();

        // Act
        $this->settingsAdmin->configureViews($viewCollection);
    }

    public function testConfigureViewsSetsCorrectTabTitle(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $resourceTabViewBuilder = $this->createMock(ResourceTabViewBuilder::class);
        $formViewBuilder = $this->createMock(FormViewBuilder::class);

        $this->securityChecker
            ->method('hasPermission')
            ->willReturn(true);

        $this->mockResourceTabViewBuilder($resourceTabViewBuilder);

        $formViewBuilder
            ->expects($this->once())
            ->method('setTabTitle')
            ->with('sulu_news.config.tab')
            ->willReturnSelf();

        $formViewBuilder->method('setResourceKey')->willReturnSelf();
        $formViewBuilder->method('setFormKey')->willReturnSelf();
        $formViewBuilder->method('addToolbarActions')->willReturnSelf();
        $formViewBuilder->method('setParent')->willReturnSelf();

        $this->viewBuilderFactory
            ->method('createResourceTabViewBuilder')
            ->willReturn($resourceTabViewBuilder);

        $this->viewBuilderFactory
            ->method('createFormViewBuilder')
            ->willReturn($formViewBuilder);

        $viewCollection->method('add')->willReturnSelf();

        // Act
        $this->settingsAdmin->configureViews($viewCollection);
    }

    public function testConfigureViewsSetsCorrectParent(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $resourceTabViewBuilder = $this->createMock(ResourceTabViewBuilder::class);
        $formViewBuilder = $this->createMock(FormViewBuilder::class);

        $this->securityChecker
            ->method('hasPermission')
            ->willReturn(true);

        $this->mockResourceTabViewBuilder($resourceTabViewBuilder);

        $formViewBuilder
            ->expects($this->once())
            ->method('setParent')
            ->with(SettingsAdmin::TAB_VIEW)
            ->willReturnSelf();

        $formViewBuilder->method('setResourceKey')->willReturnSelf();
        $formViewBuilder->method('setFormKey')->willReturnSelf();
        $formViewBuilder->method('setTabTitle')->willReturnSelf();
        $formViewBuilder->method('addToolbarActions')->willReturnSelf();

        $this->viewBuilderFactory
            ->method('createResourceTabViewBuilder')
            ->willReturn($resourceTabViewBuilder);

        $this->viewBuilderFactory
            ->method('createFormViewBuilder')
            ->willReturn($formViewBuilder);

        $viewCollection->method('add')->willReturnSelf();

        // Act
        $this->settingsAdmin->configureViews($viewCollection);
    }

    /**
     * Helper: Mock ResourceTabViewBuilder with common behavior
     */
    private function mockResourceTabViewBuilder(MockObject $builder): void
    {
        $builder->method('setResourceKey')->willReturnSelf();
        $builder->method('setAttributeDefault')->willReturnSelf();
    }

    /**
     * Helper: Mock FormViewBuilder with common behavior
     */
    private function mockFormViewBuilder(MockObject $builder): void
    {
        $builder->method('setResourceKey')->willReturnSelf();
        $builder->method('setFormKey')->willReturnSelf();
        $builder->method('setTabTitle')->willReturnSelf();
        $builder->method('addToolbarActions')->willReturnSelf();
        $builder->method('setParent')->willReturnSelf();
    }
}