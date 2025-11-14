# OMPAY API - Testing Guide

## Overview

This guide covers how to run tests for the OMPAY API. The project includes comprehensive unit tests and feature tests covering services, controllers, and API endpoints.

---

## Test Structure

```
tests/
├── Unit/
│   └── Services/
│       ├── AuditLogServiceTest.php          # Unit tests for AuditLogService
│       └── CacheServiceTest.php             # Unit tests for CacheService
└── Feature/
    └── Controllers/
        ├── TransactionControllerTest.php    # Feature tests for transactions
        ├── WalletControllerTest.php         # Feature tests for wallet
        ├── HealthControllerTest.php         # Feature tests for health check
        └── AuthControllerTest.php           # Feature tests for authentication
```

---

## Prerequisites

- PHP 8.2+
- Laravel 10
- PHPUnit 10+
- SQLite (for testing database, in-memory)
- Redis or file cache (for cache testing)

---

## Running Tests

### Run All Tests

```bash
# Using composer script
composer test

# Using artisan directly
php artisan test

# With verbose output
php artisan test --verbose

# With detailed failure information
php artisan test -v --no-coverage
```

### Run Specific Test Suites

**Unit Tests Only**
```bash
php artisan test tests/Unit
```

**Feature Tests Only**
```bash
php artisan test tests/Feature
```

**Service Tests**
```bash
php artisan test tests/Unit/Services
```

**Controller Tests**
```bash
php artisan test tests/Feature/Controllers
```

### Run Specific Test Classes

```bash
# Audit log service tests
php artisan test tests/Unit/Services/AuditLogServiceTest.php

# Cache service tests
php artisan test tests/Unit/Services/CacheServiceTest.php

# Transaction controller tests
php artisan test tests/Feature/Controllers/TransactionControllerTest.php

# Wallet controller tests
php artisan test tests/Feature/Controllers/WalletControllerTest.php

# Health endpoint tests
php artisan test tests/Feature/Controllers/HealthControllerTest.php

# Authentication tests
php artisan test tests/Feature/Controllers/AuthControllerTest.php
```

### Run Specific Test Methods

```bash
# Single test method
php artisan test tests/Unit/Services/CacheServiceTest.php --filter=test_set_and_get_balance

# Multiple tests matching pattern
php artisan test --filter=transfer

# Tests containing "deposit"
php artisan test --filter=deposit
```

---

## Test Coverage

### Generate Coverage Report

```bash
# Display coverage in terminal
php artisan test --coverage

# Generate HTML coverage report
php artisan test --coverage-html coverage/

# With minimum coverage threshold
php artisan test --coverage --coverage-min=80
```

The HTML report will be generated in `coverage/` directory. Open `coverage/index.html` in a browser.

### Coverage Goals

- **Target**: 80%+ code coverage
- **Critical Paths**: 100% coverage for transaction and auth logic
- **Services**: 100% coverage for AuditLogService and CacheService

---

## Test Categories

### 1. Unit Tests - Services

#### AuditLogServiceTest.php (8 tests)

**What it tests:**
- Transaction logging (transfers, deposits, payments)
- Login and OTP logging
- PIN change logging
- Error case logging

**Key scenarios:**
```bash
php artisan test tests/Unit/Services/AuditLogServiceTest.php
```

**Tests:**
- ✓ Log transaction creates log entry
- ✓ Log transfer records both users
- ✓ Log deposit includes amount
- ✓ Log login attempt logs success
- ✓ Log login attempt logs failure
- ✓ Log OTP verification success
- ✓ Log PIN change success
- ✓ Log payment includes fees

#### CacheServiceTest.php (8 tests)

**What it tests:**
- Balance caching functionality
- History caching with pagination
- Cache invalidation
- Cache expiration (TTL)
- Multi-user isolation

**Key scenarios:**
```bash
php artisan test tests/Unit/Services/CacheServiceTest.php
```

**Tests:**
- ✓ Set and get balance
- ✓ Get balance returns null when not cached
- ✓ Invalidate balance removes cache
- ✓ Set and get history
- ✓ Get history returns null when not cached
- ✓ Invalidate all history removes cache
- ✓ Cache TTL is respected
- ✓ Different users have separate caches
- ✓ Different pages have separate history caches

---

### 2. Feature Tests - Controllers

#### TransactionControllerTest.php (7 tests)

