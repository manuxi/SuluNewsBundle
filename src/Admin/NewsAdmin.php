<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Admin;

use Manuxi\SuluNewsBundle\Entity\News;
use Manuxi\SuluNewsBundle\Service\NewsTypeSelect;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\DropdownToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\TogglerToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\AutomationBundle\Admin\AutomationAdmin;
use Sulu\Bundle\AutomationBundle\Admin\View\AutomationViewBuilderFactoryInterface;
use Sulu\Bundle\PageBundle\Document\BasePageDocument;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class NewsAdmin extends Admin
{
    public const NAV_ITEM = 'sulu_news.news';

    public const STRUCTURE_TAG_TYPE = 'sulu_news.news.type';
    public const ADD_FORM_VIEW_DETAILS = 'sulu_news.news.add_form.details';
    public const EDIT_FORM_VIEW_DETAILS = 'sulu_news.news.edit_form.details';

    public const LIST_VIEW = 'sulu_news.news.list';
    public const ADD_FORM_VIEW = 'sulu_news.news.add_form';
    public const ADD_FORM_DETAILS_VIEW = 'sulu_news.news.add_form.details';
    public const EDIT_FORM_VIEW = 'sulu_news.news.edit_form';
    public const EDIT_FORM_DETAILS_VIEW = 'sulu_news.news.edit_form.details';
    public const SECURITY_CONTEXT = 'sulu.modules.news';

    //seo,excerpt, etc
    public const EDIT_FORM_VIEW_SEO = 'sulu_news.news.edit_form.seo';
    public const EDIT_FORM_VIEW_EXCERPT = 'sulu_news.news.edit_form.excerpt';
    public const EDIT_FORM_VIEW_SETTINGS = 'sulu_news.edit_form.settings';
    public const EDIT_FORM_VIEW_AUTOMATION = 'sulu_news.news.edit_form.automation';
    public const EDIT_FORM_VIEW_ACTIVITY = 'sulu_news.news.edit_form.activity';

    private ViewBuilderFactoryInterface $viewBuilderFactory;
    private SecurityCheckerInterface $securityChecker;
    private WebspaceManagerInterface $webspaceManager;
    private NewsTypeSelect $newsTypeSelect;

    private ?AutomationViewBuilderFactoryInterface $automationViewBuilderFactory;

    private ?array $types = null;

    public function __construct(
        ViewBuilderFactoryInterface $viewBuilderFactory,
        SecurityCheckerInterface $securityChecker,
        WebspaceManagerInterface $webspaceManager,
        NewsTypeSelect $newsTypeSelect,
        ?AutomationViewBuilderFactoryInterface $automationViewBuilderFactory = null
    ) {
        $this->viewBuilderFactory = $viewBuilderFactory;
        $this->securityChecker    = $securityChecker;
        $this->webspaceManager    = $webspaceManager;
        $this->newsTypeSelect = $newsTypeSelect;
        $this->automationViewBuilderFactory = $automationViewBuilderFactory;
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(News::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $rootNavigationItem = new NavigationItem(static::NAV_ITEM);
            $rootNavigationItem->setIcon('su-newspaper');
            $rootNavigationItem->setPosition(30);
            $rootNavigationItem->setView(static::LIST_VIEW);

            // Configure a NavigationItem with a View
            $navigationItem = new NavigationItem(static::NAV_ITEM);
            $navigationItem->setPosition(10);
            $navigationItem->setView(static::LIST_VIEW);

            $rootNavigationItem->addChild($navigationItem);

            $navigationItemCollection->add($rootNavigationItem);

        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        if (!$this->securityChecker->hasPermission(News::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            return;
        }

        $formToolbarActions = [];
        $listToolbarActions = [];

        $locales = $this->webspaceManager->getAllLocales();

        if ($this->securityChecker->hasPermission(News::SECURITY_CONTEXT, PermissionTypes::ADD)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.add');
        }

        if ($this->securityChecker->hasPermission(News::SECURITY_CONTEXT, PermissionTypes::LIVE)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.save_with_publishing');
        } else {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.save');
        }

        if ($this->securityChecker->hasPermission(News::SECURITY_CONTEXT, PermissionTypes::DELETE)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.delete');
            $listToolbarActions[] = new ToolbarAction('sulu_admin.delete');
        }

        if ($this->securityChecker->hasPermission(News::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.export');
        }


        if ($this->securityChecker->hasPermission(News::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            // Configure News List View
            $listView = $this->viewBuilderFactory->createListViewBuilder(static::LIST_VIEW, '/news/:locale')
                ->setResourceKey(News::RESOURCE_KEY)
                ->setListKey(News::LIST_KEY)
                ->setTitle('sulu_news.news')
                ->addListAdapters(['table'])
                ->addLocales($locales)
                ->setDefaultLocale($locales[0])
                ->setAddView(static::ADD_FORM_VIEW)
                ->setEditView(static::EDIT_FORM_VIEW)
                ->addToolbarActions($listToolbarActions);
            $viewCollection->add($listView);

            // Configure News Add View
            $addFormView = $this->viewBuilderFactory
                ->createResourceTabViewBuilder(static::ADD_FORM_VIEW, '/news/:locale/add')
                ->setResourceKey(News::RESOURCE_KEY)
                ->setBackView(static::LIST_VIEW)
                ->addLocales($locales);
            $viewCollection->add($addFormView);

            $addDetailsFormView = $this->viewBuilderFactory
                ->createFormViewBuilder(static::ADD_FORM_DETAILS_VIEW, '/details')
                ->setResourceKey(News::RESOURCE_KEY)
                ->setFormKey(News::FORM_KEY)
                ->setTabTitle('sulu_admin.details')
                ->setEditView(static::EDIT_FORM_VIEW)
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::ADD_FORM_VIEW);
            $viewCollection->add($addDetailsFormView);

            // Configure News Edit View
            $editFormView = $this->viewBuilderFactory
                ->createResourceTabViewBuilder(static::EDIT_FORM_VIEW, '/news/:locale/:id')
                ->setResourceKey(News::RESOURCE_KEY)
                ->setBackView(static::LIST_VIEW)
                ->setTitleProperty('title')
                ->addLocales($locales);
            $viewCollection->add($editFormView);

            //publish/unpublish toolbar actions
            $formToolbarActions = [
                new ToolbarAction('sulu_admin.save'),
                new ToolbarAction('sulu_admin.delete'),
                new TogglerToolbarAction(
                    'sulu_news.published',
                    'published',
                    'publish',
                    'unpublish'
                ),
            ];
/*
            if ($this->securityChecker->hasPermission(News::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
                $editDropdownToolbarActions = [
                    new ToolbarAction('sulu_admin.delete_draft'),
                    new ToolbarAction('sulu_admin.set_unpublished'),
                    new ToolbarAction('sulu_admin.copy'),
                ];

                if (\count($locales) > 1) {
                    $editDropdownToolbarActions[] = new ToolbarAction('sulu_admin.copy_locale');
                }

                $formToolbarActions[] = new DropdownToolbarAction(
                    'sulu_admin.edit',
                    'su-cog',
                    $editDropdownToolbarActions
                );
            }
*/

            $editDetailsFormView = $this->viewBuilderFactory
                ->createPreviewFormViewBuilder(static::EDIT_FORM_DETAILS_VIEW, '/details')
                ->setPreviewCondition('id != null')
                ->setResourceKey(News::RESOURCE_KEY)
                ->setFormKey(News::FORM_KEY)
                ->setTabTitle('sulu_admin.details')
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::EDIT_FORM_VIEW);
            $viewCollection->add($editDetailsFormView);

            //seo,excerpt, etc
            $formToolbarActionsWithoutType = [];
            $previewCondition              = 'nodeType == 1';

            if ($this->securityChecker->hasPermission(News::SECURITY_CONTEXT, PermissionTypes::ADD)) {
                $listToolbarActions[] = new ToolbarAction('sulu_admin.add');
            }

            if ($this->securityChecker->hasPermission(News::SECURITY_CONTEXT, PermissionTypes::LIVE)) {
                $formToolbarActionsWithoutType[] = new ToolbarAction('sulu_admin.save_with_publishing');
            } else {
                $formToolbarActionsWithoutType[] = new ToolbarAction('sulu_admin.save');
            }

            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createPreviewFormViewBuilder(static::EDIT_FORM_VIEW_SEO, '/seo')
//                    ->disablePreviewWebspaceChooser()
                    ->setResourceKey(News::RESOURCE_KEY)
                    ->setFormKey('page_seo')
                    ->setTabTitle('sulu_page.seo')
//                    ->setTabCondition('nodeType == 1 && shadowOn == false')
                    ->addToolbarActions($formToolbarActionsWithoutType)
//                    ->addRouterAttributesToFormRequest($routerAttributesToFormRequest)
                    ->setPreviewCondition($previewCondition)
                    ->setTitleVisible(true)
                    ->setTabOrder(2048)
                    ->setParent(static::EDIT_FORM_VIEW)
            );
            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createPreviewFormViewBuilder(static::EDIT_FORM_VIEW_EXCERPT, '/excerpt')
//                    ->disablePreviewWebspaceChooser()
                    ->setResourceKey(News::RESOURCE_KEY)
                    ->setFormKey('page_excerpt')
                    ->setTabTitle('sulu_page.excerpt')
//                    ->setTabCondition('(nodeType == 1 || nodeType == 4) && shadowOn == false')
                    ->addToolbarActions($formToolbarActionsWithoutType)
//                    ->addRouterAttributesToFormRequest($routerAttributesToFormRequest)
//                    ->addRouterAttributesToFormMetadata($routerAttributesToFormMetadata)
                    ->setPreviewCondition($previewCondition)
                    ->setTitleVisible(true)
                    ->setTabOrder(3072)
                    ->setParent(static::EDIT_FORM_VIEW)
            );
            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createPreviewFormViewBuilder(static::EDIT_FORM_VIEW_SETTINGS, '/settings')
                    ->disablePreviewWebspaceChooser()
                    ->setResourceKey(News::RESOURCE_KEY)
                    ->setFormKey('news_settings')
                    ->setTabTitle('sulu_page.settings')
                    ->addToolbarActions($formToolbarActionsWithoutType)
                    ->setPreviewCondition($previewCondition)
                    ->setTitleVisible(true)
                    ->setTabOrder(4096)
                    ->setParent(static::EDIT_FORM_VIEW)
            );


            if ($this->automationViewBuilderFactory
                && $this->securityChecker->hasPermission(AutomationAdmin::SECURITY_CONTEXT, PermissionTypes::EDIT)
            ) {
                $viewCollection->add(
                    $this->automationViewBuilderFactory
                        ->createTaskListViewBuilder(static::EDIT_FORM_VIEW_AUTOMATION,'/automation',BasePageDocument::class)
                        ->setTabOrder(5120)
                        ->setParent(static::EDIT_FORM_VIEW)
                );
            }
        }
    }

    /**
     * @return mixed[]
     */
    public function getSecurityContexts(): array
    {
        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                'News' => [
                    News::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                    ],
                ],
            ],
        ];
    }

    private function getTypes(): array
    {
        if(null === $this->types) {
            $this->types = $this->newsTypeSelect->getValues();
        }

        return $this->types;
    }

    public function getSecurityContextsTmp()
    {
        $securityContext = [];

        foreach ($this->getTypes() as $typeKey => $type) {
            $securityContext[static::getNewsSecurityContext($typeKey)] = [
                PermissionTypes::VIEW,
                PermissionTypes::ADD,
                PermissionTypes::EDIT,
                PermissionTypes::DELETE,
                PermissionTypes::LIVE,
            ];
        }

        return [
            'Sulu' => [
                'Global' => [
                    News::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                        PermissionTypes::LIVE,
                    ],
                ],
                'News types' => $securityContext,
            ],
        ];
    }

    public static function getNewsSecurityContext(string $typeKey): string
    {
        return \sprintf('%s_%s', News::SECURITY_CONTEXT, $typeKey);
    }

    public function getConfigKey(): ?string
    {
        return 'sulu_news';
    }
}
