<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Tests\Unit\Crud\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symkit\CrudBundle\Contract\CrudPersistenceManagerInterface;
use Symkit\CrudBundle\Controller\AbstractCrudController;
use Symkit\MetadataBundle\Contract\PageContextBuilderInterface;

final class AbstractCrudControllerTest extends TestCase
{
    private CrudPersistenceManagerInterface&MockObject $persistenceManager;
    private PageContextBuilderInterface&MockObject $pageContextBuilder;
    private TranslatorInterface&MockObject $translator;
    private Container $container;

    protected function setUp(): void
    {
        $this->persistenceManager = $this->createMock(CrudPersistenceManagerInterface::class);
        $this->pageContextBuilder = $this->createMock(PageContextBuilderInterface::class);
        $this->pageContextBuilder->method('setTitle')->willReturnSelf();
        $this->pageContextBuilder->method('setDescription')->willReturnSelf();

        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')->willReturnCallback(
            fn (string $id, array $parameters, string $domain) => strtr($id, $parameters).' (translated)',
        );

        $this->container = new Container();
        $this->container->set('translator', $this->translator);
    }

    public function testGetEntityIdWithValidEntity(): void
    {
        $controller = $this->createController();

        $entity = new class {
            public function getId(): int
            {
                return 42;
            }
        };

        $id = $controller->publicGetEntityId($entity);
        self::assertSame(42, $id);
    }

    public function testGetEntityIdThrowsForEntityWithoutGetId(): void
    {
        $controller = $this->createController();
        $entity = new \stdClass();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('must implement a getId() method');

        $controller->publicGetEntityId($entity);
    }

    public function testGetEntityLabelWithStringableEntity(): void
    {
        $controller = $this->createController();

        $entity = new class implements \Stringable {
            public function __toString(): string
            {
                return 'My Entity';
            }

            public function getId(): int
            {
                return 1;
            }
        };

        self::assertSame('My Entity', $controller->publicGetEntityLabel($entity));
    }

    public function testGetEntityLabelFallsBackToClassAndId(): void
    {
        $controller = $this->createController();

        $entity = new class {
            public function getId(): int
            {
                return 99;
            }
        };

        $label = $controller->publicGetEntityLabel($entity);
        self::assertSame($entity::class.'#99', $label);
    }

    public function testGetBaseLayout(): void
    {
        $controller = $this->createController();
        self::assertSame('admin/layout/base.html.twig', $controller->publicGetBaseLayout());
    }

    public function testGetNewValidationGroups(): void
    {
        $controller = $this->createController();
        self::assertSame(['create'], $controller->publicGetNewValidationGroups());
    }

    public function testGetEditValidationGroups(): void
    {
        $controller = $this->createController();
        self::assertSame(['edit'], $controller->publicGetEditValidationGroups());
    }

    public function testGetNewFormOptionsContainsValidationGroups(): void
    {
        $controller = $this->createController();
        $entity = new \stdClass();

        $options = $controller->publicGetNewFormOptions($entity);

        self::assertArrayHasKey('validation_groups', $options);
        self::assertSame(['create'], $options['validation_groups']);
    }

    public function testGetEditFormOptionsContainsValidationGroups(): void
    {
        $controller = $this->createController();
        $entity = new \stdClass();

        $options = $controller->publicGetEditFormOptions($entity);

        self::assertArrayHasKey('validation_groups', $options);
        self::assertSame(['edit'], $options['validation_groups']);
    }

    public function testTemplateDefaults(): void
    {
        $controller = $this->createController();

        self::assertSame('@SymkitCrud/crud/entity_form.html.twig', $controller->publicGetNewTemplate());
        self::assertSame('@SymkitCrud/crud/entity_form.html.twig', $controller->publicGetEditTemplate());
        self::assertSame('@SymkitCrud/crud/show.html.twig', $controller->publicGetShowTemplate());
        self::assertSame('@SymkitCrud/crud/index.html.twig', $controller->publicGetIndexTemplate());
    }

    public function testTranslatableMessages(): void
    {
        $controller = $this->createController();

        $title = $controller->publicGetIndexPageTitle();
        self::assertInstanceOf(TranslatableMessage::class, $title);

        $description = $controller->publicGetIndexPageDescription();
        self::assertInstanceOf(TranslatableMessage::class, $description);

        $createLabel = $controller->publicGetCreateLabel();
        self::assertInstanceOf(TranslatableMessage::class, $createLabel);

        $csrfMessage = $controller->publicGetInvalidCsrfMessage();
        self::assertInstanceOf(TranslatableMessage::class, $csrfMessage);
    }

    public function testSuccessMessages(): void
    {
        $controller = $this->createController();
        $entity = new class implements \Stringable {
            public function __toString(): string
            {
                return 'Test Entity';
            }
        };

        $createMsg = $controller->publicGetCreateSuccessMessage($entity);
        self::assertInstanceOf(TranslatableMessage::class, $createMsg);

        $updateMsg = $controller->publicGetUpdateSuccessMessage($entity);
        self::assertInstanceOf(TranslatableMessage::class, $updateMsg);

        $deleteMsg = $controller->publicGetDeleteSuccessMessage($entity);
        self::assertInstanceOf(TranslatableMessage::class, $deleteMsg);
    }

    public function testConfigureListFieldsDefaultsToEmptyArray(): void
    {
        $controller = $this->createController();
        self::assertSame([], $controller->publicConfigureListFields());
    }

    public function testConfigureShowFieldsDelegatesToListFields(): void
    {
        $controller = $this->createController();
        self::assertSame($controller->publicConfigureListFields(), $controller->publicConfigureShowFields());
    }

    public function testConfigureShowSectionsHasGeneralSection(): void
    {
        $controller = $this->createController();
        $sections = $controller->publicConfigureShowSections();

        self::assertArrayHasKey('general', $sections);
        self::assertSame('crud.show.section.general.label', $sections['general']['label']);
        self::assertArrayHasKey('icon', $sections['general']);
        self::assertArrayHasKey('description', $sections['general']);
        self::assertArrayHasKey('fields', $sections['general']);
    }

    public function testConfigureSearchFieldsDefaultsToEmptyArray(): void
    {
        $controller = $this->createController();
        self::assertSame([], $controller->publicConfigureSearchFields());
    }

    public function testRenderIndexSetsPageContext(): void
    {
        $controller = $this->createController();
        $request = new Request();

        $this->pageContextBuilder
            ->expects(self::once())
            ->method('setTitle')
            ->with('crud.index.title (translated)');

        $this->pageContextBuilder
            ->expects(self::once())
            ->method('setDescription')
            ->with('crud.index.description (translated)');

        $controller->publicRenderIndex($request, [
            'template' => 'custom_index.twig',
            'create_route' => 'custom_create',
            'create_label' => 'Custom Label',
            'template_vars' => ['custom_var' => true],
        ]);

        self::assertSame('custom_index.twig', $controller->lastRenderView);
        self::assertTrue($controller->lastRenderParameters['custom_var']);
        self::assertSame('custom_create', $controller->lastRenderParameters['create_route']);
        self::assertSame('Custom Label', $controller->lastRenderParameters['create_label']);
    }

    public function testRenderIndexDefaults(): void
    {
        $controller = $this->createController();
        $controller->publicRenderIndex(new Request());

        self::assertSame('@SymkitCrud/crud/index.html.twig', $controller->lastRenderView);
        self::assertInstanceOf(TranslatableMessage::class, $controller->lastRenderParameters['page_title']);
        self::assertInstanceOf(TranslatableMessage::class, $controller->lastRenderParameters['page_description']);
        self::assertSame('app_test_entity_create', $controller->lastRenderParameters['create_route']);
        self::assertInstanceOf(TranslatableMessage::class, $controller->lastRenderParameters['create_label']);
    }

    public function testRenderNewSetsPageContext(): void
    {
        $controller = $this->createController();
        $request = new Request();
        $entity = new \stdClass();

        $this->pageContextBuilder
            ->expects(self::once())
            ->method('setTitle')
            ->with('Custom Title');

        $this->pageContextBuilder
            ->expects(self::once())
            ->method('setDescription')
            ->with('Custom Description');

        $controller->publicRenderNew($entity, $request, [
            'page_title' => 'Custom Title',
            'page_description' => 'Custom Description',
        ]);
    }

    public function testRenderEditSetsPageContext(): void
    {
        $controller = $this->createController();
        $request = new Request();
        $entity = new class {
            public function getId(): int
            {
                return 1;
            }
        };

        $this->pageContextBuilder
            ->expects(self::once())
            ->method('setTitle')
            ->with('crud.page.edit_item (translated)');

        $this->pageContextBuilder
            ->expects(self::once())
            ->method('setDescription')
            ->with(null);

        $controller->publicRenderEdit($entity, $request, []);
    }

    public function testRenderShowSetsPageContext(): void
    {
        $controller = $this->createController();
        $entity = new \stdClass();

        $this->pageContextBuilder
            ->expects(self::once())
            ->method('setTitle')
            ->with('crud.page.view_details (translated)');

        $this->pageContextBuilder
            ->expects(self::once())
            ->method('setDescription')
            ->with(null);

        $controller->publicRenderShow($entity, []);
    }

    private function createController(): TestCrudController
    {
        $form = $this->createMock(\Symfony\Component\Form\FormInterface::class);
        $form->method('createView')->willReturn(new \Symfony\Component\Form\FormView());
        $form->method('isSubmitted')->willReturn(false);
        $form->method('isValid')->willReturn(false);

        $controller = new TestCrudController(
            $this->persistenceManager,
            $this->pageContextBuilder,
            $form,
        );
        $controller->setContainer($this->container);

        return $controller;
    }
}

