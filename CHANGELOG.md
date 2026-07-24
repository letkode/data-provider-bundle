# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

---

## [1.1.0] - 2026-07-24

### Added
- `GET /api/providers/{providerGroup}`: discovery endpoint that lists every provider and its exposed methods within a given `providerGroup`
- `ProviderRegistry::getGroupMap(string $providerGroup)`: returns the map filtered by group, throwing `ProviderGroupNotFoundException` when unknown

---

## [1.0.0] - 2026-07-24

### Added
- Initial release as `letkode/data-provider-bundle`
- Symfony bundle integration via `DataProviderBundle` extending `AbstractBundle`
- Auto-discovery support via `extra.symfony.bundles` in Composer
- **`#[DataProvider(providerGroup: '...', alias: '...')]`**: PHP Attribute to mark a service as a data provider within a given namespace (`providerGroup`); registered via `registerAttributeForAutoconfiguration()` in `bundle::build()` with tag `letkode.data_provider`
- **`#[DataProviderMethod(alias: '...', description: '...')]`**: PHP Attribute to mark a method within a provider as a named, exposable data set
- **`ProviderRegistry`**: collects all tagged providers via `#[AutowireIterator]` and dispatches calls by `providerGroup` + `classAlias` + `methodAlias`
- **`DataProviderController`**: exposes `GET /api/providers/{providerGroup}/{classAlias}/{methodAlias}` to fetch data at runtime
- **Exceptions**: `ProviderGroupNotFoundException`, `ProviderNotFoundException`, `ProviderMethodNotAllowedException`
- Routes provided via `config/routes.yaml` (import in your project to enable the endpoint)

### Requirements
- PHP `^8.4`
- Symfony `^7.0 || ^8.0`

[Unreleased]: https://github.com/letkode/data-provider-bundle/compare/1.1.0...HEAD
[1.1.0]: https://github.com/letkode/data-provider-bundle/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/letkode/data-provider-bundle/releases/tag/1.0.0
