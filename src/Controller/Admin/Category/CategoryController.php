<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Controller\Admin\Category;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symkit\BuilderBundle\Entity\BlockCategory;
use Symkit\BuilderBundle\Form\BlockCategoryType;
use Symkit\CrudBundle\Controller\AbstractCrudController;
use Symkit\MenuBundle\Attribute\ActiveMenu;
use Symkit\MetadataBundle\Attribute\Breadcrumb;
use Symkit\MetadataBundle\Attribute\Seo;

final class CategoryController extends AbstractCrudController
{
    /**
     * @param class-string $blockCategoryClass
     */
    public function __construct(
        private readonly string $blockCategoryClass,
    ) {
    }

    #[Seo(title: 'page.block_categories')]
    #[Breadcrumb(context: 'admin')]
    #[ActiveMenu('admin', 'blocks')]
    public function list(Request $request): Response
    {
        return $this->renderIndex($request, [
            'page_title' => 'page.block_categories',
        ]);
    }

    #[Seo(title: 'page.create_category')]
    #[Breadcrumb(context: 'admin', items: [['label' => 'page.categories', 'route' => 'admin_block_category_list']])]
    #[ActiveMenu('admin', 'blocks')]
    public function create(Request $request): Response
    {
        $entity = new ($this->blockCategoryClass)();

        return $this->renderNew($entity, $request, [
            'page_title' => 'page.create_category',
        ]);
    }

    #[Seo(title: 'page.edit_category')]
    #[Breadcrumb(context: 'admin', items: [['label' => 'page.categories', 'route' => 'admin_block_category_list']])]
    #[ActiveMenu('admin', 'blocks')]
    public function edit(BlockCategory $category, Request $request): Response
    {
        return $this->renderEdit($category, $request, [
            'page_title' => 'page.edit_category',
        ]);
    }

    public function delete(BlockCategory $category, Request $request): Response
    {
        return $this->performDelete($category, $request);
    }

    protected function getEntityClass(): string
    {
        return $this->blockCategoryClass;
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
                'label' => 'list.label',
                'sortable' => true,
            ],
            'code' => [
                'label' => 'list.code',
                'sortable' => true,
                'cell_class' => 'font-mono text-xs',
            ],
            'position' => [
                'label' => 'list.position',
                'sortable' => true,
            ],
            'blocks' => [
                'label' => 'list.blocks',
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
