# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [1.2.2] - 2018-01-13

### Added
- Implemented Webhooks handling
- Implemented `webhook` and `webhooks` methods in the `Descriptor` facade

## [1.2.1] - 2018-01-03

### Fixed
- Register the listeners in the subscriber instead of putting to the **EventServiceProvider** 
- Fixed and improved README

## [1.2.0] - 2017-09-24

### Added
- `JWTClient` to create requests to the JIRA/Confluence (closes #3)
- Pagination (used by `JWTClient`)
- Note about using route helper in the `AppServiceProvider` to the README

### Fixed
- Typos and code style issues
- TODO section in the README

## [1.1.0] - 2017-09-08

### Added
- Tests for Descriptor facade methods **fluent**, **withModules**, **withoutModules**, **setScopes**, **base**
- Support of `oauthClientId`
- CHANGELOG
- Environment variable `PLUGIN_AUTH_TYPE`

### Fixed
- Typos and code style issues

## [1.0.2] - 2017-09-06

### Added
- Descriptor facade methods fluent, withModules, withoutModules, setScopes, base
- Example of customizing the Descriptor contents to the README
- TODO section to the README

## [1.0.1] - 2017-09-04

### Fixed
- Package keywords at composer.json

[Unreleased]: https://github.com/brezzhnev/atlassian-connect-core/compare/v1.2.2...HEAD
[1.2.2]: https://github.com/brezzhnev/atlassian-connect-core/compare/v1.2.1...v1.2.2
[1.2.1]: https://github.com/brezzhnev/atlassian-connect-core/compare/v1.2.0...v1.2.1
[1.2.0]: https://github.com/brezzhnev/atlassian-connect-core/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/brezzhnev/atlassian-connect-core/compare/v1.0.2...v1.1.0
[1.0.2]: https://github.com/brezzhnev/atlassian-connect-core/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/brezzhnev/atlassian-connect-core/compare/v1.0.0...v1.0.1