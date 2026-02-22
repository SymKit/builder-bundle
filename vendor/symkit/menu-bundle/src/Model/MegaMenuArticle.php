<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Model;

final readonly class MegaMenuArticle
{
    public function __construct(
        public string $title,
        public string $description,
        public string $url,
        public string $image,
        public string $date,
        public string $category,
    ) {
    }
}
