# Behat Api Context Bundle

| Version | Build Status | Code Coverage |
|:---------:|:-------------:|:-----:|
| `master` | [![CI][master Build Status Image]][master Build Status] | [![Coverage Status][master Code Coverage Image]][master Code Coverage] |
| `develop` | [![CI][develop Build Status Image]][develop Build Status] | [![Coverage Status][develop Code Coverage Image]][develop Code Coverage] |

## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute:

#### Applications that use Symfony Flex [in progress](https://github.com/MacPaw/BehatRedisContext/issues/2)

```bash
composer require --dev macpaw/behat-api-context
```

#### Applications that don't use Symfony Flex

Open a command console, enter your project directory and execute the following command to download the latest stable version of this bundle:

```bash
composer require --dev macpaw/behat-api-context
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.


Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            BehatApiContext\BehatApiContextBundle::class => ['test' => true],
        );

        // ...
    }

    // ...
}
```

---

## Step 2: Configure Behat

Go to `behat.yml`:

```yaml
# ...
  contexts:
    - BehatApiContext\Context\ApiContext
# ...
```

### Optional: Enable ORMContext

If you want to use `ORMContext`, you need to have `doctrine/orm` installed:

```bash
composer require --dev doctrine/orm
```

Then, update your `behat.yml`:

```yaml
# ...
  contexts:
    - BehatApiContext\Context\ORMContext 
# ...
```

---

## Configuration

By default, the bundle has the following configuration:

```yaml
behat_api_context:
  kernel_reset_managers:
    - BehatApiContext\Service\ResetManager\DoctrineResetManager
```

The `use_orm_context` parameter is no longer configurable manually. Its value is determined automatically based on whether the Doctrine ORM is installed:
> **Important:** This logic is applied internally and cannot be overridden via configuration.

| ORM Installed | Default `use_orm_context` |
|:-------------:|:-------------------------:|
| Yes           | `true`                    |
| No            | `false`                   |

---

# Usage

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
    "dateTo": "<(new DateTimeImmutable())->add(new DateInterval('P6D'))->getTimeStamp()>",
    "dateFrom": "<(new DateTimeImmutable())->add(new DateInterval('P2D'))->getTimeStamp()>"
}
"""
```

#### To achieve this, several conditions must be met:
- Runnable code must be a string and placed inside `<>`.
- Do not add `return` keyword at the beginning, otherwise a `RuntimeException` will be thrown.
- Do not add `;` at the end of the expression, otherwise a `RuntimeException` will be thrown.
- Avoid code that returns `null`, otherwise a `RuntimeException` will be thrown.

---

[master Build Status]: https://github.com/macpaw/behat-api-context/actions?query=workflow%3ACI+branch%3Amaster
[master Build Status Image]: https://github.com/macpaw/behat-api-context/workflows/CI/badge.svg?branch=master
[develop Build Status]: https://github.com/macpaw/behat-api-context/actions?query=workflow%3ACI+branch%3Adevelop
[develop Build Status Image]: https://github.com/macpaw/behat-api-context/workflows/CI/badge.svg?branch=develop
[master Code Coverage]: https://codecov.io/gh/macpaw/behat-api-context/branch/master
[master Code Coverage Image]: https://img.shields.io/codecov/c/github/macpaw/behat-api-context/master?logo=codecov
[develop Code Coverage]: https://codecov.io/gh/macpaw/behat-api-context/branch/develop
[develop Code Coverage Image]: https://img.shields.io/codecov/c/github/macpaw/behat-api-context/develop?logo=codecov
