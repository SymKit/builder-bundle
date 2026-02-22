<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Twig\Component;

final class BackLink
{
    public string $route;
    /** @var array<string, mixed> */
    public array $routeParams = [];
    public string $label = 'crud.component.back_link.label';
    public string $icon = 'heroicons:arrow-left-20-solid';
}
