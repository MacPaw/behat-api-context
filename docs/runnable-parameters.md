# Runnable Parameters

Behat API Context supports inline PHP expressions in request payloads using angle brackets `<>`.

## ✨ Example

```gherkin
Given the request contains params:
"""
{
  "timestamp": "<(new DateTimeImmutable())->getTimestamp()>",
  "uuid": "<Ramsey\\Uuid\\Uuid::uuid4()>"
}
"""
```

## ✅ Rules

- Expressions must be wrapped in `<>`
- Do **not** use `return` statements
- Do **not** end expressions with a semicolon
- Expressions **must not** return `null`

## 💡 Common Use Cases

| Use Case      | Example                                                |
|---------------|--------------------------------------------------------|
| Timestamps    | `<(new DateTimeImmutable())->getTimestamp()>`          |
| UUIDs         | `<Ramsey\\Uuid\\Uuid::uuid4()>`                        |
| Relative Time | `<(new DateTimeImmutable('+1 day'))->format('Y-m-d')>` |
| Random value  | `<bin2hex(random_bytes(8))>`                           |

## 🔥 Pro Tip

You can mix static and dynamic parameters:

```json
{
  "start_date": "2024-01-01",
  "end_date": "<(new DateTimeImmutable('+7 days'))->format('Y-m-d')>"
}
```
