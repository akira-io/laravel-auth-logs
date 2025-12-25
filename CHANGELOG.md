# Changelog

## [1.0.3](https://github.com/akira-io/laravel-auth-logs/compare/1.0.2...1.0.3) (2025-12-25)


### Bug Fixes

* add database path to rector configuration and align parameter formatting ([c9cc646](https://github.com/akira-io/laravel-auth-logs/commit/c9cc64605344a2a02f775dae6092c2d3b42eab68))
* enhance geolocation API handling and support file scheme for offline tests ([d8f5282](https://github.com/akira-io/laravel-auth-logs/commit/d8f528274cb5434c74354b8d2580ebfb1699aca5))
* improve string handling in GetLocation for better compatibility ([b782dc9](https://github.com/akira-io/laravel-auth-logs/commit/b782dc912d91b629acae2c3ddeda02ddf0e31a35))
* update authentication methods to use authenticationLogs for consistency ([96dea60](https://github.com/akira-io/laravel-auth-logs/commit/96dea607ccf4470bc1fae15f863918ccceae4e16))
* update phpstan baseline and enhance test case setup with in-memory database and authentication logs table ([6f7362a](https://github.com/akira-io/laravel-auth-logs/commit/6f7362a6cdcebe62ed10b2cd32e82d62fac00522))

## [1.0.2](https://github.com/akira-io/laravel-auth-logs/compare/1.0.1...1.0.2) (2025-10-26)


### Bug Fixes

* add index to authenticatable morphs in auth logs table ([ee9c284](https://github.com/akira-io/laravel-auth-logs/commit/ee9c284b3c450213f9894b6a0ba07f0130069801))

## [1.0.1](https://github.com/akira-io/laravel-auth-logs/compare/1.0.0...1.0.1) (2025-09-27)


### Bug Fixes

* include UTC offset in login date notifications ([a1067e9](https://github.com/akira-io/laravel-auth-logs/commit/a1067e95b29cd7807452b3264586c71905dd4c87))
* update test coverage percentage and change minimum stability to stable ([f55a5cf](https://github.com/akira-io/laravel-auth-logs/commit/f55a5cfbc220baa8c0be2d61ececb4f1f3424a69))

# 1.0.0 (2025-05-24)


### Bug Fixes

* correct listener configuration for login event in AuthLogsServiceProvider ([858b69f](https://github.com/akira-io/laravel-auth-logs/commit/858b69f77d33434488e281b31d02455a87a860d9))
* simplify event listener configuration by removing type casting ([639719c](https://github.com/akira-io/laravel-auth-logs/commit/639719c600bb2d7cac651cb3f4bde9602ed96b7a))
* update event listeners and rename traits for consistency ([4d6b04b](https://github.com/akira-io/laravel-auth-logs/commit/4d6b04b891797d8bfa6522cf8e802adf6a523cc0))
* update test commands for improved coverage reporting ([34db507](https://github.com/akira-io/laravel-auth-logs/commit/34db507e8e65ce55a4809ca9357ac92371593a37))


### Features

* add authentication logs system with notifications and templates ([5eb74f6](https://github.com/akira-io/laravel-auth-logs/commit/5eb74f6c9a4350f4ed9cc9fd20988d0a9cb610da))
* add configurable options to auth-logs.php ([ff35b16](https://github.com/akira-io/laravel-auth-logs/commit/ff35b16197c7df0243e7ada69de813ff9ba6018a))
* add configurable options to auth-logs.php ([2c878b4](https://github.com/akira-io/laravel-auth-logs/commit/2c878b4ea4e76712fd619964587f0a8d7fdce38d))
* add PHPStan baseline configuration and update CI workflow ([f75626c](https://github.com/akira-io/laravel-auth-logs/commit/f75626c7962af693ad915fbd3d12f54422a4bbe0))
* add Portuguese translations for new device login notifications ([edf3ea0](https://github.com/akira-io/laravel-auth-logs/commit/edf3ea0d48552e94012edd9a254579687c9f6e1b))
* add translation support to LaravelAuthLogsServiceProvider ([668fe02](https://github.com/akira-io/laravel-auth-logs/commit/668fe02d65ee65359a3d3dc30c21183739884749))

All notable changes to `laravel-auth-logs` will be documented in this file.
