<?php

declare(strict_types=1);

namespace Letkode\DataProviderBundle\Registry;

use Letkode\DataProviderBundle\Attribute\DataProvider;
use Letkode\DataProviderBundle\Attribute\DataProviderMethod;
use Letkode\DataProviderBundle\Exception\ProviderGroupNotFoundException;
use Letkode\DataProviderBundle\Exception\ProviderMethodNotAllowedException;
use Letkode\DataProviderBundle\Exception\ProviderNotFoundException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class ProviderRegistry
{
    /** @var array<string, array<string, object>> */
    private array $providersByGroup = [];

    /** @var array<string, array<string, array<string, string>>> */
    private array $methodMap = [];

    private bool $initialized = false;

    public function __construct(
        #[AutowireIterator('letkode.data_provider')]
        private readonly iterable $providers,
    ) {
    }

    private function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        foreach ($this->providers as $provider) {
            $reflection = new \ReflectionClass($provider);
            $classAttrs = $reflection->getAttributes(DataProvider::class);

            if (empty($classAttrs)) {
                continue;
            }

            /** @var DataProvider $dataProvider */
            $dataProvider = $classAttrs[0]->newInstance();
            $providerGroup = $dataProvider->providerGroup;
            $alias = $dataProvider->alias;

            $this->providersByGroup[$providerGroup][$alias] = $provider;
            $this->methodMap[$providerGroup][$alias] = [];

            foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                foreach ($method->getAttributes(DataProviderMethod::class) as $methodAttr) {
                    /** @var DataProviderMethod $dpm */
                    $dpm = $methodAttr->newInstance();
                    $this->methodMap[$providerGroup][$alias][$dpm->alias] = $method->getName();
                }
            }
        }

        $this->initialized = true;
    }

    /**
     * @throws ProviderGroupNotFoundException
     * @throws ProviderNotFoundException
     * @throws ProviderMethodNotAllowedException
     */
    public function resolve(string $providerGroup, string $classAlias, string $methodAlias): callable
    {
        $this->initialize();

        if (!isset($this->providersByGroup[$providerGroup])) {
            throw new ProviderGroupNotFoundException(\sprintf('Provider group "%s" not found.', $providerGroup));
        }

        if (!isset($this->providersByGroup[$providerGroup][$classAlias])) {
            throw new ProviderNotFoundException(\sprintf('Provider "%s" not found in group "%s".', $classAlias, $providerGroup));
        }

        if (!isset($this->methodMap[$providerGroup][$classAlias][$methodAlias])) {
            throw new ProviderMethodNotAllowedException(\sprintf('Method "%s" is not exposed on "%s/%s".', $methodAlias, $providerGroup, $classAlias));
        }

        return [$this->providersByGroup[$providerGroup][$classAlias], $this->methodMap[$providerGroup][$classAlias][$methodAlias]];
    }

    /** @return array<array{providerGroup: string, alias: string, methods: string[]}> */
    public function getMap(): array
    {
        $this->initialize();

        $map = [];

        foreach ($this->providersByGroup as $providerGroup => $providers) {
            foreach (array_keys($providers) as $alias) {
                $map[] = [
                    'providerGroup' => $providerGroup,
                    'alias' => $alias,
                    'methods' => array_keys($this->methodMap[$providerGroup][$alias]),
                ];
            }
        }

        return $map;
    }

    /**
     * @return array<array{alias: string, methods: string[]}>
     *
     * @throws ProviderGroupNotFoundException
     */
    public function getGroupMap(string $providerGroup): array
    {
        $this->initialize();

        if (!isset($this->providersByGroup[$providerGroup])) {
            throw new ProviderGroupNotFoundException(\sprintf('Provider group "%s" not found.', $providerGroup));
        }

        return array_map(
            fn (string $alias) => [
                'alias' => $alias,
                'methods' => array_keys($this->methodMap[$providerGroup][$alias]),
            ],
            array_keys($this->providersByGroup[$providerGroup]),
        );
    }
}
