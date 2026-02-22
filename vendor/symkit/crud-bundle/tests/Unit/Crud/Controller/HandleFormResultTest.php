<?php

declare(strict_types=1);

namespace Symkit\CrudBundle\Tests\Unit\Crud\Controller;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Response;
use Symkit\CrudBundle\Controller\HandleFormResult;

final class HandleFormResultTest extends TestCase
{
    public function testIsRedirectReturnsTrueWithResponse(): void
    {
        $response = new Response();
        $formView = new FormView();

        $result = new HandleFormResult($response, $formView);

        self::assertTrue($result->isRedirect());
        self::assertSame($response, $result->redirect);
        self::assertSame($formView, $result->formView);
    }

    public function testIsRedirectReturnsFalseWithNull(): void
    {
        $formView = new FormView();

        $result = new HandleFormResult(null, $formView);

        self::assertFalse($result->isRedirect());
        self::assertNull($result->redirect);
        self::assertSame($formView, $result->formView);
    }
}
