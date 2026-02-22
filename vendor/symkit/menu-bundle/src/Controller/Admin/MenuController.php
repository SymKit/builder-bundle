<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symkit\CrudBundle\Controller\AbstractCrudController;
use Symkit\MenuBundle\Attribute\ActiveMenu;
use Symkit\MenuBundle\Entity\Menu;
use Symkit\MenuBundle\Form\MenuType;
use Symkit\MetadataBundle\Attribute\Breadcrumb;
use Symkit\MetadataBundle\Attribute\Seo;

final class MenuController extends AbstractCrudController
{
    /**
     * @param class-string<Menu> $menuEntityClass
     */
    public function __construct(
        \Symkit\CrudBundle\Contract\CrudPersistenceManagerInterface $persistenceManager,
        \Symkit\MetadataBundle\Contract\PageContextBuilderInterface $pageContextBuilder,
        private readonly string $menuEntityClass = Menu::class,
    ) {
        parent::__construct($persistenceManager, $pageContextBuilder);
    }

    #[Seo(title: 'Menu Management', description: 'Manage dynamic menus and their items.')]
    #[Breadcrumb(context: 'admin')]
    #[ActiveMenu('admin', 'menus')]
    public function list(Request $request): Response
    {
        return $this->renderIndex($request, [
            'page_title' => 'Menu Management',
            'page_description' => 'Manage dynamic menus and their items.',
        ]);
    }

    protected function getEntityClass(): string
    {
        return $this->menuEntityClass;
    }

    protected function getFormClass(): string
    {
        return MenuType::class;
    }

    protected function getRoutePrefix(): string
    {
        return 'admin_menu';
    }

    protected function configureListFields(): array
    {
        return [
            'name' => [
                'label' => 'Name',
                'sortable' => true,
            ],
            'code' => [
                'label' => 'Code',
                'sortable' => true,
                'cell_class' => 'font-mono text-xs',
            ],
            'items' => [
                'label' => 'Items',
                'template' => '@SymkitCrud/crud/field/count.html.twig',
                'icon' => 'heroicons:list-bullet-20-solid',
            ],
            'actions' => [
                'label' => '',
                'template' => '@SymkitCrud/crud/field/actions.html.twig',
                'edit_route' => 'admin_menu_edit',
                'header_class' => 'text-right',
                'cell_class' => 'text-right',
            ],
        ];
    }

    protected function configureSearchFields(): array
    {
        return ['name', 'code'];
    }

    #[Seo(title: 'Create Menu', description: 'Create a new menu location.')]
    #[Breadcrumb(context: 'admin')]
    #[ActiveMenu('admin', 'menus')]
    public function create(Request $request): Response
    {
        return $this->renderNew(new $this->menuEntityClass(), $request, [
            'page_title' => 'Create Menu',
            'page_description' => 'Create a new menu location.',
        ]);
    }

    #[Seo(title: 'Edit Menu', description: 'Manage menu details and items.')]
    #[Breadcrumb(context: 'admin')]
    #[ActiveMenu('admin', 'menus')]
    public function edit(Menu $menu, Request $request): Response
    {
        return $this->renderEdit($menu, $request, [
            'page_title' => $menu->getName(),
            'page_description' => 'Manage menu details and items.',
            'after_form_template' => '@SymkitMenu/admin/_items_list.html.twig',
            'extra_nav_items_template' => '@SymkitMenu/admin/_items_nav_link.html.twig',
            'template_vars' => [
                'menu' => $menu,
            ],
        ]);
    }

    public function delete(Menu $menu, Request $request): Response
    {
        return $this->performDelete($menu, $request);
    }
}