/**
 * Concrete implementation exposing protected methods for testing.
 */
class TestCrudController extends AbstractCrudController
{
    private \Symfony\Component\Form\FormInterface $mockForm;
    public ?string $lastRenderView = null;
    public ?array $lastRenderParameters = null;
    public ?string $lastRedirectRoute = null;
    public ?array $lastRedirectParams = null;
    public array $lastFlashes = [];

    public function __construct(
        CrudPersistenceManagerInterface $persistenceManager,
        PageContextBuilderInterface $pageContextBuilder,
        ?\Symfony\Component\Form\FormInterface $mockForm = null,
    ) {
        parent::__construct($persistenceManager, $pageContextBuilder);
        if ($mockForm) {
            $this->mockForm = $mockForm;
        }
    }

    protected function getEntityClass(): string
    {
        return 'App\\Entity\\TestEntity';
    }

    protected function getFormClass(): string
    {
        return 'App\\Form\\TestEntityType';
    }

    protected function getRoutePrefix(): string
    {
        return 'app_test_entity';
    }

    // Expose protected methods for testing

    public function publicGetEntityId(object $entity): string|int
    {
        return $this->getEntityId($entity);
    }

    public function publicGetEntityLabel(object $entity): string
    {
        return $this->getEntityLabel($entity);
    }

