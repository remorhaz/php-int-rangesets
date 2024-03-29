# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.3.0] - 2024-02-11
### Fixed
- PHP versions range is set correctly.
### Removed
- Dropped PHP 8.0 support.
### Added
- Added PHP 8.3 support.

## [0.2.1] - 2023-06-14
### Fixed
- Incorrect parameter annotation for `RangeSet::importRanges()` method fixed.

## [0.2.0] - 2023-06-13
### Removed
- Dropped support for PHP 7.

## [0.1.1] - 2021-01-14
### Added
- PHP 8.0 support added.

## [0.1.0] - 2020-04-17
Initial release.
### Added
- Binary operations on range sets:
  - [Union](https://en.wikipedia.org/wiki/Union_(set_theory))
  - [Intersection](https://en.wikipedia.org/wiki/Intersection_(set_theory))
  - [Symmetric difference](https://en.wikipedia.org/wiki/Symmetric_difference)
