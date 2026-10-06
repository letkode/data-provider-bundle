# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.3.1] - 2026-10-06

### Changed
- Requires `letkode/config-publisher-bundle` instead of `letkode/config-publisher` (the package was renamed). The example file is now published with `bin/console letkode:config:publish data-provider`.

---

## [1.3.0] - 2026-10-06

### Added
- `extra.letkode.publish` in `composer.json` declares `resources/config/letkode_data_provider.routes.yaml.dist` as a publishable example routes file, so `vendor/bin/letkode-publish data-provider` copies it into the project (see `letkode/config-publisher`).
- `letkode/config-publisher` is now a `require`; it only ships the `letkode-publish` executable and is never used by the bundle's code.

---

## [1.2.0] - 2026-08-10

### Changed
- **Breaking**: bundle class renamed from `DataProviderBundle` to `LetkodeDataProviderBundle`, matching the `Letkode{Name}Bundle` naming convention used by every other Letkode bundle. This also fixes the `@LetkodeDataProviderBundle` alias already referenced by `config/routes.yaml` and the README, which never resolved correctly under the old class name.
  - Update `config/bundles.php`: `Letkode\DataProviderBundle\LetkodeDataProviderBundle::class`

### Fixed
- `ProviderRegistry::resolve()` return type and `LetkodeDataProviderBundle::build()` autoconfiguration callback now match Symfony's expected contracts (phpstan level 9, previously unchecked)

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

[Unreleased]: https://github.com/letkode/data-provider-bundle/compare/1.2.0...HEAD
[1.2.0]: https://github.com/letkode/data-provider-bundle/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/letkode/data-provider-bundle/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/letkode/data-provider-bundle/releases/tag/1.0.0