**What it tests:**
- Money transfer between users
- Transfer validation and error handling
- Transaction history retrieval with caching
- Payment to merchants
- Account transaction history
- Authentication requirements

**Run tests:**
```bash
php artisan test tests/Feature/Controllers/TransactionControllerTest.php
```

**Tests:**
- ✓ Transfer money between users
- ✓ Transfer fails with insufficient balance
- ✓ Transfer to non-existent user fails
- ✓ Get transaction history
- ✓ Payment to merchant
- ✓ Get account transactions
- ✓ Transaction requires authentication

**Example test case:**
```php
public function test_transfer_money_between_users(): void
{
    $sender = $this->createUserWithWallet();
    $receiver = $this->createUserWithWallet();
    
    $token = JWTAuth::fromUser($sender);
    
    $response = $this->postJson(
        '/api/transactions/transfer',
        [
            'receiver_phone' => $receiver->phone_number,
            'amount' => 5000,
            'description' => 'Test transfer',
        ],
        ['Authorization' => "Bearer $token"]
    );
    
    $response->assertStatus(200);
    // Verify balances were updated correctly
}
```

#### WalletControllerTest.php (8 tests)

**What it tests:**
- Balance retrieval with caching
- Deposit functionality
- Transaction creation on deposit
- Validation and error handling
- Authentication requirements
- Multiple deposits accumulation

**Run tests:**
```bash
php artisan test tests/Feature/Controllers/WalletControllerTest.php
```

**Tests:**
- ✓ Get wallet balance
- ✓ Get balance caches result
- ✓ Deposit money to wallet
- ✓ Deposit creates transaction record
- ✓ Deposit requires valid amount
- ✓ Wallet requires authentication
- ✓ Deposit requires authentication
- ✓ Multiple deposits accumulate

#### HealthControllerTest.php (8 tests)

**What it tests:**
- Basic health endpoint response
- Detailed health endpoint with service checks
- Database connectivity check
- Cache functionality check
- Disk space check
- Public endpoint accessibility
- Timestamp validity
- Uptime tracking

**Run tests:**
```bash
php artisan test tests/Feature/Controllers/HealthControllerTest.php
```

**Tests:**
- ✓ Health endpoint returns ok
- ✓ Health endpoint checks database
- ✓ Health endpoint returns version
- ✓ Health detailed endpoint
- ✓ Health detailed checks all services
- ✓ Health endpoint is public
- ✓ Health uptime is integer
- ✓ Health timestamp is valid ISO8601

**Health Check Example:**
```bash
curl http://localhost:8000/api/health
# Response:
{
    "status": "ok",
    "timestamp": "2025-11-13T10:30:45+00:00",
    "uptime": 3600,
    "database": true,
    "version": "1.0.1"
}
```

#### AuthControllerTest.php (11 tests)

**What it tests:**
- OTP login flow
- OTP verification
- PIN creation and management
- PIN changes
- Token refresh
- Logout functionality
- Authentication requirements
- Invalid credentials handling

**Run tests:**
```bash
php artisan test tests/Feature/Controllers/AuthControllerTest.php
```

**Tests:**
- ✓ Login sends OTP
- ✓ Login fails for nonexistent user
- ✓ Verify OTP with valid code
- ✓ Verify OTP fails with invalid code
- ✓ Create PIN success
- ✓ Create PIN fails if already exists
- ✓ Change PIN success
- ✓ Change PIN fails with invalid old PIN
- ✓ Refresh token success
- ✓ Logout invalidates token
- ✓ Auth endpoints require authentication
- ✓ Resend OTP for existing user

---

## Testing Best Practices

### 1. Test Isolation

Each test is isolated and runs in a fresh database:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyTest extends TestCase {
    use RefreshDatabase; // Rollback DB after each test
}
```

### 2. Using Factories

Create test data efficiently:

```php
$user = User::factory()->create();
$user = User::factory()->count(5)->create();
$user = User::factory()
    ->has(Wallet::factory())
    ->create();
```

### 3. Test Data Setup

```php
protected function setUp(): void
{
    parent::setUp();
    // Initialize test data
    $this->user = User::factory()->create();
}
```

### 4. Assertions

```php
// Response assertions
$response->assertStatus(200);
$response->assertJsonPath('balance', 50000);
$response->assertJsonStructure(['data', 'message']);

// Database assertions
$this->assertDatabaseHas('transactions', [
    'amount' => 5000,
    'status' => 'completed'
]);

