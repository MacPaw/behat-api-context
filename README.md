# Behat Api Context Bundle

|  Version  |                       Build Status                        |                              Code Coverage                               |  Latest Release   |
|:---------:|:---------------------------------------------------------:|:------------------------------------------------------------------------:|:-----------------:|
| `master`  |  [![CI][master Build Status Image]][master Build Status]  |  [![Coverage Status][master Code Coverage Image]][master Code Coverage]  | ![Latest Release] |
| `develop` | [![CI][develop Build Status Image]][develop Build Status] | [![Coverage Status][develop Code Coverage Image]][develop Code Coverage] |         -         |

## ⚠️ Deprecation Notice

> The `ORMContext` has been **deprecated** and **removed** from this package.  
> Please use the standalone package [`macpaw/behat-orm-context`](https://github.com/macpaw/behat-orm-context) instead.

---

## Installation

### Step 1: Install the Bundle

Run the following command in your project directory to install the bundle as a development dependency:

```bash
  composer require --dev macpaw/behat-api-context
```

> If you are using Symfony Flex, the bundle will be registered automatically.
> Otherwise, follow Step 2 to register the bundle manually.
### Step 2: Register the Bundle

If your project does **not** use Symfony Flex or the bundle does not provide a recipe, manually register it in `config/bundles.php`:

```php
<?php
// config/bundles.php

return [
    // ...
    BehatApiContext\BehatApiContextBundle::class => ['test' => true],
];
```

> ℹ️ The bundle should only be enabled in the `test` environment.

### Step 3: Configure Behat

Update your `behat.yml`:

```yaml
default:
  suites:
    default:
      contexts:
        - BehatApiContext\Context\ApiContext
        - BehatApiContext\Context\ORMContext
```

> If you also want to use `ORMContext`, install [macpaw/behat-orm-context](https://github.com/macpaw/behat-orm-context) and follow its setup instructions.

> 📄 **Migration Notice:** `OrmContext` will be removed from `behat-api-context` in the next major release.
> Please migrate to [`behat-orm-context`](https://github.com/macpaw/behat-orm-context) to avoid test failures.
> See the full [ORMContext Migration Plan](./docs/ormcontext-migration.md) for step-by-step instructions.


---

## Configuration

By default, the bundle provides the following configuration:
> This bundle does not yet include a Symfony recipe to automatically create the configuration file.
> If you need a specific configuration, you have to add it manually.  
> [Recipe in progress](https://github.com/MacPaw/BehatRedisContext/issues/2)

```yaml
behat_api_context:
  kernel_reset_managers: []
```

You can also add your own reset manager by overriding the configuration manually in `config/packages/test/behat_api_context.yaml`:

```yaml
behat_api_context:
  kernel_reset_managers: 
    - BehatApiContext\Service\ResetManager\DoctrineResetManager
```

---

## Usage

### Runnable request parameters

Main use case when tests need to use the current date.  
Instead of static data in some `.feature` file like this:

```gherkin
"""
{
    "dateTo": 1680360081,
    "dateFrom": 1680532881
}
"""
```

You can use dynamic expressions:

```gherkin
"""
{
    "dateTo": "<(new DateTimeImmutable())->add(new DateInterval('P6D'))->getTimestamp()>",
    "dateFrom": "<(new DateTimeImmutable())->add(new DateInterval('P2D'))->getTimestamp()>"
}
"""
```

#### To achieve this, several conditions must be met:
- Runnable code must be a string and placed inside `<>`.
- Do not add `return` keyword at the beginning, otherwise a `RuntimeException` will be thrown.
- Do not add a semicolon (`;`) at the end of the expression, otherwise a `RuntimeException` will be thrown.
- Avoid code that returns `null`, otherwise a `RuntimeException` will be thrown.

---

[master Build Status]: https://github.com/MacPaw/behat-api-context/actions/workflows/ci.yaml
[master Build Status Image]: https://github.com/MacPaw/behat-api-context/actions/workflows/ci.yaml/badge.svg?branch=master
[develop Build Status]: https://github.com/MacPaw/behat-api-context/actions/workflows/ci.yaml
[develop Build Status Image]: https://github.com/MacPaw/behat-api-context/actions/workflows/ci.yaml/badge.svg?branch=develop
[master Code Coverage]: https://codecov.io/gh/macpaw/behat-api-context/branch/master
[master Code Coverage Image]: https://img.shields.io/codecov/c/github/macpaw/behat-api-context/master?logo=codecov
[develop Code Coverage]: https://codecov.io/gh/macpaw/behat-api-context/branch/develop
[develop Code Coverage Image]: https://img.shields.io/codecov/c/github/macpaw/behat-api-context/develop?logo=codecov
[Latest Release]: https://img.shields.io/github/v/release/macpaw/behat-api-context
