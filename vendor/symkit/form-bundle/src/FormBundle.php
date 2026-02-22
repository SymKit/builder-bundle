<?php

declare(strict_types=1);

namespace Symkit\FormBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symkit\FormBundle\Contract\IconProviderInterface;
use Symkit\FormBundle\Form\Extension\CheckboxRichSelectExtension;
use Symkit\FormBundle\Form\Extension\DependencyExtension;
use Symkit\FormBundle\Form\Extension\PasswordExtension;
use Symkit\FormBundle\Form\Extension\RichSelectExtension;
use Symkit\FormBundle\Form\Extension\TranslatableExtension;
use Symkit\FormBundle\Form\Extension\UrlExtension;
use Symkit\FormBundle\Form\Type\ActiveInactiveType;
use Symkit\FormBundle\Form\Type\FormSectionType;
use Symkit\FormBundle\Form\Type\IconPickerType;
use Symkit\FormBundle\Form\Type\SitemapPriorityType;
use Symkit\FormBundle\Form\Type\SlugType;
use Symkit\FormBundle\Service\HeroiconProvider;
use Symkit\FormBundle\Twig\Component\PasswordField;
use Symkit\FormBundle\Twig\Component\RichSelect;
use Symkit\FormBundle\Twig\Component\Slug;
use Symkit\FormBundle\Twig\Component\TranslatableField;

final class FormBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('form_types')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('slug')->defaultTrue()->end()
                        ->booleanNode('sitemap_priority')->defaultTrue()->end()
                        ->booleanNode('icon_picker')->defaultTrue()->end()
                        ->booleanNode('active_inactive')->defaultTrue()->end()
                        ->booleanNode('form_section')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('form_extensions')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('rich_select')->defaultTrue()->end()
                        ->booleanNode('password')->defaultTrue()->end()
                        ->booleanNode('translatable')->defaultTrue()->end()
                        ->booleanNode('url')->defaultTrue()->end()
                        ->booleanNode('dependency')->defaultTrue()->end()
                        ->booleanNode('checkbox_rich_select')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('components')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('slug')->defaultTrue()->end()
                        ->booleanNode('rich_select')->defaultTrue()->end()
                        ->booleanNode('password_field')->defaultTrue()->end()
                        ->booleanNode('translatable_field')->defaultTrue()->end()
                    ->end()
                ->end()
                ->booleanNode('twig_prepend')->defaultTrue()->end()
                ->booleanNode('asset_mapper')->defaultTrue()->end()
            ->end();
    }

    /**
     * @param array{form_types: array{slug: bool, sitemap_priority: bool, icon_picker: bool, active_inactive: bool, form_section: bool}, form_extensions: array{rich_select: bool, password: bool, translatable: bool, url: bool, dependency: bool, checkbox_rich_select: bool}, components: array{slug: bool, rich_select: bool, password_field: bool, translatable_field: bool}, twig_prepend: bool, asset_mapper: bool} $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services();
        $formTypes = $config['form_types'];
        $formExt = $config['form_extensions'];
        $components = $config['components'];

        $services->set(HeroiconProvider::class);
        $services->alias(IconProviderInterface::class, HeroiconProvider::class);
        $services->alias('form_bundle.heroicon_provider', HeroiconProvider::class)->public();

        if ($formTypes['active_inactive']) {
            $services->set(ActiveInactiveType::class)->tag('form.type');
        }
        if ($formTypes['sitemap_priority']) {
            $services->set(SitemapPriorityType::class)->tag('form.type');
        }
        if ($formTypes['icon_picker']) {
            $services->set(IconPickerType::class)->autowire()->autoconfigure()->tag('form.type');
        }
        if ($formTypes['form_section']) {
            $services->set(FormSectionType::class)->tag('form.type');
        }

        if ($formExt['checkbox_rich_select']) {
            $services->set(CheckboxRichSelectExtension::class)->tag('form.type_extension');
        }
        if ($formExt['password']) {
            $services->set(PasswordExtension::class)->tag('form.type_extension');
        }
        if ($formExt['rich_select']) {
            $services->set(RichSelectExtension::class)->tag('form.type_extension');
        }

        if ($components['rich_select']) {
            $services->set(RichSelect::class)->autowire()->autoconfigure()
                ->tag('twig.component', ['key' => 'RichSelect']);
        }
        if ($components['password_field']) {
            $services->set(PasswordField::class)->autowire()->autoconfigure()
                ->tag('twig.component', ['key' => 'PasswordField']);
        }
        if ($components['translatable_field']) {
            $services->set(TranslatableField::class)->autowire()->autoconfigure()
                ->tag('twig.component', ['key' => 'TranslatableField']);
        }

        if ($formExt['translatable']) {
            $defaultLocale = $builder->getParameter('kernel.default_locale');
            $enabledLocales = $builder->hasParameter('kernel.enabled_locales')
                ? $builder->getParameter('kernel.enabled_locales')
                : [$defaultLocale];
            $services->set(TranslatableExtension::class)
                ->arg(0, $defaultLocale)
                ->arg(1, $enabledLocales)
                ->tag('form.type_extension');
        }

        if ($formExt['url']) {
            $services->set(UrlExtension::class)->tag('form.type_extension');
        }
        if ($formExt['dependency']) {
            $services->set(DependencyExtension::class)->tag('form.type_extension');
        }

        if ($formTypes['slug']) {
            $services->set(SlugType::class)->tag('form.type');
        }
        if ($components['slug']) {
            $services->set(Slug::class)->autowire()->autoconfigure()
                ->tag('twig.component', ['key' => 'Slug']);
        }
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $path = $this->getPath();
        $configs = $builder->getExtensionConfig('symkit_form');
        $merged = array_merge(...array_values($configs));
        $twigPrepend = $merged['twig_prepend'] ?? true;
        $assetMapper = $merged['asset_mapper'] ?? true;

        if ($twigPrepend) {
            $container->extension('twig', [
                'paths' => [
                    $path.'/templates' => 'SymkitForm',
                ],
            ], true);
            $container->extension('twig_component', [
                'defaults' => [
                    'Symkit\FormBundle\Twig\Component\\' => '@SymkitForm/components/',
                ],
            ], true);
        }

        if ($assetMapper) {
            $container->extension('framework', [
                'asset_mapper' => [
                    'paths' => [
                        $path.'/assets/controllers' => 'form',
                    ],
                ],
            ], true);
        }
    }
}
