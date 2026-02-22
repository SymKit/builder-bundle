<?php

declare(strict_types=1);

namespace Symkit\MetadataBundle\Enum;

enum ArticleType: string
{
    case ARTICLE = 'Article';
    case BLOG_POSTING = 'BlogPosting';
    case NEWS_ARTICLE = 'NewsArticle';
}
