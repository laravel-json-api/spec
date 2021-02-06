# Change Log

All notable changes to this project will be documented in this file. This project adheres to
[Semantic Versioning](http://semver.org/) and [this changelog format](http://keepachangelog.com/).

## Unreleased

### Added

- Added French translations for error messages.
- If a *to-many* relation is sent for a *to-one* field (and vice versa), the value will be rejected with a new message
  that clearly indicates that the relation type is invalid.

### Changed

- **BREAKING** The constructor for the to-many and to-one values now expect the relation name to be provided. This also
  means the factory create methods for these values has been updated to expect the relation name.

## [1.0.0-alpha.2] - 2021-02-02

### Bugfix

- [#1](https://github.com/laravel-json-api/spec/issues/1) Add missing package discovery configuration to
  the `composer.json`.

## [1.0.0-alpha.1] - 2021-01-25

Initial release.
