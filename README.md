# letkode/data-provider-bundle

Attribute-driven, namespaced data provider registry and endpoint for Symfony applications.

Allows any service to expose named, aliased method sets under a **provider group** (a namespace such as `form-options`, `search`, `reports`, etc.), which are then discoverable at runtime through a single REST endpoint. It generalizes the "expose a repository method as an option list" pattern so it can be reused for any read-only, alias-driven data access use case — not just form options.

---

## Installation

```bash
composer require letkode/data-provider-bundle
```

Symfony Flex will register the bundle automatically. If not using Flex, add it manually:

```php
// config/bundles.php
return [
    Letkode\DataProviderBundle\LetkodeDataProviderBundle::class => ['all' => true],
];
```

Import the routes to expose the endpoint:

```yaml
# config/routes/letkode_data_provider.yaml
letkode_data_provider:
    resource: '@LetkodeDataProviderBundle/config/routes.yaml'
```

or let the bundle write that file for you:

```bash
vendor/bin/letkode-publish data-provider
```

It never overwrites an existing file unless you add `--force`. `--dry-run` shows what it would do.

---

## Usage

### 1. Mark a service as a provider

```php
use Letkode\DataProviderBundle\Attribute\DataProvider;
use Letkode\DataProviderBundle\Attribute\DataProviderMethod;

#[DataProvider(providerGroup: 'form-options', alias: 'countries')]
final class CountryRepository extends ServiceEntityRepository
{
    #[DataProviderMethod(alias: 'active')]
    public function findActiveAsOptions(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.uuid, c.code, c.name')
            ->where('c.active = true')
            ->getQuery()
            ->getArrayResult();
    }
}
```

### 2. Fetch data via the API

```
GET /api/providers/form-options/countries/active
```

```json
[
    { "uuid": "...", "code": "CL", "name": "Chile" },
    { "uuid": "...", "code": "AR", "name": "Argentina" }
]
```

### 3. Reuse the same mechanism for other domains

The `providerGroup` namespaces providers so unrelated features can reuse the same `alias` without colliding:

```php
#[DataProvider(providerGroup: 'search', alias: 'countries')]
final class CountrySearchService
{
    #[DataProviderMethod(alias: 'by-name')]
    public function searchByName(): array { /* ... */ }
}
```

```
GET /api/providers/search/countries/by-name?q=chi
```

### 4. Discover providers within a group

```
GET /api/providers/form-options
```

```json
[
    { "alias": "countries", "methods": ["active"] },
    { "alias": "roles", "methods": ["list"] }
]
```

---

## How it works

- `LetkodeDataProviderBundle::build()` calls `registerAttributeForAutoconfiguration()` so any service annotated with `#[DataProvider]` is automatically tagged as `letkode.data_provider`.
- `ProviderRegistry` collects all tagged providers via `#[AutowireIterator]` and indexes them in three levels: `providerGroup → classAlias → methodAlias`.
- `DataProviderController` resolves `providerGroup` + `classAlias` + `methodAlias` and returns the result as JSON. Query parameters, if present, are passed as a single array argument to the resolved method.
- `DataProviderController` also exposes `GET /api/providers/{providerGroup}` to list every provider and its exposed methods within a given group, for runtime discovery.

---

## Requirements

- PHP `^8.4`
- Symfony `^7.0 || ^8.0`

---

## License

MIT — see [LICENSE](LICENSE).
