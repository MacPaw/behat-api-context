Behat Api Context Bundle
=================================

| Version | Build Status | Code Coverage |
|:---------:|:-------------:|:-----:|
| `master`| [![CI][master Build Status Image]][master Build Status] | [![Coverage Status][master Code Coverage Image]][master Code Coverage] |
| `develop`| [![CI][develop Build Status Image]][develop Build Status] | [![Coverage Status][develop Code Coverage Image]][develop Code Coverage] |

Installation
============

Step 1: Download the Bundle
----------------------------------
Open a command console, enter your project directory and execute:

###  Applications that use Symfony Flex [in progress](https://github.com/MacPaw/BehatRedisContext/issues/2)

```console
$ composer require --dev macpaw/behat-api-context
```

### Applications that don't use Symfony Flex

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require --dev macpaw/behat-api-context
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

Step 2: Configure Behat
=============
Go to `behat.yml`

```yaml
# ...
  contexts:
    - BehatApiContext\Context\ApiContext
# ...
```

If you want use orm context add to `behat.yml`
```yaml
# ...
  contexts:
    - BehatApiContext\Context\ORMContext 
# ...
```

Usage 
=============

Runnable request parameters
----------------------------------
Main use case when tests need to use current date.
Instead of static data in some `testCaseName.feature`, like this:
```feature
"""
{
    "dateTo": 1680360081,
    "dateFrom": 1680532881,
}
"""
```
Can use, for example:
```feature
"""
{
    "dateTo": "<(new DateTimeImmutable())->add(new DateInterval('P6D'))->getTimeStamp()>",
    "dateFrom": "<(new DateTimeImmutable())->add(new DateInterval('P2D'))->getTimeStamp()>",
}
"""
```

#### To accomplish this, several conditions must be met:
- Runnable code must be a string and placed in `<>`
- Should not add `return` keyword at the beginning, otherwise will get RuntimeException
- Should not add `;` keyword at the end, otherwise will get RuntimeException
- Should not use the code that returns `null`, otherwise will get RuntimeException


[master Build Status]: https://github.com/macpaw/behat-api-context/actions?query=workflow%3ACI+branch%3Amaster
[master Build Status Image]: https://github.com/macpaw/behat-api-context/workflows/CI/badge.svg?branch=master
[develop Build Status]: https://github.com/macpaw/behat-api-context/actions?query=workflow%3ACI+branch%3Adevelop
[develop Build Status Image]: https://github.com/macpaw/behat-api-context/workflows/CI/badge.svg?branch=develop
[master Code Coverage]: https://codecov.io/gh/macpaw/behat-api-context/branch/master
[master Code Coverage Image]: https://img.shields.io/codecov/c/github/macpaw/behat-api-context/master?logo=codecov
[develop Code Coverage]: https://codecov.io/gh/macpaw/behat-api-context/branch/develop
[develop Code Coverage Image]: https://img.shields.io/codecov/c/github/macpaw/behat-api-context/develop?logo=codecov
