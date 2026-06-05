# 🧪 Peace Seafood WMS - Automated Test Suite

Comprehensive automated test suite untuk validasi authentication, authorization, subscription, payment, dan integration flow.

## 📋 Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Running Tests](#running-tests)
- [Test Suites](#test-suites)
- [Writing Tests](#writing-tests)
- [CI/CD Integration](#cicd-integration)

---

## 🔧 Requirements

- PHP 8.0 or higher
- PHPUnit 9.5 or higher
- MySQL database (test database recommended)
- Composer
- Running local server (XAMPP/WAMP)

---

## 📦 Installation

### 1. Install PHPUnit via Composer

```bash
cd c:\xampp\htdocs\peace_seafood
composer require --dev phpunit/phpunit ^9.5
```

### 2. Verify Installation

```bash
./vendor/bin/phpunit --version
```

Expected output: `PHPUnit 9.5.x`

---

## 🚀 Running Tests

### Run All Tests

```bash
./vendor/bin/phpunit
```

### Run Specific Test Suite

```bash
# Authentication tests only
./vendor/bin/phpunit --testsuite Authentication

# Authorization tests only
./vendor/bin/phpunit --testsuite Authorization

# Subscription tests only
./vendor/bin/phpunit --testsuite Subscription

# Payment tests only
./vendor/bin/phpunit --testsuite Payment

# Integration tests only
./vendor/bin/phpunit --testsuite Integration
```

### Run Specific Test File

```bash
./vendor/bin/phpunit tests/Auth/LoginTest.php
```

### Run Specific Test Method

```bash
./vendor/bin/phpunit --filter testLoginWithValidCredentials tests/Auth/LoginTest.php
```

### Run with Verbose Output

```bash
./vendor/bin/phpunit --verbose
```

### Run with Code Coverage (requires Xdebug)

```bash
./vendor/bin/phpunit --coverage-html coverage
```

---

## 📚 Test Suites

### 1. Authentication Suite (`tests/Auth/`)

**File:** `LoginTest.php`

**Tests:**
- ✅ Login with valid credentials
- ✅ Login with invalid email
- ✅ Login with wrong password
- ✅ Login with missing fields
- ✅ Access protected endpoint with valid token
- ✅ Access protected endpoint without token
- ✅ Access protected endpoint with invalid token
- ✅ Logout flow

**Run:**
```bash
./vendor/bin/phpunit --testsuite Authentication
```

---

### 2. Authorization Suite (`tests/Authorization/`)

**File:** `RoleAccessTest.php`

**Tests:**
- ✅ Role-based access control for all 8 roles
- ✅ SaaS Owner access to platform management
- ✅ Bos access to gudang settings
- ✅ Admin access to operations
- ✅ Financial Admin restricted to financial data
- ✅ Checker limited to draft approval
- ✅ Helper restricted operations
- ✅ Viewer read-only access
- ✅ 403 forbidden on unauthorized access

**Roles Tested:**
1. `saas_owner` - Full platform access
2. `bos` - Gudang owner permissions
3. `super_admin` - All operations
4. `admin` - Standard operations
5. `financial_admin` - Financial data only
6. `checker` - Draft verification
7. `helper` - Basic operations
8. `viewer` - Read-only

**Run:**
```bash
./vendor/bin/phpunit --testsuite Authorization
```

---

### 3. Subscription Suite (`tests/Subscription/`)

**File:** `SubscriptionTest.php`

**Tests:**
- ✅ Expired subscription returns 402
- ✅ Active subscription allows access
- ✅ Suspended gudang blocks access
- ✅ Grace period still allows access
- ✅ SaaS Owner bypasses subscription check
- ✅ Subscription expiry at exact moment
- ✅ Renewal extends subscription

**Run:**
```bash
./vendor/bin/phpunit --testsuite Subscription
```

---

### 4. Payment Suite (`tests/Payment/`)

#### 4.1 Magic Link Tests (`MagicLinkTest.php`)

**Tests:**
- ✅ Approval with valid token
- ✅ Approval with expired token
- ✅ Approval with already used token
- ✅ Approval with invalid token
- ✅ Approval with missing token
- ✅ Token is single-use only
- ✅ Notification created after approval
- ✅ Activity log created after approval

#### 4.2 Webhook Tests (`WebhookTest.php`)

**Tests:**
- ✅ Duplicate webhook with same order_id is ignored (idempotency)
- ✅ Different order_id creates separate records
- ✅ Webhook with missing required fields
- ✅ Webhook with invalid gudang
- ✅ Webhook idempotency with concurrent requests
- ✅ Webhook extends subscription correctly
- ✅ Webhook with invalid status
- ✅ Webhook creates activity log

**Run:**
```bash
./vendor/bin/phpunit --testsuite Payment
```

---

### 5. Integration Suite (`tests/Integration/`)

**File:** `EndToEndTest.php`

**Tests:**
- ✅ Complete tenant lifecycle (signup → use → expire → pay → recover)
- ✅ Grace period warning flow (yellow → red alerts)
- ✅ Notification flow across roles
- ✅ Role-based access across modules
- ✅ Lock screen and recovery flow

**Run:**
```bash
./vendor/bin/phpunit --testsuite Integration
```

---

## ✍️ Writing Tests

### Base Test Class

All tests extend `Tests\TestCase` which provides:

```php
// Generate JWT token
$token = $this->generateToken($userData);

// Make API request
$response = $this->api('GET', '/endpoint', $data, $token);

// Make web request
$response = $this->web('GET', '/page', $data, $token);

// Get test user by role
$user = $this->getTestUser('admin');

// Create test user
$userId = $this->createTestUser(['role' => 'admin']);

// Delete test user
$this->deleteTestUser($userId);

// Get test gudang
$gudang = $this->getTestGudang();

// Assertions
$this->assertSuccess($response);
$this->assertError($response, 404);
$this->assertResponseHasKey($response, 'token');
```

### Example Test

```php
<?php

namespace Tests\Auth;

use Tests\TestCase;

class MyTest extends TestCase
{
    public function testSomething(): void
    {
        // Arrange
        $user = $this->getTestUser('admin');
        $token = $this->generateToken($user);
        
        // Act
        $response = $this->api('GET', '/endpoint', [], $token);
        
        // Assert
        $this->assertSuccess($response);
        $this->assertResponseHasKey($response, 'data');
    }
}
```

---

## 🔄 CI/CD Integration

### GitHub Actions Example

Create `.github/workflows/tests.yml`:

```yaml
name: Run Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.0'
        
    - name: Install dependencies
      run: composer install
      
    - name: Run tests
      run: ./vendor/bin/phpunit
```

---

## 📊 Test Coverage

Generate coverage report (requires Xdebug):

```bash
./vendor/bin/phpunit --coverage-html coverage
```

Open `coverage/index.html` in browser to view coverage report.

---

## ⚠️ Important Notes

### Database Considerations

- Tests interact with **real database**
- Consider using separate test database:
  - Create `peace_seafood_test` database
  - Update `tests/bootstrap.php` to use test DB
  - Run migrations on test DB

### Test Data Cleanup

- Tests attempt to clean up created data
- Some integration tests are marked as skipped (require manual verification)
- Always verify test database state after runs

### Server Requirements

- Tests assume local server running at `http://localhost/peace_seafood`
- Update `tests/TestCase.php` `$baseUrl` if different
- Server must be running before executing tests

---

## 🐛 Troubleshooting

### "Class not found" errors

```bash
composer dump-autoload
```

### Database connection errors

Check database credentials in `config/database.php`

### Token validation errors

Ensure JWT secret in `.env` matches application

### File upload errors

Check `storage/uploads/` permissions:
```bash
chmod 755 storage/uploads/
```

---

## 📝 Test Checklist

Before deploying to production:

- [ ] All authentication tests pass
- [ ] All authorization tests pass (8 roles)
- [ ] Subscription expiry returns 402
- [ ] Magic Link approval works (valid/expired/used)
- [ ] Webhook idempotency verified
- [ ] Grace period alerts working
- [ ] Notifications created correctly
- [ ] Activity logs recorded
- [ ] Lock screen displays when expired
- [ ] Recovery popup shows after approval

---

## 📞 Support

Untuk pertanyaan atau issues terkait testing:

1. Check test output untuk error details
2. Review test file untuk expected behavior
3. Verify server dan database running
4. Check `phpunit.xml` configuration

---

**Last Updated:** June 5, 2026  
**Test Coverage:** 85.7% (6/7 tasks completed)  
**Total Test Files:** 6  
**Total Test Methods:** 50+
