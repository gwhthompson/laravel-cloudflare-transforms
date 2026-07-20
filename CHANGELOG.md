# Changelog

## [4.0.0](https://github.com/gwhthompson/laravel-cloudflare-transforms/compare/v3.2.3...v4.0.0) (2026-07-20)


### ⚠ BREAKING CHANGES

* Storage::disk(...)->image(...) is now Storage::disk(...)->cloudflareImage(...). On Laravel >= 13.17 the old name silently dispatches to the framework's native image() method, so call sites must be updated, not aliased.
* Laravel 11 is no longer supported; require Laravel 12+.

### Features

* drop EOL Laravel 11 support ([82522b1](https://github.com/gwhthompson/laravel-cloudflare-transforms/commit/82522b1e51eaca4be9ec4a7532383d405cbe979f))
* expose onerror on the Blade image component ([9c8c908](https://github.com/gwhthompson/laravel-cloudflare-transforms/commit/9c8c90801a35ac8db86bab81a693ee3d7f43f612))
* rename image() macro to cloudflareImage() ([d352124](https://github.com/gwhthompson/laravel-cloudflare-transforms/commit/d352124d683d5f029c9e8b8b37042af9106e9383))


### Bug Fixes

* include the disk url sub-path in transform source paths ([d14a796](https://github.com/gwhthompson/laravel-cloudflare-transforms/commit/d14a796c6a26ce8eb59e560f990f03c797607e1f))

## [3.2.3](https://github.com/gwhthompson/laravel-cloudflare-transforms/compare/v3.2.2...v3.2.3) (2026-04-10)


### Miscellaneous Chores

* add Laravel 13 support ([0767bac](https://github.com/gwhthompson/laravel-cloudflare-transforms/commit/0767bacda14bd5ee58129ffb589188cfce2017a7))
* improve Packagist discoverability ([04965bf](https://github.com/gwhthompson/laravel-cloudflare-transforms/commit/04965bf52dc4d5327a2e800b22ce48f2e3b68e52))

## [3.2.2](https://github.com/gwhthompson/laravel-cloudflare-transforms/compare/v3.2.1...v3.2.2) (2025-12-19)


### Miscellaneous Chores

* broaden PHP/Laravel support and fix CI workflows ([4c36097](https://github.com/gwhthompson/laravel-cloudflare-transforms/commit/4c360975eb969bccf656c0012be03a764f180015))

## [3.2.1](https://github.com/gwhthompson/laravel-cloudflare-transforms/compare/v3.2.0...v3.2.1) (2025-12-17)


### Bug Fixes

* remove hardcoded version from composer.json ([07e6310](https://github.com/gwhthompson/laravel-cloudflare-transforms/commit/07e6310c0f72f496a4e5cd17b56962f704137be4))

## [3.2.0](https://github.com/gwhthompson/laravel-cloudflare-transforms/compare/v3.1.0...v3.2.0) (2025-12-17)


### Features

* prepare for public release and remove unnecessary dependencies ([7792f5e](https://github.com/gwhthompson/laravel-cloudflare-transforms/commit/7792f5ee37513956cbf44ebb64a45016a595d626))

## [3.1.0](https://github.com/gwhthompson/laravel-cloudflare-transforms/compare/v3.0.0...v3.1.0) (2025-12-17)


### Features

* add Blade component and refactor validation architecture ([061bf14](https://github.com/gwhthompson/laravel-cloudflare-transforms/commit/061bf14909a4c78ddad7a64d9846fc9fd79ed29f))
* add custom exception classes and Laravel Facade ([a96f930](https://github.com/gwhthompson/laravel-cloudflare-transforms/commit/a96f930ae42eac5bc379bd151a660d78b49afcf7))
