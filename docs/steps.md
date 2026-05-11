# API Context Behat Steps Documentation

## Table of Contents

* [Introduction](#introduction)
* [🧪Step: `Given the "…" request header contains "…"`](#step-given-the--request-header-contains-)
* [🧪Step: `Given the "…" request header contains multiline value`](#step-given-the--request-header-contains-multiline-value)
* [🧪Step: `Given the request JSON content type is used`](#step-given-the-request-json-content-type-is-used)
* [🧪Step: `Given the request ip is ":ip"`](#step-given-the-request-ip-is-ip)
* [🧪Step: `Given the request contains params`](#step-given-the-request-contains-params)
* [🧪Step: `When I send ":method" request to ":route" route`](#step-when-i-send-method-request-to-route-route)
* [🧪Step: `Then response status code should be :httpStatus`](#step-then-response-status-code-should-be-httpstatus)
* [🧪Step: `Then response is JSON`](#step-then-response-is-json)
* [🧪Step: `Then response should be empty`](#step-then-response-should-be-empty)
* [🧪Step: `Then response should be JSON`](#step-then-response-should-be-json)
* [🧪Step: `When I save ":paramPath" param from json response as ":valueKey"`](#step-when-i-save-parampath-param-from-json-response-as-valuekey)
* [🧪Step: `Then response should be JSON with variable fields ":variableFields"`](#step-then-response-should-be-json-with-variable-fields-variablefields)
* [🧪Step: `Then the "…" response headers contains "…"`](#step-then-the--response-headers-contains-)
* [📝Notes](#-notes)

---

## Introduction

This document describes the Behat step definitions used in the `ApiContext` class for testing HTTP API endpoints. Each step allows configuring the HTTP request, sending it, and asserting various properties of the response.

---

### 🧪Step: `Given the "…" request header contains "…"`

Set or replace the specified HTTP request header with the given value. Supports variable substitutions from saved context variables.

The header name and value **must be double-quoted** in Gherkin. This avoids Turnip placeholder limits (unquoted tokens do not include `-` or `/`, so values like `application/json` and names like `Content-Type` would not match).

```gherkin
Given the "Authorization" request header contains "Bearer abc123"
Given the "Content-Type" request header contains "application/json"
```

---

### 🧪Step: `Given the "…" request header contains multiline value`

Set or replace the specified HTTP request header with a multiline value block. The header name must be double-quoted (same rules as the single-line header step).

```gherkin
Given the "Authorization" request header contains multiline value:
  """
  Bearer 
  {{token}}
  UserId={{user_id}}
  """
```

---

### 🧪Step: `Given the request JSON content type is used`

Equivalent to `Given the "Content-Type" request header contains "application/json"`. Use this so POST, PUT, and PATCH requests JSON-encode the payload from `Given the request contains params` (see the send step below).

```gherkin
Given the request JSON content type is used
```

---

### 🧪Step: `Given the request ip is ":ip"`

Set the client IP address for the request by modifying the `REMOTE_ADDR` server parameter.

```gherkin
Given the request ip is "192.168.1.1"
```

---

### 🧪Step: `Given the request contains params`

Add parameters to the request payload or query string. Supports embedded PHP expressions wrapped in `< >` that will be evaluated. Also saves parameters for reuse.
> See [Runnable Parameters](runnable-parameters.md) for more details on how expressions work.

```gherkin
Given the request contains params:
  """
  {
    "user_id": "<time()>",
    "active": true
  }
  """
```

---

### 🧪Step: `When I send ":method" request to ":route" route`

Sends an HTTP request with the specified method (`GET`, `POST`, `PUT`, `PATCH`) to the Symfony route named `:route`. Uses previously configured headers and parameters.

For **POST**, **PUT**, and **PATCH**, the body is JSON-encoded when `Content-Type` contains `application/json` (set with the quoted header step or `Given the request JSON content type is used`). Otherwise parameters are sent as form fields.

```gherkin
When I send "POST" request to "api_login" route
```

---

### 🧪Step: `Then response status code should be :httpStatus`

Asserts that the HTTP response status matches the expected status code.

```gherkin
Then response status code should be 200
```

---

### 🧪Step: `Then response is JSON`

Asserts that the response body contains valid, non-empty JSON.

```gherkin
Then response is JSON
```

---

### 🧪Step: `Then response should be empty`

Asserts that the response body is empty.

```gherkin
Then response should be empty
```
---

### 🧪Step: `Then response should be JSON`

Compare the actual JSON response to the expected JSON block for equality.

```gherkin
Then response should be JSON:
  """
  {
    "success": true,
    "data": {
      "id": 123
    }
  }
  """
```

---

### 🧪Step: `When I save ":paramPath" param from json response as ":valueKey"`

Extracts a value from the JSON response at the dot-notated path and saves it in the context for later use.

```gherkin
When I save "data.id" param from json response as "userId"
```

---

### 🧪Step: `Then response should be JSON with variable fields ":variableFields"`

Compares the actual JSON response to the expected JSON while ignoring differences in specified fields that may vary (e.g., timestamps, UUIDs, etc.).

You can also use regular expressions to match the values of these variable fields. To do so, prefix the expected value with a ~ (tilde). This indicates that the value should match the given regex pattern rather than be compared literally.

For example:

To match any numeric timestamp (e.g., Unix timestamp), use:
`~^\\d+$`

To match a UUID value, use:
`~^[0-9a-fA-F]{8}\\-[0-9a-fA-F]{4}\\-[0-9a-fA-F]{4}\\-[0-9a-fA-F]{4}\\-[0-9a-fA-F]{12}$`

```gherkin
Then response should be JSON with variable fields "id, createdAt, updatedAt":
  """
  {
    "user": {
      "id": "~^[0-9a-fA-F]{8}\\-[0-9a-fA-F]{4}\\-[0-9a-fA-F]{4}\\-[0-9a-fA-F]{4}\\-[0-9a-fA-F]{12}$",
      "name": "John",
      "createdAt": "~^\\d+$",
      "updatedAt": "~^\\d+$"
    }
  }
  """
```
This allows flexibility in matching dynamic values while still validating the structure and correctness of the response.

---

### 🧪Step: `Then the "…" response headers contains "…"`

Asserts that the response contains the specified header with a value that includes the given substring. The header name and expected fragment **must be double-quoted** (same reason as request header steps).

```gherkin
Then the "Content-Type" response headers contains "application/json"
```

---

### 📝 Notes
- ⚠️ **Breaking change (header steps):** Unquoted header names and values are no longer accepted for the request and response header steps; use double quotes (or `Given the request JSON content type is used` for JSON APIs).
- ✅ Variable substitutions ({{variable}}) are supported in headers and body.
- ✅ PHP expressions (<time()>, <uniqid()>, etc.) are dynamically evaluated.
- ✅ Saved values can be reused across steps for chaining and correlation.
- ✅ Requests are sent using Symfony route names, not raw URLs.
