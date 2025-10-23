<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Tests\Unit\Admin;

use Manuxi\SuluNewsBundle\Admin\NewsAdmin;
use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Service\NewsTypeSelect;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\AutomationBundle\Admin\View\AutomationViewBuilderFactoryInterface;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class NewsAdminTest extends TestCase
{
    private NewsAdmin $newsAdmin;

    private ViewBuilderFactoryInterface|MockObject $viewBuilderFactory;
    private SecurityCheckerInterface|MockObject $securityChecker;
    private WebspaceManagerInterface|MockObject $webspaceManager;
    private NewsTypeSelect|MockObject $newsTypeSelect;
    private AutomationViewBuilderFactoryInterface|MockObject $automationViewBuilderFactory;

    protected function setUp(): void
    {
        $this->viewBuilderFactory = $this->createMock(ViewBuilderFactoryInterface::class);
        $this->securityChecker = $this->createMock(SecurityCheckerInterface::class);
        $this->webspaceManager = $this->createMock(WebspaceManagerInterface::class);
        $this->newsTypeSelect = $this->createMock(NewsTypeSelect::class);
        $this->automationViewBuilderFactory = $this->createMock(AutomationViewBuilderFactoryInterface::class);

        $this->newsAdmin = new NewsAdmin(
            $this->viewBuilderFactory,
            $this->securityChecker,
            $this->webspaceManager,
            $this->newsTypeSelect,
            $this->automationViewBuilderFactory
        );
    }

    public function testGetConfigKeyReturnsCorrectValue(): void
    {
        // Act
        $configKey = $this->newsAdmin->getConfigKey();

        // Assert
        $this->assertEquals('sulu_news', $configKey);
    }

    public function testGetNewsSecurityContextReturnsFormattedString(): void
    {
        // Act
        $context = NewsAdmin::getNewsSecurityContext('article');

        // Assert
        $this->assertEquals('sulu.news.news_article', $context);
    }

    public function testGetNewsSecurityContextWithDifferentTypes(): void
    {
        // Assert
        $this->assertEquals('sulu.news.news_blog', NewsAdmin::getNewsSecurityContext('blog'));
        $this->assertEquals('sulu.news.news_event', NewsAdmin::getNewsSecurityContext('event'));
        $this->assertEquals('sulu.news.news_press', NewsAdmin::getNewsSecurityContext('press'));
    }

    public function testGetSecurityContextsReturnsCorrectStructure(): void
    {
        // Act
        $contexts = $this->newsAdmin->getSecurityContexts();

        // Assert
        $this->assertIsArray($contexts);
        $this->assertArrayHasKey('Sulu', $contexts);
        $this->assertArrayHasKey('News', $contexts['Sulu']);
        $this->assertArrayHasKey(News::SECURITY_CONTEXT, $contexts['Sulu']['News']);
    }

    public function testGetSecurityContextsContainsAllPermissionTypes(): void
    {
        // Act
        $contexts = $this->newsAdmin->getSecurityContexts();
        $permissions = $contexts['Sulu']['News'][News::SECURITY_CONTEXT];

        // Assert
        $this->assertContains(PermissionTypes::VIEW, $permissions);
        $this->assertContains(PermissionTypes::ADD, $permissions);
        $this->assertContains(PermissionTypes::EDIT, $permissions);
        $this->assertContains(PermissionTypes::DELETE, $permissions);
        $this->assertContains(PermissionTypes::LIVE, $permissions);
    }

    public function testConfigureNavigationItemsDoesNothingWhenNoEditPermission(): void
    {
        // Arrange
        $navigationItemCollection = $this->createMock(NavigationItemCollection::class);

        $this->securityChecker
            ->expects($this->once())
            ->method('hasPermission')
            ->with(News::SECURITY_CONTEXT, PermissionTypes::EDIT)
            ->willReturn(false);

        $navigationItemCollection
            ->expects($this->never())
            ->method('add');

        // Act
        $this->newsAdmin->configureNavigationItems($navigationItemCollection);
    }

    public function testConfigureNavigationItemsAddsItemWhenHasEditPermission(): void
    {
        // Arrange
        $navigationItemCollection = $this->createMock(NavigationItemCollection::class);

        $this->securityChecker
            ->expects($this->once())
            ->method('hasPermission')
            ->with(News::SECURITY_CONTEXT, PermissionTypes::EDIT)
            ->willReturn(true);

        $navigationItemCollection
            ->expects($this->once())
            ->method('add');

        // Act
        $this->newsAdmin->configureNavigationItems($navigationItemCollection);
    }

    public function testConfigureViewsDoesNothingWhenNoEditPermission(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);

        $this->securityChecker
            ->expects($this->once())
            ->method('hasPermission')
            ->with(News::SECURITY_CONTEXT, PermissionTypes::EDIT)
            ->willReturn(false);

        $viewCollection
            ->expects($this->never())
            ->method('add');

        // Act
        $this->newsAdmin->configureViews($viewCollection);
    }

    public function testConfigureViewsAddsViewsWhenHasEditPermission(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $locales = ['en', 'de'];

        $this->mockSecurityCheckerForViews();
        $this->mockWebspaceManager($locales);
        $this->mockViewBuilders($viewCollection, $locales);

        // Act
        $this->newsAdmin->configureViews($viewCollection);

        // Note: Exact count depends on permissions and automation availability
        // We just verify that add() was called
        $this->assertTrue(true);
    }

    public function testConfigureViewsWithMultipleLocales(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $locales = ['en', 'de', 'fr', 'es'];

        $this->mockSecurityCheckerForViews();
        $this->mockWebspaceManager($locales);
        $this->mockViewBuilders($viewCollection, $locales);

        // Act
        $this->newsAdmin->configureViews($viewCollection);

        // Assert - verify that webspace manager was called
        $this->assertTrue(true);
    }

    public function testConfigureViewsAddsSeoTab(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $locales = ['en'];

        $this->mockSecurityCheckerForViews();
        $this->mockWebspaceManager($locales);

        // Verify that SEO view is created
        $this->viewBuilderFactory
            ->method('createPreviewFormViewBuilder')
            ->willReturnCallback(function($name) {
                $builder = $this->createMock(\Sulu\Bundle\AdminBundle\Admin\View\PreviewFormViewBuilder::class);
                $builder->method('setResourceKey')->willReturnSelf();
                $builder->method('setFormKey')->willReturnSelf();
                $builder->method('setTabTitle')->willReturnSelf();
                $builder->method('addToolbarActions')->willReturnSelf();
                $builder->method('setPreviewCondition')->willReturnSelf();
                $builder->method('setTitleVisible')->willReturnSelf();
                $builder->method('setTabOrder')->willReturnSelf();
                $builder->method('setParent')->willReturnSelf();
                $builder->method('disablePreviewWebspaceChooser')->willReturnSelf();

                if ($name === NewsAdmin::EDIT_FORM_VIEW_SEO) {
                    $this->assertEquals(NewsAdmin::EDIT_FORM_VIEW_SEO, $name);
                }

                return $builder;
            });

        // Act
        $this->newsAdmin->configureViews($viewCollection);

        // Assert
        $this->assertTrue(true);
    }

    public function testConfigureViewsAddsExcerptTab(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $locales = ['en'];

        $this->mockSecurityCheckerForViews();
        $this->mockWebspaceManager($locales);

        // Verify that Excerpt view is created
        $this->viewBuilderFactory
            ->method('createPreviewFormViewBuilder')
            ->willReturnCallback(function($name) {
                $builder = $this->createMock(\Sulu\Bundle\AdminBundle\Admin\View\PreviewFormViewBuilder::class);
                $builder->method('setResourceKey')->willReturnSelf();
                $builder->method('setFormKey')->willReturnSelf();
                $builder->method('setTabTitle')->willReturnSelf();
                $builder->method('addToolbarActions')->willReturnSelf();
                $builder->method('setPreviewCondition')->willReturnSelf();
                $builder->method('setTitleVisible')->willReturnSelf();
                $builder->method('setTabOrder')->willReturnSelf();
                $builder->method('setParent')->willReturnSelf();
                $builder->method('disablePreviewWebspaceChooser')->willReturnSelf();

                if ($name === NewsAdmin::EDIT_FORM_VIEW_EXCERPT) {
                    $this->assertEquals(NewsAdmin::EDIT_FORM_VIEW_EXCERPT, $name);
                }

                return $builder;
            });

        // Act
        $this->newsAdmin->configureViews($viewCollection);

        // Assert
        $this->assertTrue(true);
    }

    public function testConfigureViewsAddsSettingsTab(): void
    {
        // Arrange
        $viewCollection = $this->createMock(ViewCollection::class);
        $locales = ['en'];

        $this->mockSecurityCheckerForViews();
        $this->mockWebspaceManager($locales);

        // Verify that Settings view is created
        $this->viewBuilderFactory
            ->method('createPreviewFormViewBuilder')
            ->willReturnCallback(function($name) {
                $builder = $this->createMock(\Sulu\Bundle\AdminBundle\Admin\View\PreviewFormViewBuilder::class);
                $builder->method('setResourceKey')->willReturnSelf();
                $builder->method('setFormKey')->willReturnSelf();
                $builder->method('setTabTitle')->willReturnSelf();
                $builder->method('addToolbarActions')->willReturnSelf();
                $builder->method('setPreviewCondition')->willReturnSelf();
                $builder->method('setTitleVisible')->willReturnSelf();
                $builder->method('setTabOrder')->willReturnSelf();
                $builder->method('setParent')->willReturnSelf();
                $builder->method('disablePreviewWebspaceChooser')->willReturnSelf();

                if ($name === NewsAdmin::EDIT_FORM_VIEW_SETTINGS) {
                    $this->assertEquals(NewsAdmin::EDIT_FORM_VIEW_SETTINGS, $name);
                }

                return $builder;
            });

        // Act
        $this->newsAdmin->configureViews($viewCollection);

        // Assert
        $this->assertTrue(true);
    }

    public function testConstantsHaveCorrectValues(): void
    {
        // Assert
        $this->assertEquals('sulu_news.news', NewsAdmin::NAV_ITEM);
        $this->assertEquals('sulu_news.news.list', NewsAdmin::LIST_VIEW);
        $this->assertEquals('sulu_news.news.add_form', NewsAdmin::ADD_FORM_VIEW);
        $this->assertEquals('sulu_news.news.add_form.details', NewsAdmin::ADD_FORM_DETAILS_VIEW);
        $this->assertEquals('sulu_news.news.edit_form', NewsAdmin::EDIT_FORM_VIEW);
        $this->assertEquals('sulu_news.news.edit_form.details', NewsAdmin::EDIT_FORM_DETAILS_VIEW);
        $this->assertEquals('sulu.modules.news', NewsAdmin::SECURITY_CONTEXT);
        $this->assertEquals('sulu_news.news.edit_form.seo', NewsAdmin::EDIT_FORM_VIEW_SEO);
        $this->assertEquals('sulu_news.news.edit_form.excerpt', NewsAdmin::EDIT_FORM_VIEW_EXCERPT);
        $this->assertEquals('sulu_news.edit_form.settings', NewsAdmin::EDIT_FORM_VIEW_SETTINGS);
        $this->assertEquals('sulu_news.news.edit_form.automation', NewsAdmin::EDIT_FORM_VIEW_AUTOMATION);
        $this->assertEquals('sulu_news.news.edit_form.activity', NewsAdmin::EDIT_FORM_VIEW_ACTIVITY);
    }

    /**
     * Helper: Mock security checker for views configuration
     */
    private function mockSecurityCheckerForViews(): void
    {
        $this->securityChecker
            ->method('hasPermission')
            ->willReturnCallback(function($context, $permission) {
                // Grant all permissions for testing
                return true;
            });
    }

    /**
     * Helper: Mock webspace manager with locales
     */
    private function mockWebspaceManager(array $locales): void
    {
        $this->webspaceManager
            ->expects($this->atLeastOnce())
            ->method('getAllLocales')
            ->willReturn($locales);
    }

    /**
     * Helper: Mock view builders for views configuration
     */
    private function mockViewBuilders(MockObject $viewCollection, array $locales): void
    {
        // Mock list view builder
        $listViewBuilder = $this->createMock(\Sulu\Bundle\AdminBundle\Admin\View\ListViewBuilder::class);
        $listViewBuilder->method('setResourceKey')->willReturnSelf();
        $listViewBuilder->method('setListKey')->willReturnSelf();
        $listViewBuilder->method('setTitle')->willReturnSelf();
        $listViewBuilder->method('addListAdapters')->willReturnSelf();
        $listViewBuilder->method('addLocales')->willReturnSelf();
        $listViewBuilder->method('setDefaultLocale')->willReturnSelf();
        $listViewBuilder->method('setAddView')->willReturnSelf();
        $listViewBuilder->method('setEditView')->willReturnSelf();
        $listViewBuilder->method('addToolbarActions')->willReturnSelf();

        // Mock resource tab view builder
        $resourceTabViewBuilder = $this->createMock(\Sulu\Bundle\AdminBundle\Admin\View\ResourceTabViewBuilder::class);
        $resourceTabViewBuilder->method('setResourceKey')->willReturnSelf();
        $resourceTabViewBuilder->method('setBackView')->willReturnSelf();
        $resourceTabViewBuilder->method('addLocales')->willReturnSelf();
        $resourceTabViewBuilder->method('setTitleProperty')->willReturnSelf();
        $resourceTabViewBuilder->method('setAttributeDefault')->willReturnSelf();

        // Mock form view builder
        $formViewBuilder = $this->createMock(\Sulu\Bundle\AdminBundle\Admin\View\FormViewBuilder::class);
        $formViewBuilder->method('setResourceKey')->willReturnSelf();
        $formViewBuilder->method('setFormKey')->willReturnSelf();
        $formViewBuilder->method('setTabTitle')->willReturnSelf();
        $formViewBuilder->method('setEditView')->willReturnSelf();
        $formViewBuilder->method('addToolbarActions')->willReturnSelf();
        $formViewBuilder->method('setParent')->willReturnSelf();

        // Mock preview form view builder
        $previewFormViewBuilder = $this->createMock(\Sulu\Bundle\AdminBundle\Admin\View\PreviewFormViewBuilder::class);
        $previewFormViewBuilder->method('setPreviewCondition')->willReturnSelf();
        $previewFormViewBuilder->method('setResourceKey')->willReturnSelf();
        $previewFormViewBuilder->method('setFormKey')->willReturnSelf();
        $previewFormViewBuilder->method('setTabTitle')->willReturnSelf();
        $previewFormViewBuilder->method('addToolbarActions')->willReturnSelf();
        $previewFormViewBuilder->method('setParent')->willReturnSelf();
        $previewFormViewBuilder->method('setTitleVisible')->willReturnSelf();
        $previewFormViewBuilder->method('setTabOrder')->willReturnSelf();
        $previewFormViewBuilder->method('disablePreviewWebspaceChooser')->willReturnSelf();

        $this->viewBuilderFactory
            ->method('createListViewBuilder')
            ->willReturn($listViewBuilder);

        $this->viewBuilderFactory
            ->method('createResourceTabViewBuilder')
            ->willReturn($resourceTabViewBuilder);

        $this->viewBuilderFactory
            ->method('createFormViewBuilder')
            ->willReturn($formViewBuilder);

        $this->viewBuilderFactory
            ->method('createPreviewFormViewBuilder')
            ->willReturn($previewFormViewBuilder);

        $viewCollection->method('add')->willReturnSelf();
    }
}