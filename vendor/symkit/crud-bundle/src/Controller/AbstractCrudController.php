<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symkit\CrudBundle\Contract\CrudPersistenceManagerInterface;
use Symkit\MetadataBundle\Contract\PageContextBuilderInterface;

/**
 * Base CRUD controller for host applications to extend.
 *
 * Exception to the "never extend AbstractController" rule: this class is the
 * bundle's designated extension point for apps. Extending Symfony's
 * AbstractController is intentional to provide render(), redirectToRoute(),
 * addFlash(), etc. without requiring apps to inject each service.
 */
abstract class AbstractCrudController extends AbstractController
{
    public function __construct(
        protected readonly CrudPersistenceManagerInterface $persistenceManager,
        protected readonly PageContextBuilderInterface $pageContextBuilder,
    ) {
    }

    abstract protected function getEntityClass(): string;

    abstract protected function getFormClass(): string;

    abstract protected function getRoutePrefix(): string;

    protected function getBaseLayout(): string
    {
        return 'admin/layout/base.html.twig';
    }

    /** @return list<string> */
    protected function getNewValidationGroups(): array
    {
        return ['create'];
    }

    /** @return list<string> */
    protected function getEditValidationGroups(): array
    {
        return ['edit'];
    }

    /** @return array{validation_groups: list<string>} */
    protected function getNewFormOptions(object $entity): array
    {
        return [
            'validation_groups' => $this->getNewValidationGroups(),
        ];
    }

    /** @return array{validation_groups: list<string>} */
    protected function getEditFormOptions(object $entity): array
    {
        return [
            'validation_groups' => $this->getEditValidationGroups(),
        ];
    }

    protected function getNewTemplate(): string
    {
        return '@SymkitCrud/crud/entity_form.html.twig';
    }

    protected function getEditTemplate(): string
    {
        return '@SymkitCrud/crud/entity_form.html.twig';
    }

    protected function getShowTemplate(): string
    {
        return '@SymkitCrud/crud/show.html.twig';
    }

    protected function getIndexTemplate(): string
    {
        return '@SymkitCrud/crud/index.html.twig';
    }

    protected function getIndexPageTitle(): TranslatableMessage
    {
        return new TranslatableMessage('crud.index.title', [], 'SymkitCrud');
    }

    protected function getIndexPageDescription(): TranslatableMessage
    {
        return new TranslatableMessage('crud.index.description', [], 'SymkitCrud');
    }

    protected function getCreateLabel(): TranslatableMessage
    {
        return new TranslatableMessage('crud.action.create', [], 'SymkitCrud');
    }

    protected function getNewPageTitle(): TranslatableMessage
    {
        return new TranslatableMessage('crud.page.create_new', [], 'SymkitCrud');
    }

    protected function getEditPageTitle(): TranslatableMessage
    {
        return new TranslatableMessage('crud.page.edit_item', [], 'SymkitCrud');
    }

    protected function getShowPageTitle(): TranslatableMessage
    {
        return new TranslatableMessage('crud.page.view_details', [], 'SymkitCrud');
    }

    /** @return array<string, array<string, mixed>> */
    protected function configureListFields(): array
    {
        return [];
    }

    /** @return array<string, array<string, mixed>> */
    protected function configureShowFields(): array
    {
        return $this->configureListFields();
    }

    /** @return array<string, array{label: string, icon: string, description: string, fields: array<string, array<string, mixed>>}> */
    protected function configureShowSections(): array
    {
        return [
            'general' => [
                'label' => 'crud.show.section.general.label',
                'icon' => 'heroicons:information-circle-20-solid',
                'description' => 'crud.show.section.general.description',
                'fields' => $this->configureShowFields(),
            ],
        ];
    }

    /** @return list<string> */
    protected function configureSearchFields(): array
    {
        return [];
    }

    protected function getEntityId(object $entity): string|int
    {
        if (!method_exists($entity, 'getId')) {
            throw new \LogicException(\sprintf('Entity of class "%s" must implement a getId() method. Override getEntityId() to customize.', $entity::class));
        }

        return $entity->getId();
    }

    protected function getEntityLabel(object $entity): string
    {
        if ($entity instanceof \Stringable) {
            return (string) $entity;
        }

        return $entity::class.'#'.$this->getEntityId($entity);
    }

