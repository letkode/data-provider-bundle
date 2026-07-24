<?php

declare(strict_types=1);

namespace Letkode\DataProviderBundle\Tests\Exception;

use Letkode\DataProviderBundle\Exception\ProviderGroupNotFoundException;
use Letkode\DataProviderBundle\Exception\ProviderMethodNotAllowedException;
use Letkode\DataProviderBundle\Exception\ProviderNotFoundException;
use PHPUnit\Framework\TestCase;

final class ProviderExceptionsTest extends TestCase
{
    public function testProviderGroupNotFoundExceptionExtendsRuntimeException(): void
    {
        $e = new ProviderGroupNotFoundException('group "foo" not found.');

        self::assertInstanceOf(\RuntimeException::class, $e);
        self::assertSame('group "foo" not found.', $e->getMessage());
    }

    public function testProviderNotFoundExceptionExtendsRuntimeException(): void
    {
        $e = new ProviderNotFoundException('provider "foo" not found.');

        self::assertInstanceOf(\RuntimeException::class, $e);
        self::assertSame('provider "foo" not found.', $e->getMessage());
    }

    public function testProviderMethodNotAllowedExceptionExtendsRuntimeException(): void
    {
        $e = new ProviderMethodNotAllowedException('Method "bar" is not exposed.');

        self::assertInstanceOf(\RuntimeException::class, $e);
        self::assertSame('Method "bar" is not exposed.', $e->getMessage());
    }

    public function testProviderGroupNotFoundExceptionIsThrowable(): void
    {
        $this->expectException(ProviderGroupNotFoundException::class);
        throw new ProviderGroupNotFoundException('not found');
    }

    public function testProviderNotFoundExceptionIsThrowable(): void
    {
        $this->expectException(ProviderNotFoundException::class);
        throw new ProviderNotFoundException('not found');
    }

    public function testProviderMethodNotAllowedExceptionIsThrowable(): void
    {
        $this->expectException(ProviderMethodNotAllowedException::class);
        throw new ProviderMethodNotAllowedException('not allowed');
    }
}
