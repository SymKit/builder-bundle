<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symkit\BuilderBundle\Entity\Block;
use Symkit\BuilderBundle\Form\BlockType;
use Symkit\CrudBundle\Controller\AbstractCrudController;
use Symkit\MenuBundle\Attribute\ActiveMenu;
use Symkit\MetadataBundle\Attribute\Breadcrumb;
use Symkit\MetadataBundle\Attribute\Seo;

final class BlockController extends AbstractCrudController
{
    /**
     * @param class-string $blockClass
     */
    public function __construct(
        private readonly string $blockClass,
    ) {
    }

    #[Seo(title: 'page.block_management', description: 'page.block_management_description')]
    #[Breadcrumb(context: 'admin')]
    #[ActiveMenu('admin', 'blocks')]
    public function list(Request $request): Response
    {
        return $this->renderIndex($request, [
            'page_title' => 'page.block_management',
        ]);
    }

    #[Seo(title: 'page.create_block')]
    #[Breadcrumb(context: 'admin', items: [['label' => 'page.blocks', 'route' => 'admin_block_list']])]
    #[ActiveMenu('admin', 'blocks')]
    public function create(Request $request): Response
    {
        $entity = new ($this->blockClass)();

        return $this->renderNew($entity, $request, [
            'page_title' => 'page.create_block',
        ]);
    }

    #[Seo(title: 'page.edit_block')]
    #[Breadcrumb(context: 'admin', items: [['label' => 'page.blocks', 'route' => 'admin_block_list']])]
    #[ActiveMenu('admin', 'blocks')]
    public function edit(Block $block, Request $request): Response
    {
        return $this->renderEdit($block, $request, [
            'page_title' => 'page.edit_block',
        ]);
    }

    public function delete(Block $block, Request $request): Response
    {
        return $this->performDelete($block, $request);
    }

    protected function getEntityClass(): string
    {
        return $this->blockClass;
    }

    protected function getFormClass(): string
    {
        return BlockType::class;
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
        return 'admin_block';
    }

    protected function configureListFields(): array
    {
        return [
            'code' => [
                'label' => 'list.code',
                'sortable' => true,
                'cell_class' => 'font-mono text-xs',
            ],
            'label' => [
                'label' => 'list.label',
                'sortable' => true,
            ],
            'category' => [
                'label' => 'list.category',
                'sortable' => true,
            ],
            'isActive' => [
                'label' => 'list.status',
                'template' => '@SymkitCrud/crud/field/boolean.html.twig',
            ],
            'actions' => [
                'label' => '',
                'template' => '@SymkitCrud/crud/field/actions.html.twig',
                'edit_route' => 'admin_block_edit',
                'header_class' => 'text-right',
                'cell_class' => 'text-right',
            ],
        ];
    }

    protected function configureSearchFields(): array
    {
        return ['code', 'label'];
    }
}
