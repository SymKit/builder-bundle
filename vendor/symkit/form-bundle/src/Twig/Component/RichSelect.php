<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Twig\Component;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/** Service registration and live/template metadata are also configured in FormBundle::loadExtension(); AsLiveComponent is required by symfony/ux-live-component for Live detection. */
#[AsLiveComponent('RichSelect', template: '@SymkitForm/components/RichSelect.html.twig')]
final class RichSelect
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $searchQuery = '';

    /** @var array<string, string> */
    #[LiveProp]
    public array $choices = [];

    #[LiveProp(writable: true)]
    public ?string $value = null;

    #[LiveProp]
    public bool $searchable = true;

    #[LiveProp]
    public string $name = '';

    #[LiveProp]
    public string $placeholder = 'form.component.rich_select.placeholder';

    #[LiveProp]
    public bool $required = false;

    /** @var array<string, array{name: string, class: string}> */
    #[LiveProp]
    public array $choiceIcons = [];

    /** @return array<string, string> */
    public function getFilteredChoices(): array
    {
        if (!$this->searchable || '' === $this->searchQuery) {
            return $this->choices;
        }

        $filtered = [];
        $query = mb_strtolower($this->searchQuery);

        foreach ($this->choices as $label => $value) {
            if (false !== mb_strpos(mb_strtolower((string) $label), $query)) {
                $filtered[$label] = $value;
            }
        }

        return $filtered;
    }

    public function getSelectedLabel(): ?string
    {
        if (null === $this->value) {
            return null;
        }

        foreach ($this->choices as $label => $val) {
            if ((string) $val === (string) $this->value) {
                return (string) $label;
            }
        }

        return null;
    }
}
