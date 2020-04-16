# Contributing to `remorhaz/int-rangeset`

# Keep a changelog
Please don't forget to update [CHANGELOG.md](./CHANGELOG.md) when you're making some changes users should know about.

# Static checks
## Code style
Code style should conform to [PSR-12 standard](https://www.php-fig.org/psr/psr-12/) with the following exceptions:

- Test method names must follow `testMethod_Conditions_Behaviour` pattern.

Run `composer test-cs` to check the code style.

## Psalm
[Psalm](https://psalm.dev) is static analysis tool for finding errors in PHP applications. You can learn more about it in [its documentation](https://psalm.dev/docs). Code of this library must conform to level 1 of Psalm checks (the strictest one).

Run `composer test-psalm` to run Psalm checks on code.

# Dynamic checks
## Unit tests
Please don't forget to write and update unit tests using [PHPUnit framework](https://phpunit.de/). Please keep coverage as high as possible.

Run `composer test-unit` to run unit tests, or `composer test` to run all static and dynamic checks altogether.

## Infection
[Infection](https://infection.github.io/) is a mutation testing framework that helps to keep quality of unit tests high.

Run `composer infection` to run Infection checks on code.
