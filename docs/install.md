# Installation & Configuration

## 1. Install the package

```bash
composer require --dev macpaw/behat-api-context
```

> If you're using Symfony Flex, the bundle will be registered automatically.

## 2. Register the bundle manually (if not using Flex)

```php
// config/bundles.php
return [
    BehatApiContext\BehatApiContextBundle::class => ['test' => true],
];
```

## 3. Configure Behat
By default, the bundle provides the following configuration:
> This bundle does not yet include a Symfony recipe to automatically create the configuration file.
> If you need a specific configuration, you have to add it manually.  
> [Recipe in progress](https://github.com/MacPaw/BehatRedisContext/issues/2)

```yaml
# behat.yml
default:
  suites:
    default:
      contexts:
        - BehatApiContext\Context\ApiContext
```

## 4. Optional configuration
You can also add your own reset manager by overriding the configuration manually in `config/packages/behat_api_context.yaml`:

```yaml
# config/packages/behat_api_context.yaml
when@test:
    behat_api_context:
      kernel_reset_managers:
        - BehatApiContext\Service\ResetManager\DoctrineResetManager
```
