<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Twig\Component;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/** Service registration and live/template metadata are also configured in FormBundle::loadExtension(); AsLiveComponent is required by symfony/ux-live-component for Live detection. */
#[AsLiveComponent('TranslatableField', template: '@SymkitForm/components/TranslatableField.html.twig')]
final class TranslatableField
{
    use DefaultActionTrait;

    /** @var array<string, string> */
    #[LiveProp(writable: true)]
    public array $translations = [];

    /** @var list<string> */
    #[LiveProp]
    public array $locales = ['fr', 'en'];

    #[LiveProp(writable: true)]
    public string $activeLocale = '';

    #[LiveProp]
    public string $name = '';

    #[LiveProp]
    public string $type = 'text'; // 'text' or 'textarea'

    #[LiveProp]
    public string $placeholder = '';

    /** @param list<string> $locales */
    public function mount(array $locales = ['fr', 'en'], mixed $translations = null, ?string $activeLocale = null): void
    {
        $this->locales = $locales;
        /** @var array<string, string> $translationData */
        $translationData = \is_array($translations) ? $translations : [];
        $this->translations = $translationData;

        // Ensure all locales have a value (even empty)
        foreach ($this->locales as $locale) {
            if (!isset($this->translations[$locale])) {
                $this->translations[$locale] = '';
            }
        }

        $this->activeLocale = $activeLocale ?? ($this->locales[0] ?? 'en');
    }
}
