<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Tests\Unit\Crud\Component;

use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\TestCase;
use Symkit\CrudBundle\Component\CrudList;
use Symkit\CrudBundle\Contract\CrudListProviderInterface;

final class CrudListTest extends TestCase
{
    public function testOnFilterUpdatedSetsFiltersAndResetsPage(): void
    {
        $provider = $this->createMock(CrudListProviderInterface::class);
        $component = new CrudList($provider);
        $component->page = 5;

        $component->onFilterUpdated(['q' => 'test']);

        self::assertSame(['q' => 'test'], $component->filters);
        self::assertSame(1, $component->page);
    }

    public function testOnFilterUpdatedDefaultsToEmptyArray(): void
    {
        $provider = $this->createMock(CrudListProviderInterface::class);
        $component = new CrudList($provider);
        $component->page = 3;

        $component->onFilterUpdated();

        self::assertSame([], $component->filters);
        self::assertSame(1, $component->page);
    }

    public function testChangeSortTogglesSameColumnDirection(): void
    {
        $provider = $this->createMock(CrudListProviderInterface::class);
        $component = new CrudList($provider);
        $component->listFields = ['name' => ['sortable' => true]];
        $component->sortBy = 'name';
        $component->sortDirection = 'asc';

        $component->changeSort('name');

        self::assertSame('name', $component->sortBy);
        self::assertSame('desc', $component->sortDirection);
    }

    public function testChangeSortSetsAscForNewColumn(): void
    {
        $provider = $this->createMock(CrudListProviderInterface::class);
        $component = new CrudList($provider);
        $component->listFields = [
            'name' => ['sortable' => true],
            'email' => ['sortable' => true],
        ];
        $component->sortBy = 'name';
        $component->sortDirection = 'desc';

        $component->changeSort('email');

        self::assertSame('email', $component->sortBy);
        self::assertSame('asc', $component->sortDirection);
    }

    public function testChangeSortIgnoresNonSortableColumn(): void
    {
        $provider = $this->createMock(CrudListProviderInterface::class);
        $component = new CrudList($provider);
        $component->listFields = ['name' => ['sortable' => false]];
        $component->sortBy = null;
        $component->sortDirection = 'asc';

        $component->changeSort('name');

        self::assertNull($component->sortBy);
    }

    public function testChangeSortIgnoresUnknownColumn(): void
    {
        $provider = $this->createMock(CrudListProviderInterface::class);
        $component = new CrudList($provider);
        $component->listFields = ['name' => ['sortable' => true]];
        $component->sortBy = 'name';

        $component->changeSort('unknown');

        self::assertSame('name', $component->sortBy);
    }

    public function testGetMinKeyReturnsCorrectValue(): void
    {
        $provider = $this->createMock(CrudListProviderInterface::class);
        $component = new CrudList($provider);

        $component->page = 1;
        $component->limit = 25;
        self::assertSame(1, $component->getMinKey());

        $component->page = 2;
        $component->limit = 25;
        self::assertSame(26, $component->getMinKey());

        $component->page = 3;
        $component->limit = 10;
        self::assertSame(21, $component->getMinKey());
    }

    public function testGetMaxKeyReturnsCorrectValue(): void
    {
        $provider = $this->createMock(CrudListProviderInterface::class);
        $component = new CrudList($provider);

        $component->page = 1;
        $component->limit = 25;
        self::assertSame(25, $component->getMaxKey(100));

        $component->page = 4;
        $component->limit = 25;
        self::assertSame(100, $component->getMaxKey(100));
    }

    public function testGetMaxKeyClampedByTotal(): void
    {
        $provider = $this->createMock(CrudListProviderInterface::class);
        $component = new CrudList($provider);

        $component->page = 2;
        $component->limit = 25;
        self::assertSame(30, $component->getMaxKey(30));
    }

    public function testGetEntitiesDelegatesToProvider(): void
    {
        $paginator = $this->createMock(Paginator::class);
        $provider = $this->createMock(CrudListProviderInterface::class);
        $provider->expects(self::once())
            ->method('getEntities')
            ->with('App\\Entity\\Post', ['q' => 'test'], ['title'], 'title', 'asc', 2, 10)
            ->willReturn($paginator);

        $component = new CrudList($provider);
        $component->entityClass = 'App\\Entity\\Post';
        $component->filters = ['q' => 'test'];
        $component->searchFields = ['title'];
        $component->sortBy = 'title';
        $component->sortDirection = 'asc';
        $component->page = 2;
        $component->limit = 10;
        $component->listFields = ['title' => ['sortable' => true]];

        $result = $component->getEntities();

        self::assertSame($paginator, $result);
    }

    public function testGetEntitiesAutoSelectsFirstSortableField(): void
    {
        $paginator = $this->createMock(Paginator::class);
        $provider = $this->createMock(CrudListProviderInterface::class);
        $provider->expects(self::once())
            ->method('getEntities')
            ->with(
                self::anything(),
                self::anything(),
                self::anything(),
                'email',
                self::anything(),
                self::anything(),
                self::anything(),
            )
            ->willReturn($paginator);

        $component = new CrudList($provider);
        $component->entityClass = 'App\\Entity\\User';
        $component->listFields = [
            'name' => ['sortable' => false],
            'email' => ['sortable' => true],
            'role' => ['sortable' => true],
        ];
        $component->sortBy = null;

        $component->getEntities();
    }

    public function testDefaultPropertyValues(): void
    {
        $provider = $this->createMock(CrudListProviderInterface::class);
        $component = new CrudList($provider);

        self::assertSame([], $component->listFields);
        self::assertSame([], $component->searchFields);
        self::assertSame([], $component->filters);
        self::assertSame(1, $component->page);
        self::assertSame(25, $component->limit);
        self::assertNull($component->sortBy);
        self::assertSame('asc', $component->sortDirection);
    }

    public function testGetEntitiesNormalizesInvalidSortDirection(): void
    {
        $paginator = $this->createMock(Paginator::class);
        $provider = $this->createMock(CrudListProviderInterface::class);
        $provider->expects(self::once())
            ->method('getEntities')
            ->with(
                self::anything(),
                self::anything(),
                self::anything(),
                self::anything(),
                'asc',
                self::anything(),
                self::anything(),
            )
            ->willReturn($paginator);

        $component = new CrudList($provider);
        $component->entityClass = 'App\\Entity\\Post';
        $component->sortDirection = 'INVALID';
        $component->listFields = ['title' => ['sortable' => true]];

        $component->getEntities();

        self::assertSame('asc', $component->sortDirection);
    }
}
