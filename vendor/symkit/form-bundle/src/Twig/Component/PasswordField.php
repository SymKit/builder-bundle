<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Twig\Component;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/** Service registration and live/template metadata are also configured in FormBundle::loadExtension(); AsLiveComponent is required by symfony/ux-live-component for Live detection. */
#[AsLiveComponent('PasswordField', template: '@SymkitForm/components/PasswordField.html.twig')]
final class PasswordField
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $password = '';

    #[LiveProp]
    public string $name = '';

    #[LiveProp]
    public string $placeholder = 'form.component.password_field.placeholder';

    #[LiveProp]
    public bool $showStrength = true;

    #[LiveProp]
    public int $minLength = 8;

    #[LiveProp]
    public bool $requireUppercase = false;

    #[LiveProp]
    public bool $requireNumbers = false;

    #[LiveProp]
    public bool $requireSpecial = false;

    /** @return array{score: int, rules: array{length: bool, uppercase: bool, number: bool, special: bool}} */
    public function getStrength(): array
    {
        if ('' === $this->password) {
            return [
                'score' => 0,
                'rules' => [
                    'length' => false,
                    'uppercase' => false,
                    'number' => false,
                    'special' => false,
                ],
            ];
        }

        $rules = [
            'length' => mb_strlen($this->password) >= $this->minLength,
            'uppercase' => !$this->requireUppercase || (bool) preg_match('/[A-Z]/', $this->password),
            'number' => !$this->requireNumbers || (bool) preg_match('/[0-9]/', $this->password),
            'special' => !$this->requireSpecial || (bool) preg_match('/[^a-zA-Z0-9]/', $this->password),
        ];

        $score = \count(array_filter($rules));
        $maxScore = \count($rules);

        // Normalize score to 0-4 for UI color mapping
        $normalizedScore = (int) ceil(($score / $maxScore) * 4);

        return [
            'score' => $normalizedScore,
            'rules' => $rules,
        ];
    }
}
