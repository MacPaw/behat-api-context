# Usage Examples

Real-world `.feature` examples using the API Context.

---

## 🔐 Example: Successful Login

```gherkin
Feature: Login API

  Scenario: Successful login returns token
    Given the request contains params:
    """
    {
      "email": "test@example.com",
      "password": "securepassword"
    }
    """
    When I send "POST" request to "api_v1_sign_in" route
    Then response status code should be 200
    And response should be JSON with variable fields "token":
    """
    {
      "token": "abc123"
    }
    """
```

---

## ❌ Example: Invalid Credentials

```gherkin
Scenario: Login with wrong password
  Given the request contains params:
  """
  {
    "email": "test@example.com",
    "password": "wrongpassword"
  }
  """
  When I send "POST" request to "api_v1_sign_in" route
  Then response status code should be 401
  And response should be JSON:
  """
  {
    "error": "Invalid credentials"
  }
  """
```

---

## 🕓 Example: Relative Dates in Request

```gherkin
Scenario: Create report with dynamic date range
  Given the request contains params:
  """
  {
    "dateFrom": "<(new DateTimeImmutable('-7 days'))->format('Y-m-d')>",
    "dateTo": "<(new DateTimeImmutable())->format('Y-m-d')>"
  }
  """
  When I send a "POST" request to "api_v1_generate_reports"
  Then response status code should be 201
```
