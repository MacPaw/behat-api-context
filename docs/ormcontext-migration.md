## Migration plan for using ORMContext

### Step 1: 📦 Add `behat-orm-context` to `composer.json`
```bash
composer require --dev macpaw/behat-orm-context
```

### Step 2: 🧩 Register `OrmContext` in `behat.yml`
```yaml
default:
  suites:
    default:
      contexts:
        - BehatOrmContext\Context\OrmContext

```
> If you previously used `BehatApiContext\Context\OrmContext`, replace it with `BehatOrmContext\Context\OrmContext`

### Step 3: 🧪 Verify Scenarios
1. Run Behat tests:
```bash
  vendor/bin/behat
```

2. Ensure that all steps previously relying on OrmContext still work correctly after the migration.

### 🚨 Backward Compatibility Notice
> `OrmContext` will be removed from `behat-api-context` in a future major release. If you don't migrate, your tests will break after updating dependencies.

### 🔔 Recommendation
Perform the migration before the next major release of `behat-api-context` to avoid CI/CD disruptions and unexpected test failures in your production pipeline.