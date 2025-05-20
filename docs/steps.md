# API Context Behat Steps Documentation

## Table of Contents

* [Introduction](#introduction)
* [🧪Step: `Given the ":headerName" request header contains ":value"`](#step-given-the-headername-request-header-contains-value)
* [🧪Step: `Given the ":headerName" request header contains multiline value`](#step-given-the-headername-request-header-contains-multiline-value)
* [🧪Step: `Given the request ip is ":ip"`](#step-given-the-request-ip-is-ip)
* [🧪Step: `Given the request contains params`](#step-given-the-request-contains-params)
* [🧪Step: `When I send ":method" request to ":route" route`](#step-when-i-send-method-request-to-route-route)
* [🧪Step: `Then response status code should be :httpStatus`](#step-then-response-status-code-should-be-httpstatus)
* [🧪Step: `Then response is JSON`](#step-then-response-is-json)
* [🧪Step: `Then response should be empty`](#step-then-response-should-be-empty)
* [🧪Step: `Then response should be JSON`](#step-then-response-should-be-json)
* [🧪Step: `When I save ":paramPath" param from json response as ":valueKey"`](#step-when-i-save-parampath-param-from-json-response-as-valuekey)
* [🧪Step: `Then response should be JSON with variable fields ":variableFields"`](#step-then-response-should-be-json-with-variable-fields-variablefields)
* [🧪Step: `Then the ":headerName" response headers contains ":headerValue"`](#step-then-the-headername-response-headers-contains-headervalue)
* [📝Notes](#-notes)

---

## Introduction

This document describes the Behat step definitions used in the `ApiContext` class for testing HTTP API endpoints. Each step allows configuring the HTTP request, sending it, and asserting various properties of the response.

---

### 🧪Step: `Given the ":headerName" request header contains ":value"`

Set or replace the specified HTTP request header with the given value. Supports variable substitutions from saved context variables.

```gherkin
Given the "Authorization" request header contains "Bearer abc123"
```

---

### 🧪Step: `Given the ":headerName" request header contains multiline value`

Set or replace the specified HTTP request header with a multiline value block.

```gherkin
Given the "Authorization" request header contains multiline value:
  """
  Bearer 
  {{token}}
  UserId={{user_id}}
  """
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
    {
        "name": "status",
        "result": true,
        "message": "up",
        "params": []
    }
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

### 🧪Step: `Then the ":headerName" response headers contains ":headerValue"`

Asserts that the response contains the specified header with a value that includes the given substring.

```gherkin
Then the "Content-Type" response headers contains "application/json"
```

---

### 📝 Notes
- ✅ Variable substitutions ({{variable}}) are supported in headers and body.
- ✅ PHP expressions (<time()>, <uniqid()>, etc.) are dynamically evaluated.
- ✅ Saved values can be reused across steps for chaining and correlation.
- ✅ Requests are sent using Symfony route names, not raw URLs.
