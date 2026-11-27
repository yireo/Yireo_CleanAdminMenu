# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 27 November 2026
### Added
- Initial release: moves all non-Magento root items of the admin menu into a "Third Party" menu item, hooking into the core menu model without JavaScript
- Support for PHP 8.5

### Removed
- Support for PHP 8.1

### Fixed
- Admin menu no longer breaks when the `Magento_Marketplace::partners` item is missing
- Adjacent third-party root menu items are no longer skipped when moved into "Third Party"
- Core menu items are detected by module name, and the admin menu block cache is no longer disabled
- Missing `magento/module-backend` dependency and the wildcard `magento/framework` constraint in composer.json
- ACL resource title now states it only controls the "Third Party" menu container