    /** @param array<string, mixed> $options */
    protected function renderIndex(Request $request, array $options = []): Response
    {
        /** @var string $template */
        $template = $options['template'] ?? $this->getIndexTemplate();
        /** @var array<string, mixed> $templateVars */
        $templateVars = $options['template_vars'] ?? [];

        /** @var string|TranslatableMessage|null $title */
        $title = $options['page_title'] ?? $this->getIndexPageTitle();
        /** @var string|TranslatableMessage|null $description */
        $description = $options['page_description'] ?? $this->getIndexPageDescription();

        $this->pageContextBuilder
            ->setTitle($this->resolveTranslatable($title))
            ->setDescription($this->resolveTranslatable($description));

        return $this->render($template, array_merge([
            'page_title' => $title,
            'page_description' => $description,
            'base_layout' => $this->getBaseLayout(),
            'entity_class' => $this->getEntityClass(),
            'search_fields' => $this->configureSearchFields(),
            'list_fields' => $this->configureListFields(),
            'create_route' => $options['create_route'] ?? $this->getRoutePrefix().'_create',
            'create_label' => $options['create_label'] ?? $this->getCreateLabel(),
        ], $templateVars));
    }

    protected function getCreateSuccessMessage(object $entity): TranslatableMessage
    {
        return new TranslatableMessage('crud.success.created', ['%entity%' => $this->getEntityLabel($entity)], 'SymkitCrud');
    }

    protected function getUpdateSuccessMessage(object $entity): TranslatableMessage
    {
        return new TranslatableMessage('crud.success.updated', ['%entity%' => $this->getEntityLabel($entity)], 'SymkitCrud');
    }

    protected function getDeleteSuccessMessage(object $entity): TranslatableMessage
    {
        return new TranslatableMessage('crud.success.deleted', ['%entity%' => $this->getEntityLabel($entity)], 'SymkitCrud');
    }

    protected function getInvalidCsrfMessage(): TranslatableMessage
    {
        return new TranslatableMessage('crud.error.invalid_csrf', [], 'SymkitCrud');
    }

    /**
     * @param array<string, mixed>            $formOptions
     * @param \Closure(): TranslatableMessage $successMessage
     * @param array<string, mixed>            $options
     */
    private function handleForm(
        object $entity,
        Request $request,
        array $formOptions,
        \Closure $successMessage,
        array $options,
    ): HandleFormResult {
        $form = $this->createForm($this->getFormClass(), $entity, $formOptions);
        $form->handleRequest($request);

        $redirect = null;

        if ($form->isSubmitted() && $form->isValid()) {
            if (isset($options['_persist'])) {
                $this->persistenceManager->persist($entity, $form, $request);
            } else {
                $this->persistenceManager->update($entity, $form, $request);
            }

            $this->addFlash('success', $successMessage());

            /** @var string $redirectRoute */
            $redirectRoute = $options['redirect_route'] ?? $this->getRoutePrefix().'_list';
            /** @var array<string, mixed> $redirectParams */
            $redirectParams = $options['redirect_params'] ?? [];

            $redirect = $this->redirectToRoute($redirectRoute, $redirectParams);
        }

        return new HandleFormResult($redirect, $form->createView());
    }

    /** @param array<string, mixed> $options */
    protected function renderNew(object $entity, Request $request, array $options = []): Response
    {
        $result = $this->handleForm(
            $entity,
            $request,
            $this->getNewFormOptions($entity),
            fn () => $this->getCreateSuccessMessage($entity),
            array_merge($options, ['_persist' => true]),
        );

        if ($result->isRedirect()) {
            /** @var Response $redirect */
            $redirect = $result->redirect;

            return $redirect;
        }

        /** @var string $template */
        $template = $options['template'] ?? $this->getNewTemplate();
        /** @var array<string, mixed> $templateVars */
        $templateVars = $options['template_vars'] ?? [];

        /** @var string|TranslatableMessage|null $title */
        $title = $options['page_title'] ?? $this->getNewPageTitle();
        /** @var string|TranslatableMessage|null $description */
        $description = $options['page_description'] ?? '';

        $this->pageContextBuilder
            ->setTitle($this->resolveTranslatable($title))
            ->setDescription($this->resolveTranslatable($description));

        return $this->render($template, array_merge([
            'form' => $result->formView,
            'base_layout' => $this->getBaseLayout(),
            'back_route' => $this->getRoutePrefix().'_list',
            'show_back' => true,
            'page_title' => $title,
            'page_description' => $description,
            'after_form_template' => $options['after_form_template'] ?? null,
            'extra_nav_items_template' => $options['extra_nav_items_template'] ?? null,
        ], $templateVars));
    }