    public function publicGetBaseLayout(): string
    {
        return $this->getBaseLayout();
    }

    public function publicGetNewValidationGroups(): array
    {
        return $this->getNewValidationGroups();
    }

    public function publicGetEditValidationGroups(): array
    {
        return $this->getEditValidationGroups();
    }

    public function publicGetNewFormOptions(object $entity): array
    {
        return $this->getNewFormOptions($entity);
    }

    public function publicGetEditFormOptions(object $entity): array
    {
        return $this->getEditFormOptions($entity);
    }

    public function publicGetNewTemplate(): string
    {
        return $this->getNewTemplate();
    }

    public function publicGetEditTemplate(): string
    {
        return $this->getEditTemplate();
    }

    public function publicGetShowTemplate(): string
    {
        return $this->getShowTemplate();
    }

    public function publicGetIndexTemplate(): string
    {
        return $this->getIndexTemplate();
    }

    public function publicGetIndexPageTitle(): TranslatableMessage
    {
        return $this->getIndexPageTitle();
    }

    public function publicGetIndexPageDescription(): TranslatableMessage
    {
        return $this->getIndexPageDescription();
    }

    public function publicGetCreateLabel(): TranslatableMessage
    {
        return $this->getCreateLabel();
    }

    public function publicGetCreateSuccessMessage(object $entity): TranslatableMessage
    {
        return $this->getCreateSuccessMessage($entity);
    }

    public function publicGetUpdateSuccessMessage(object $entity): TranslatableMessage
    {
        return $this->getUpdateSuccessMessage($entity);
    }

    public function publicGetDeleteSuccessMessage(object $entity): TranslatableMessage
    {
        return $this->getDeleteSuccessMessage($entity);
    }

    public function publicGetInvalidCsrfMessage(): TranslatableMessage
    {
        return $this->getInvalidCsrfMessage();
    }

    public function publicConfigureListFields(): array
    {
        return $this->configureListFields();
    }

    public function publicConfigureShowFields(): array
    {
        return $this->configureShowFields();
    }

    public function publicConfigureShowSections(): array
    {
        return $this->configureShowSections();
    }

    public function publicConfigureSearchFields(): array
    {
        return $this->configureSearchFields();
    }

    public function publicRenderIndex(Request $request, array $options = []): Response
    {
        return $this->renderIndex($request, $options);
    }

    public function publicRenderNew(object $entity, Request $request, array $options = []): Response
    {
        return $this->renderNew($entity, $request, $options);
    }

    public function publicRenderEdit(object $entity, Request $request, array $options = []): Response
    {
        return $this->renderEdit($entity, $request, $options);
    }

    public function publicRenderShow(object $entity, array $options = []): Response
    {
        return $this->renderShow($entity, $options);
    }

    // Override render and createForm to simulate base controller without full container
    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $this->lastRenderView = $view;
        $this->lastRenderParameters = $parameters;

        return new Response('dummy render');
    }

    protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $this->lastRedirectRoute = $route;
        $this->lastRedirectParams = $parameters;

        return new \Symfony\Component\HttpFoundation\RedirectResponse('dummy_redirect');
    }

    protected function addFlash(string $type, mixed $message): void
    {
        $this->lastFlashes[$type][] = $message;
    }

    protected function createForm(string $type, mixed $data = null, array $options = []): \Symfony\Component\Form\FormInterface
    {
        return $this->mockForm;
    }
}
