<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Controller\Admin;

use Symkit\BuilderBundle\Entity\Block;
use Symkit\BuilderBundle\Form\BlockType;
use Symkit\CrudBundle\Controller\AbstractCrudController;
use Symkit\MenuBundle\Attribute\ActiveMenu;
use Symkit\MetadataBundle\Attribute\Breadcrumb;
use Symkit\MetadataBundle\Attribute\Seo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/blocks')]
final class BlockController extends AbstractCrudController
{
    #[Route('', name: 'admin_block_list')]
    #[Seo(title: 'Block Management', description: 'Manage available content blocks.')]
    #[Breadcrumb(context: 'admin')]
    #[ActiveMenu('admin', 'blocks')]
    public function list(Request $request): Response
    {
        return $this->renderIndex($request, [
            'page_title' => 'Block Management',
        ]);
    }

    #[Route('/create', name: 'admin_block_create')]
    #[Seo(title: 'Create Block')]
    #[Breadcrumb(context: 'admin', items: [['label' => 'Blocks', 'route' => 'admin_block_list']])]
    #[ActiveMenu('admin', 'blocks')]
    public function create(Request $request): Response
    {
        return $this->renderNew(new Block(), $request, [
            'page_title' => 'Create Block',
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_block_edit')]
    #[Seo(title: 'Edit Block')]
    #[Breadcrumb(context: 'admin', items: [['label' => 'Blocks', 'route' => 'admin_block_list']])]
    #[ActiveMenu('admin', 'blocks')]
    public function edit(Block $block, Request $request): Response
    {
        return $this->renderEdit($block, $request, [
            'page_title' => 'Edit Block',
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_block_delete', methods: ['POST'])]
    public function delete(Block $block, Request $request): Response
    {
        return $this->performDelete($block, $request);
    }

    protected function getEntityClass(): string
    {
        return Block::class;
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
                'label' => 'Code',
                'sortable' => true,
                'cell_class' => 'font-mono text-xs',
            ],
            'label' => [
                'label' => 'Label',
                'sortable' => true,
            ],
            'category' => [
                'label' => 'Category',
                'sortable' => true,
            ],
            'isActive' => [
                'label' => 'Status',
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
