<?php

declare(strict_types=1);

namespace Letkode\DataProviderBundle\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final readonly class DataProviderMethod
{
    public function __construct(
        public string $alias,
        public string|null $description = null,
    ) {
    }
}
