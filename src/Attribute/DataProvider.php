<?php

declare(strict_types=1);

namespace Letkode\DataProviderBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class DataProvider
{
    public function __construct(
        public string $providerGroup,
        public string $alias,
    ) {
    }
}
