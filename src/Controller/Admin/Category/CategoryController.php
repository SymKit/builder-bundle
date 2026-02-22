<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Controller\Admin\Category;

use Symkit\BuilderBundle\Entity\BlockCategory;
use Symkit\BuilderBundle\Form\BlockCategoryType;
use Symkit\CrudBundle\Controller\AbstractCrudController;
use Symkit\MenuBundle\Attribute\ActiveMenu;
use Symkit\MetadataBundle\Attribute\Breadcrumb;
use Symkit\MetadataBundle\Attribute\Seo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/block-categories')]
final class CategoryController extends AbstractCrudController
{
    #[Route('', name: 'admin_block_category_list')]
    #[Seo(title: 'Block Categories')]
    #[Breadcrumb(context: 'admin')]
    #[ActiveMenu('admin', 'blocks')]
    public function list(Request $request): Response
    {
        return $this->renderIndex($request, [
            'page_title' => 'Block Categories',
        ]);
    }

    #[Route('/create', name: 'admin_block_category_create')]
    #[Seo(title: 'Create Category')]
    #[Breadcrumb(context: 'admin', items: [['label' => 'Categories', 'route' => 'admin_block_category_list']])]
    #[ActiveMenu('admin', 'blocks')]
    public function create(Request $request): Response
    {
        return $this->renderNew(new BlockCategory(), $request, [
            'page_title' => 'Create Category',
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_block_category_edit')]
    #[Seo(title: 'Edit Category')]
    #[Breadcrumb(context: 'admin', items: [['label' => 'Categories', 'route' => 'admin_block_category_list']])]
    #[ActiveMenu('admin', 'blocks')]
    public function edit(BlockCategory $category, Request $request): Response
    {
        return $this->renderEdit($category, $request, [
            'page_title' => 'Edit Category',
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_block_category_delete', methods: ['POST'])]
    public function delete(BlockCategory $category, Request $request): Response
    {
        return $this->performDelete($category, $request);
    }

    protected function getEntityClass(): string
    {
        return BlockCategory::class;
    }

    protected function getFormClass(): string
    {
        return BlockCategoryType::class;
    }

    protected function getNewTemplate(): string
    {
        return '@SymkitCrud/crud/entity_form.html.twig';
    }

    protected function getEditTemplate(): string
    {
        return '@SymkitCrud/crud/entity_form.html.twig';
    }

    protected function getRoutePrefix(): string
    {
        return 'admin_block_category';
    }

    protected function configureListFields(): array
    {
        return [
            'label' => [
                'label' => 'Label',
                'sortable' => true,
            ],
            'code' => [
                'label' => 'Code',
                'sortable' => true,
                'cell_class' => 'font-mono text-xs',
            ],
            'position' => [
                'label' => 'Position',
                'sortable' => true,
            ],
            'blocks' => [
                'label' => 'Blocks',
                'template' => '@SymkitCrud/crud/field/count.html.twig',
            ],
            'actions' => [
                'label' => '',
                'template' => '@SymkitCrud/crud/field/actions.html.twig',
                'edit_route' => 'admin_block_category_edit',
                'header_class' => 'text-right',
                'cell_class' => 'text-right',
            ],
        ];
    }

    protected function configureSearchFields(): array
    {
        return ['label', 'code'];
    }
}