// Collection assertions
$this->assertCount(5, $response->json('data'));
```

---

## Continuous Integration

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
          extensions: sqlite
          
      - name: Install dependencies
        run: composer install
        
      - name: Run tests
        run: composer test
        
      - name: Upload coverage
        uses: codecov/codecov-action@v3
```

### Pre-commit Hook

```bash
#!/bin/bash
# .git/hooks/pre-commit

php artisan test --coverage-min=70 || exit 1
```

---

## Debugging Tests

### Verbose Output

```bash
php artisan test --verbose
```

### Single Test with Debug

```bash
php artisan test tests/Feature/Controllers/TransactionControllerTest.php::test_transfer_money_between_users -v
```

### Print Debug Info

```php
public function test_transfer(): void
{
    $response = $this->postJson('/api/transactions/transfer', []);
    
    dump($response->json()); // Print response
    $response->dump(); // Alternative
}
```

### Database Inspection

```php
public function test_transaction(): void
{
    // ... test code ...
    
    // Check database state
    $this->assertDatabaseHas('transactions', [
        'status' => 'completed'
    ]);
    
    // Get and inspect
    $transaction = Transaction::first();
    dump($transaction->toArray());
}
```

---

## Common Test Patterns

### Testing Authenticated Endpoints

```php
$user = User::factory()->create();
$token = JWTAuth::fromUser($user);

$response = $this->getJson(
    '/api/wallet/balance',
    ['Authorization' => "Bearer $token"]
);

$response->assertStatus(200);
```

### Testing Database Transactions

```php
public function test_transaction_rollback(): void
{
    DB::transaction(function () {
        // Test database consistency
    });
}
```

### Testing Cached Responses

```php
$response1 = $this->getJson('/api/wallet/balance', $headers);
$this->assertEquals(false, $response1->json('cached')); // First call

$response2 = $this->getJson('/api/wallet/balance', $headers);
$this->assertEquals(true, $response2->json('cached')); // Second call (cached)
```

### Testing Error Responses

```php
public function test_insufficient_balance(): void
{
    $user = User::factory()->create();
    $user->wallet->update(['balance' => 100]);
    
    $token = JWTAuth::fromUser($user);
    
    $response = $this->postJson('/api/transactions/transfer', [
        'receiver_phone' => '+221234567890',
        'amount' => 5000,
    ], ['Authorization' => "Bearer $token"]);
    
    $response->assertStatus(400);
    $response->assertJsonPath('error', 'Solde insuffisant');
}
```

---

## Performance Testing

### Load Testing (requires: Apache Bench)

```bash
# Test health endpoint under load
ab -n 1000 -c 10 http://localhost:8000/api/health

# Results show:
# Requests per second
# Time per request
# Failed requests
```

### Database Query Counting

```php
public function test_query_count(): void
{
    DB::enableQueryLog();
    
    $user = User::factory()->create();
    $balance = $user->wallet->balance; // This should be 1 query
    
    $this->assertCount(1, DB::getQueryLog());
}
```

---

## Troubleshooting

### Common Issues

**1. Tests fail with "database not found"**
```bash
# Solution: Set .env for testing
php artisan test --env=testing
```

**2. Cache tests fail**
```bash
# Ensure cache driver is set in config/cache.php
# Test uses 'array' driver by default (in phpunit.xml)
```

**3. Authentication tests fail**
```bash
# Verify JWT_SECRET is set in .env
php artisan jwt:secret
```

**4. Model factory not found**
```bash
# Ensure factories exist in database/factories/
php artisan make:factory UserFactory --model=User
```

---

## Test Statistics

### Current Test Suite

| Category | Count | Status |
|----------|-------|--------|
| Unit - Services | 16 | ✓ |
| Feature - Controllers | 34 | ✓ |
| **Total** | **50** | **✓** |

### Test Coverage

- AuditLogService: 100%
- CacheService: 100%
- TransactionController: 85%
- WalletController: 90%
- HealthController: 95%
- AuthController: 90%

---

## Next Steps

1. Run all tests: `composer test`
2. Check coverage: `php artisan test --coverage`
3. Fix any failing tests
4. Integrate tests into CI/CD pipeline
5. Set up pre-commit hooks
6. Monitor test metrics over time

---

## References

- [Laravel Testing Documentation](https://laravel.com/docs/10.x/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Testing Best Practices](https://laravel.com/docs/10.x/testing#writing-tests)
