<?php

declare(strict_types=1);

namespace Letkode\DataProviderBundle\Tests\Registry;

use Letkode\DataProviderBundle\Attribute\DataProvider;
use Letkode\DataProviderBundle\Attribute\DataProviderMethod;
use Letkode\DataProviderBundle\Exception\ProviderGroupNotFoundException;
use Letkode\DataProviderBundle\Exception\ProviderMethodNotAllowedException;
use Letkode\DataProviderBundle\Exception\ProviderNotFoundException;
use Letkode\DataProviderBundle\Registry\ProviderRegistry;
use PHPUnit\Framework\TestCase;

#[DataProvider(providerGroup: 'form-options', alias: 'countries')]
final class StubCountryProvider
{
    #[DataProviderMethod(alias: 'active')]
    public function findActive(): array
    {
        return [['code' => 'CL', 'name' => 'Chile']];
    }

    #[DataProviderMethod(alias: 'all')]
    public function findAll(): array
    {
        return [['code' => 'CL'], ['code' => 'AR']];
    }

    public function notExposed(): array
    {
        return [];
    }
}

#[DataProvider(providerGroup: 'form-options', alias: 'roles')]
final class StubRoleProvider
{
    #[DataProviderMethod(alias: 'list')]
    public function getRoles(): array
    {
        return ['ROLE_ADMIN', 'ROLE_USER'];
    }
}

#[DataProvider(providerGroup: 'search', alias: 'countries')]
final class StubCountrySearchProvider
{
    #[DataProviderMethod(alias: 'by-name')]
    public function searchByName(): array
    {
        return [['code' => 'CL', 'name' => 'Chile', 'score' => 0.9]];
    }
}

final class ProviderRegistryTest extends TestCase
{
    private function makeRegistry(array $providers): ProviderRegistry
    {
        return new ProviderRegistry(new \ArrayObject($providers));
    }

    public function testResolvesProviderAndMethod(): void
    {
        $registry = $this->makeRegistry([new StubCountryProvider()]);

        $callable = $registry->resolve('form-options', 'countries', 'active');
        $result = $callable();

        self::assertSame([['code' => 'CL', 'name' => 'Chile']], $result);
    }

    public function testResolvesSecondMethodOnSameProvider(): void
    {
        $registry = $this->makeRegistry([new StubCountryProvider()]);

        $callable = $registry->resolve('form-options', 'countries', 'all');
        $result = $callable();

        self::assertCount(2, $result);
    }

    public function testResolvesMultipleProvidersInSameGroup(): void
    {
        $registry = $this->makeRegistry([new StubCountryProvider(), new StubRoleProvider()]);

        $callable = $registry->resolve('form-options', 'roles', 'list');
        $result = $callable();

        self::assertSame(['ROLE_ADMIN', 'ROLE_USER'], $result);
    }

    public function testDoesNotCollideWhenSameClassAliasUsedInDifferentGroups(): void
    {
        $registry = $this->makeRegistry([new StubCountryProvider(), new StubCountrySearchProvider()]);

        $formOptionsResult = ($registry->resolve('form-options', 'countries', 'active'))();
        $searchResult = ($registry->resolve('search', 'countries', 'by-name'))();

        self::assertSame([['code' => 'CL', 'name' => 'Chile']], $formOptionsResult);
        self::assertSame([['code' => 'CL', 'name' => 'Chile', 'score' => 0.9]], $searchResult);
    }

    public function testThrowsWhenProviderGroupNotFound(): void
    {
        $registry = $this->makeRegistry([new StubCountryProvider()]);

        $this->expectException(ProviderGroupNotFoundException::class);
        $this->expectExceptionMessageMatches('/unknown-group/');
        $registry->resolve('unknown-group', 'countries', 'active');
    }

    public function testThrowsWhenClassAliasNotFoundInGroup(): void
    {
        $registry = $this->makeRegistry([new StubCountryProvider()]);

        $this->expectException(ProviderNotFoundException::class);
        $this->expectExceptionMessageMatches('/unknown/');
        $registry->resolve('form-options', 'unknown', 'active');
    }

    public function testThrowsWhenMethodAliasNotFound(): void
    {
        $registry = $this->makeRegistry([new StubCountryProvider()]);

        $this->expectException(ProviderMethodNotAllowedException::class);
        $this->expectExceptionMessageMatches('/missing/');
        $registry->resolve('form-options', 'countries', 'missing');
    }

    public function testDoesNotExposeUnmarkedMethods(): void
    {
        $registry = $this->makeRegistry([new StubCountryProvider()]);

        $this->expectException(ProviderMethodNotAllowedException::class);
        $registry->resolve('form-options', 'countries', 'notExposed');
    }

    public function testGetMapReturnsAllRegisteredProviders(): void
    {
        $registry = $this->makeRegistry([new StubCountryProvider(), new StubRoleProvider()]);

        $map = $registry->getMap();

        self::assertCount(2, $map);
        $aliases = array_column($map, 'alias');
        self::assertContains('countries', $aliases);
        self::assertContains('roles', $aliases);
    }

    public function testGetMapIncludesProviderGroup(): void
    {
        $registry = $this->makeRegistry([new StubCountryProvider(), new StubCountrySearchProvider()]);

        $map = $registry->getMap();
        $groups = array_column($map, 'providerGroup');

        self::assertContains('form-options', $groups);
        self::assertContains('search', $groups);
    }

    public function testGetMapIncludesMethodAliases(): void
    {
        $registry = $this->makeRegistry([new StubCountryProvider()]);

        $map = $registry->getMap();
        $countriesEntry = $map[0];

        self::assertSame('countries', $countriesEntry['alias']);
        self::assertContains('active', $countriesEntry['methods']);
        self::assertContains('all', $countriesEntry['methods']);
        self::assertNotContains('notExposed', $countriesEntry['methods']);
    }

    public function testGetGroupMapFiltersByProviderGroup(): void
    {
        $registry = $this->makeRegistry([new StubCountryProvider(), new StubRoleProvider(), new StubCountrySearchProvider()]);

        $map = $registry->getGroupMap('form-options');

        self::assertCount(2, $map);
        $aliases = array_column($map, 'alias');
        self::assertContains('countries', $aliases);
        self::assertContains('roles', $aliases);
    }

    public function testGetGroupMapDoesNotIncludeOtherGroups(): void
    {
        $registry = $this->makeRegistry([new StubCountryProvider(), new StubCountrySearchProvider()]);

        $map = $registry->getGroupMap('search');

        self::assertCount(1, $map);
        self::assertSame('countries', $map[0]['alias']);
        self::assertSame(['by-name'], $map[0]['methods']);
    }

    public function testGetGroupMapThrowsWhenProviderGroupNotFound(): void
    {
        $registry = $this->makeRegistry([new StubCountryProvider()]);

        $this->expectException(ProviderGroupNotFoundException::class);
        $this->expectExceptionMessageMatches('/unknown-group/');
        $registry->getGroupMap('unknown-group');
    }

    public function testEmptyRegistryReturnsEmptyMap(): void
    {
        $registry = $this->makeRegistry([]);

        self::assertSame([], $registry->getMap());
    }

    public function testIsInitializedOnlyOnce(): void
    {
        $registry = $this->makeRegistry([new StubCountryProvider()]);

        $registry->resolve('form-options', 'countries', 'active');
        $callable = $registry->resolve('form-options', 'countries', 'active');

        self::assertIsCallable($callable);
    }
}