    /** @param array<string, mixed> $options */
    protected function renderEdit(object $entity, Request $request, array $options = []): Response
    {
        $result = $this->handleForm(
            $entity,
            $request,
            $this->getEditFormOptions($entity),
            fn () => $this->getUpdateSuccessMessage($entity),
            $options,
        );

        if ($result->isRedirect()) {
            /** @var Response $redirect */
            $redirect = $result->redirect;

            return $redirect;
        }

        $entityId = $this->getEntityId($entity);

        /** @var string $template */
        $template = $options['template'] ?? $this->getEditTemplate();
        /** @var array<string, mixed> $templateVars */
        $templateVars = $options['template_vars'] ?? [];

        /** @var string|TranslatableMessage|null $title */
        $title = $options['page_title'] ?? $this->getEditPageTitle();
        /** @var string|TranslatableMessage|null $description */
        $description = $options['page_description'] ?? '';

        $this->pageContextBuilder
            ->setTitle($this->resolveTranslatable($title))
            ->setDescription($this->resolveTranslatable($description));

        return $this->render($template, array_merge([
            'form' => $result->formView,
            'base_layout' => $this->getBaseLayout(),
            'back_route' => $options['back_route'] ?? $this->getRoutePrefix().'_list',
            'show_back' => $options['show_back'] ?? true,
            'show_delete' => $options['show_delete'] ?? true,
            'delete_route' => $options['delete_route'] ?? $this->getRoutePrefix().'_delete',
            'delete_route_params' => $options['delete_route_params'] ?? ['id' => $entityId],
            'entity_id' => $entityId,
            'page_title' => $title,
            'page_description' => $description,
            'after_form_template' => $options['after_form_template'] ?? null,
            'extra_nav_items_template' => $options['extra_nav_items_template'] ?? null,
        ], $templateVars));
    }

    /** @param array<string, mixed> $options */
    protected function renderShow(object $entity, array $options = []): Response
    {
        /** @var string $template */
        $template = $options['template'] ?? $this->getShowTemplate();
        /** @var array<string, mixed> $templateVars */
        $templateVars = $options['template_vars'] ?? [];

        /** @var string|TranslatableMessage|null $title */
        $title = $options['page_title'] ?? $this->getShowPageTitle();
        /** @var string|TranslatableMessage|null $description */
        $description = $options['page_description'] ?? '';

        $this->pageContextBuilder
            ->setTitle($this->resolveTranslatable($title))
            ->setDescription($this->resolveTranslatable($description));

        return $this->render($template, array_merge([
            'entity' => $entity,
            'base_layout' => $this->getBaseLayout(),
            'back_route' => $options['back_route'] ?? $this->getRoutePrefix().'_list',
            'show_back' => $options['show_back'] ?? true,
            'page_title' => $title,
            'page_description' => $description,
            'show_sections' => $options['show_sections'] ?? $this->configureShowSections(),
        ], $templateVars));
    }

    protected function performDelete(object $entity, Request $request): Response
    {
        $entityId = $this->getEntityId($entity);
        $token = $request->request->get('_token_delete');
        if (null !== $token && '' !== $token && $this->isCsrfTokenValid('delete'.$entityId, (string) $token)) {
            $this->persistenceManager->delete($entity, $request);

            $this->addFlash('success', $this->getDeleteSuccessMessage($entity));
        } else {
            $this->addFlash('error', $this->getInvalidCsrfMessage());
        }

        return $this->redirectToRoute($this->getRoutePrefix().'_list');
    }

    protected function resolveTranslatable(string|TranslatableMessage|null $message): ?string
    {
        if (null === $message || '' === $message) {
            return null;
        }

        if ($message instanceof TranslatableMessage) {
            /** @var TranslatorInterface|null $translator */
            $translator = $this->container->has('translator') ? $this->container->get('translator') : null;

            if (null !== $translator) {
                return $translator->trans($message->getMessage(), $message->getParameters(), $message->getDomain());
            }

            // Fallback for missing translator: return plain message
            return strtr($message->getMessage(), $message->getParameters());
        }

        return $message;
    }
}
