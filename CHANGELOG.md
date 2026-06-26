# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0](https://github.com/akira-io/laravel-auth-logs/compare/1.1.0...v1.2.0) (2026-06-26)

### Bug Fixes

- **listeners:** Restore documented phpdoc on subscribeToLogoutOtherDeviceEvent ([d5158df](https://github.com/akira-io/laravel-auth-logs/commit/d5158df787b287fff21afb76e4cb2dd56f981b60))
- **listeners:** Register logout timestamps ([912ba8d](https://github.com/akira-io/laravel-auth-logs/commit/912ba8d4cb33633bc3a56ebd5f3a0913fb7bff04))
- **logs:** Persist resolved location data ([c8ad37a](https://github.com/akira-io/laravel-auth-logs/commit/c8ad37a77298f5de9af0363b195d0441c3941ff1))
- **location:** Allow disabled geolocation ([73cf7f0](https://github.com/akira-io/laravel-auth-logs/commit/73cf7f050193b3deac47df1ec643a952662f5831))
- **notifications:** Reject unsupported channels ([09abaa0](https://github.com/akira-io/laravel-auth-logs/commit/09abaa081eabd260e5e07b6577e23df59ab3c8a6))
- **location:** Use timeout-bound geolocation read ([d68604b](https://github.com/akira-io/laravel-auth-logs/commit/d68604b39e33d6f228de05eb681e9cb8aa9f1e88))
- **migrations:** Add auth logs rollback ([7ac596d](https://github.com/akira-io/laravel-auth-logs/commit/7ac596dbe86a19b3c862f001133345ebec953d1d))
- **commands:** Remove hard-coded migration filename ([b20ebb2](https://github.com/akira-io/laravel-auth-logs/commit/b20ebb2363292ffa6274b0d20e4a66458a8f0b1b))
- **listeners:** Honor notification flags ([46ec983](https://github.com/akira-io/laravel-auth-logs/commit/46ec983fba9e8c32c4653940fd8d81fbbd4c1356))
- **notifications:** Require mail templates ([54289f9](https://github.com/akira-io/laravel-auth-logs/commit/54289f913b8ca6213c6ab849d813a0e7771c8d38))
- **logs:** Handle null auth log connection ([b809e03](https://github.com/akira-io/laravel-auth-logs/commit/b809e03562c2bd56b54d8c03fa7acb9deb173515))
- **notifications:** Defer geolocation lookup ([d23d65a](https://github.com/akira-io/laravel-auth-logs/commit/d23d65acd04449ec64bb38c422fe2190f543461c))
- **notifications:** Resolve 1.x merge conflicts ([14e6308](https://github.com/akira-io/laravel-auth-logs/commit/14e6308c44227530e29af6df3a81755457328774))
- **notifications:** Stabilize mail template validation ([482bd78](https://github.com/akira-io/laravel-auth-logs/commit/482bd78b79387da05c803a0fa5e8128b733822bb))


### Features

- **listeners:** Register logout timestamps from the default logout listener ([337f516](https://github.com/akira-io/laravel-auth-logs/commit/337f51605a05d79ab2a29943768015b56c290692))
- **listeners:** Wire OtherDeviceLogout to its dedicated event and listener config keys ([c5a0ebd](https://github.com/akira-io/laravel-auth-logs/commit/c5a0ebd378b8ec698e92fca2bdb1ac1fd0e031da))

## [1.1.0](https://github.com/akira-io/laravel-auth-logs/compare/1.0.3...1.1.0) (2026-02-25)

### Bug Fixes

- Correct fixture path and update test coverage threshold to 100% ([df14056](https://github.com/akira-io/laravel-auth-logs/commit/df1405607ac07778887e8ddafb9098afaeb87acd))


### Code Refactoring

- Improve type hinting for authentication log relationships ([add1c6f](https://github.com/akira-io/laravel-auth-logs/commit/add1c6f564b1d187efd3c04329463b73ad377dc6))


### Features

- Add Laravel 13 support ([1a54f93](https://github.com/akira-io/laravel-auth-logs/commit/1a54f9397eabe17b1026b9dc0af1de8b00655598))

## [1.0.3](https://github.com/akira-io/laravel-auth-logs/compare/1.0.2...1.0.3) (2025-12-25)

### Bug Fixes

- Add database path to rector configuration and align parameter formatting ([c9cc646](https://github.com/akira-io/laravel-auth-logs/commit/c9cc64605344a2a02f775dae6092c2d3b42eab68))
- Update authentication methods to use authenticationLogs for consistency ([96dea60](https://github.com/akira-io/laravel-auth-logs/commit/96dea607ccf4470bc1fae15f863918ccceae4e16))
- Enhance geolocation API handling and support file scheme for offline tests ([d8f5282](https://github.com/akira-io/laravel-auth-logs/commit/d8f528274cb5434c74354b8d2580ebfb1699aca5))
- Update phpstan baseline and enhance test case setup with in-memory database and authentication logs table ([6f7362a](https://github.com/akira-io/laravel-auth-logs/commit/6f7362a6cdcebe62ed10b2cd32e82d62fac00522))
- Improve string handling in GetLocation for better compatibility ([b782dc9](https://github.com/akira-io/laravel-auth-logs/commit/b782dc912d91b629acae2c3ddeda02ddf0e31a35))


### Other

- Update package description and add laravel-debugger as a development dependency ([63576bd](https://github.com/akira-io/laravel-auth-logs/commit/63576bd1111db4e6e1a7b4a98ed5cde2cf016820))

## [1.0.2](https://github.com/akira-io/laravel-auth-logs/compare/1.0.1...1.0.2) (2025-10-26)

### Bug Fixes

- Add index to authenticatable morphs in auth logs table ([ee9c284](https://github.com/akira-io/laravel-auth-logs/commit/ee9c284b3c450213f9894b6a0ba07f0130069801))

## [1.0.1](https://github.com/akira-io/laravel-auth-logs/compare/1.0.0...1.0.1) (2025-09-27)

### Bug Fixes

- Include UTC offset in login date notifications ([a1067e9](https://github.com/akira-io/laravel-auth-logs/commit/a1067e95b29cd7807452b3264586c71905dd4c87))
- Update test coverage percentage and change minimum stability to stable ([f55a5cf](https://github.com/akira-io/laravel-auth-logs/commit/f55a5cfbc220baa8c0be2d61ececb4f1f3424a69))

## [1.0.0](https://github.com/akira-io/laravel-auth-logs/compare/...1.0.0) (2025-05-24)

### Bug Fixes

- Update event listeners and rename traits for consistency ([4d6b04b](https://github.com/akira-io/laravel-auth-logs/commit/4d6b04b891797d8bfa6522cf8e802adf6a523cc0))
- Simplify event listener configuration by removing type casting ([639719c](https://github.com/akira-io/laravel-auth-logs/commit/639719c600bb2d7cac651cb3f4bde9602ed96b7a))
- Correct listener configuration for login event in AuthLogsServiceProvider ([858b69f](https://github.com/akira-io/laravel-auth-logs/commit/858b69f77d33434488e281b31d02455a87a860d9))
- Update test commands for improved coverage reporting ([34db507](https://github.com/akira-io/laravel-auth-logs/commit/34db507e8e65ce55a4809ca9357ac92371593a37))


### Features

- Add configurable options to auth-logs.php ([2c878b4](https://github.com/akira-io/laravel-auth-logs/commit/2c878b4ea4e76712fd619964587f0a8d7fdce38d))
- Add configurable options to auth-logs.php ([ff35b16](https://github.com/akira-io/laravel-auth-logs/commit/ff35b16197c7df0243e7ada69de813ff9ba6018a))
- Add authentication logs system with notifications and templates ([5eb74f6](https://github.com/akira-io/laravel-auth-logs/commit/5eb74f6c9a4350f4ed9cc9fd20988d0a9cb610da))
- Add Portuguese translations for new device login notifications ([edf3ea0](https://github.com/akira-io/laravel-auth-logs/commit/edf3ea0d48552e94012edd9a254579687c9f6e1b))
- Add translation support to LaravelAuthLogsServiceProvider ([668fe02](https://github.com/akira-io/laravel-auth-logs/commit/668fe02d65ee65359a3d3dc30c21183739884749))
- Add PHPStan baseline configuration and update CI workflow ([f75626c](https://github.com/akira-io/laravel-auth-logs/commit/f75626c7962af693ad915fbd3d12f54422a4bbe0))

