<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Contract;

interface IconProviderInterface
{
    /** @return array<string, string> */
    public function getAllIcons(?string $style = '20-solid'): array;
}
