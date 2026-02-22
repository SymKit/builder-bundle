<?php

declare(strict_types=1);

namespace Symkit\FormBundle\Tests\Unit\Twig\Component;

use PHPUnit\Framework\TestCase;
use Symkit\FormBundle\Twig\Component\PasswordField;

final class PasswordFieldTest extends TestCase
{
    public function testGetStrengthReturnsZeroForEmptyPassword(): void
    {
        $component = new PasswordField();
        $strength = $component->getStrength();

        self::assertSame(0, $strength['score']);
        self::assertFalse($strength['rules']['length']);
        self::assertFalse($strength['rules']['uppercase']);
        self::assertFalse($strength['rules']['number']);
        self::assertFalse($strength['rules']['special']);
    }

    public function testGetStrengthReturnsFullScoreForStrongPassword(): void
    {
        $component = new PasswordField();
        $component->requireUppercase = true;
        $component->requireNumbers = true;
        $component->requireSpecial = true;
        $component->password = 'StrongP@ss1';

        $strength = $component->getStrength();

        self::assertSame(4, $strength['score']);
        self::assertTrue($strength['rules']['length']);
        self::assertTrue($strength['rules']['uppercase']);
        self::assertTrue($strength['rules']['number']);
        self::assertTrue($strength['rules']['special']);
    }

    public function testGetStrengthWithOnlyLengthRule(): void
    {
        $component = new PasswordField();
        $component->minLength = 5;
        $component->password = 'hello';

        $strength = $component->getStrength();

        self::assertTrue($strength['rules']['length']);
    }

    public function testGetStrengthWithShortPassword(): void
    {
        $component = new PasswordField();
        $component->minLength = 10;
        $component->password = 'short';

        $strength = $component->getStrength();

        self::assertFalse($strength['rules']['length']);
    }

    public function testGetStrengthPartialRules(): void
    {
        $component = new PasswordField();
        $component->requireUppercase = true;
        $component->requireNumbers = true;
        $component->requireSpecial = true;
        $component->password = 'lowercaseonly';

        $strength = $component->getStrength();

        self::assertTrue($strength['rules']['length']);
        self::assertFalse($strength['rules']['uppercase']);
        self::assertFalse($strength['rules']['number']);
        self::assertFalse($strength['rules']['special']);
    }
}
