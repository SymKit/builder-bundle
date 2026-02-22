<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symkit\CrudBundle\Contract\CrudPersistenceManagerInterface;
use Symkit\CrudBundle\Controller\AbstractCrudController;
use Symkit\MenuBundle\Attribute\ActiveMenu;
use Symkit\MenuBundle\Entity\Menu;
use Symkit\MenuBundle\Entity\MenuItem;
use Symkit\MenuBundle\Form\MenuItemType;
use Symkit\MetadataBundle\Attribute\Breadcrumb;
use Symkit\MetadataBundle\Attribute\Seo;
use Symkit\MetadataBundle\Contract\PageContextBuilderInterface;

final class MenuItemController extends AbstractCrudController
{
    /**
     * @param class-string<Menu>     $menuEntityClass
     * @param class-string<MenuItem> $menuItemEntityClass
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        CrudPersistenceManagerInterface $persistenceManager,
        PageContextBuilderInterface $pageContextBuilder,
        private readonly string $menuEntityClass = Menu::class,
        private readonly string $menuItemEntityClass = MenuItem::class,
    ) {
        parent::__construct($persistenceManager, $pageContextBuilder);
    }

    protected function getEntityClass(): string
    {
        return $this->menuItemEntityClass;
    }

    protected function getFormClass(): string
    {
        return MenuItemType::class;
    }

    protected function getRoutePrefix(): string
    {
        return 'admin_menu_item';
    }

    #[Seo(title: 'Create Menu Item', description: 'Add a new item to the menu.')]
    #[Breadcrumb(context: 'admin')]
    #[ActiveMenu('admin', 'menus')]
    public function create(int $menuId, Request $request): Response
    {
        /** @var Menu|null $menu */
        $menu = $this->entityManager->getRepository($this->menuEntityClass)->find($menuId);
        if (!$menu instanceof Menu) {
            throw $this->createNotFoundException('Menu not found');
        }

        /** @var MenuItem $item */
        $item = new $this->menuItemEntityClass();
        $item->setMenu($menu);

        if ($parentId = $request->query->get('parent_id')) {
            /** @var MenuItem|null $parent */
            $parent = $this->entityManager->getRepository($this->menuItemEntityClass)->find($parentId);
            if ($parent instanceof MenuItem && $parent->getMenu() === $menu) {
                $item->setParent($parent);
            }
        }

        return $this->renderNew($item, $request, [
            'page_title' => 'Create Menu Item',
            'page_description' => 'Add a new item to the menu.',
            'template_vars' => [
                'back_route' => 'admin_menu_edit',
                'back_route_params' => ['id' => $menu->getId()],
            ],
            'redirect_route' => 'admin_menu_edit',
            'redirect_params' => ['id' => $menu->getId()],
        ]);
    }

    #[Seo(title: 'Edit Menu Item', description: 'Update menu item details.')]
    #[Breadcrumb(context: 'admin')]
    #[ActiveMenu('admin', 'menus')]
    public function edit(MenuItem $item, Request $request): Response
    {
        $menu = $item->getMenu();
        if (null === $menu) {
            throw $this->createNotFoundException('Menu item must belong to a menu.');
        }

        return $this->renderEdit($item, $request, [
            'page_title' => 'Edit: '.$item->getLabel(),
            'page_description' => 'Update menu item details.',
            'template_vars' => [
                'back_route' => 'admin_menu_edit',
                'back_route_params' => ['id' => $menu->getId()],
            ],
            'redirect_route' => 'admin_menu_edit',
            'redirect_params' => ['id' => $menu->getId()],
        ]);
    }

    public function delete(MenuItem $item, Request $request): Response
    {
        $menu = $item->getMenu();
        if (null === $menu) {
            throw $this->createNotFoundException('Menu item must belong to a menu.');
        }

        $entityId = $this->getEntityId($item);
        $menuId = $menu->getId();
        $token = $request->request->get('_token_delete');

        if (null !== $token && '' !== $token && $this->isCsrfTokenValid('delete'.$entityId, (string) $token)) {
            $this->persistenceManager->delete($item, $request);
            $this->addFlash('success', $this->getDeleteSuccessMessage($item));
        } else {
            $this->addFlash('error', $this->getInvalidCsrfMessage());
        }

        return $this->redirectToRoute('admin_menu_edit', ['id' => $menuId]);
    }
}
