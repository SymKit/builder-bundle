<?php

declare(strict_types=1);

namespace Symkit\MenuBundle\Mapper;

use Symkit\MenuBundle\Contract\MenuItemEntityInterface;
use Symkit\MenuBundle\Contract\MenuModelFactoryInterface;
use Symkit\MenuBundle\Model\MegaMenuArticle;
use Symkit\MenuBundle\Model\MegaMenuSection;
use Symkit\MenuBundle\Model\MenuItemInterface;
use Symkit\MenuBundle\Model\MenuMega;

final readonly class MegaMenuMapper implements MenuItemMapperInterface
{
    public function supports(MenuItemEntityInterface $entity): bool
    {
        return MenuItemEntityInterface::TYPE_MEGA === $entity->getType();
    }

    public function map(MenuItemEntityInterface $entity, MenuModelFactoryInterface $factory): MenuItemInterface
    {
        $sections = [];
        foreach ($entity->getChildren() as $sectionEntity) {
            $sectionItems = [];
            foreach ($sectionEntity->getChildren() as $subItem) {
                if ($subItemModel = $factory->createModel($subItem)) {
                    $sectionItems[] = $subItemModel;
                }
            }

            $sections[] = new MegaMenuSection(
                title: $sectionEntity->getLabel() ?? '',
                items: $sectionItems,
            );
        }

        $options = $entity->getOptions();
        $recentArticles = [];
        if (isset($options['recentArticles']) && \is_array($options['recentArticles'])) {
            foreach ($options['recentArticles'] as $article) {
                if (!\is_array($article)) {
                    continue;
                }
                $recentArticles[] = new MegaMenuArticle(
                    title: \is_string($article['title'] ?? null) ? $article['title'] : '',
                    description: \is_string($article['description'] ?? null) ? $article['description'] : '',
                    url: \is_string($article['url'] ?? null) ? $article['url'] : '#',
                    image: \is_string($article['image'] ?? null) ? $article['image'] : '',
                    date: \is_string($article['date'] ?? null) ? $article['date'] : '',
                    category: \is_string($article['category'] ?? null) ? $article['category'] : '',
                );
            }
        }

        return new MenuMega(
            id: $entity->getIdentifier() ?? (string) $entity->getId(),
            label: $entity->getLabel() ?? '',
            sections: $sections,
            recentArticles: $recentArticles,
            icon: $entity->getIcon(),
        );
    }
}
