# Quick Commands - OMPAY API

## Testing Commands

### Run All Tests
```bash
composer test
php artisan test
```

### Run Tests by Category
```bash
# Unit tests only
php artisan test tests/Unit

# Feature tests only
php artisan test tests/Feature

# Service tests
php artisan test tests/Unit/Services

# Controller tests
php artisan test tests/Feature/Controllers
```

### Run Specific Test Classes
```bash
# Audit logging tests
php artisan test tests/Unit/Services/AuditLogServiceTest.php

# Cache tests
php artisan test tests/Unit/Services/CacheServiceTest.php

# Transaction tests
php artisan test tests/Feature/Controllers/TransactionControllerTest.php

# Wallet tests
php artisan test tests/Feature/Controllers/WalletControllerTest.php

# Health check tests
php artisan test tests/Feature/Controllers/HealthControllerTest.php

# Authentication tests
php artisan test tests/Feature/Controllers/AuthControllerTest.php
```

### Run Specific Test Methods
```bash
# Single test method
php artisan test tests/Unit/Services/CacheServiceTest.php --filter=test_set_and_get_balance

# All tests matching pattern
php artisan test --filter=transfer

# Tests containing "deposit"
php artisan test --filter=deposit
```

### Test Coverage Reports
```bash
# Show coverage in terminal
php artisan test --coverage

# Generate HTML report
php artisan test --coverage-html coverage/

# With minimum threshold
php artisan test --coverage --coverage-min=80

# Open HTML report (macOS)
open coverage/index.html

# Open HTML report (Linux)
xdg-open coverage/index.html

# Open HTML report (Windows)
start coverage/index.html
```

### Verbose Testing
```bash
# Detailed output
php artisan test -v

# Very detailed
php artisan test --verbose

# With failure details
php artisan test -v --no-coverage
```

---

## Health Check Commands

### Test Health Endpoints
```bash
# Basic health check
curl http://localhost:8000/api/health

# Detailed health check
curl http://localhost:8000/api/health/detailed

# Format output with jq
curl -s http://localhost:8000/api/health | jq

# Watch health continuously
watch -n 5 'curl -s http://localhost:8000/api/health | jq'

# Check status code
curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8000/api/health

# Pretty print JSON
curl -s http://localhost:8000/api/health | jq '.'
```

### Health Monitoring
```bash
# Monitor health in background
watch -c 'curl -s http://localhost:8000/api/health | jq "."'

# Check every 10 seconds
while true; do curl -s http://localhost:8000/api/health | jq '.status'; sleep 10; done

# Alert if down
curl -s http://localhost:8000/api/health | grep -q '"status":"ok"' || echo "ALERT: API Down"
```

---

## Logging Commands

### View Audit Logs
```bash
# View recent logs
tail -f storage/logs/audit.log

# Last 100 lines
tail -100 storage/logs/audit.log

# Search for transfers
grep "Transfer" storage/logs/audit.log

# Search for failures
grep "failed" storage/logs/audit.log

# Count transactions by type
grep -c "Transfer" storage/logs/audit.log
grep -c "Deposit" storage/logs/audit.log
grep -c "Payment" storage/logs/audit.log

# Real-time log filtering
tail -f storage/logs/audit.log | grep "Transfer"

# View logs by time
grep "2025-11-13" storage/logs/audit.log
```

### Audit Log Analysis
```bash
# Count total transactions
grep "Transaction:" storage/logs/audit.log | wc -l

# Count successful transactions
grep "successful" storage/logs/audit.log | wc -l

# Count failed transactions
grep "failed" storage/logs/audit.log | wc -l

# Summary of transaction types
grep -o '"transaction_type":"[^"]*"' storage/logs/audit.log | sort | uniq -c

# Find transactions over 10000
grep -E '"amount":[0-9]{5,}' storage/logs/audit.log
```

### Clear Logs (be careful!)
```bash
# Clear audit logs
echo "" > storage/logs/audit.log

# Clear all logs
echo "" > storage/logs/laravel.log
```

---

## Cache Commands

### Redis Cache Management
```bash
# Connect to Redis
redis-cli

# Inside redis-cli:
# List all cache keys
KEYS user.*

# Get specific key
GET user:{user_id}.balance

# Delete specific key
DEL user:{user_id}.balance

# Clear all cache
FLUSHALL

# Check cache size
DBSIZE

# Monitor real-time
MONITOR

# Get cache stats
INFO stats
```

### Cache Operations
```bash
# View balance cache key
redis-cli GET user:{user_id}.balance

# Clear user balance cache
redis-cli DEL user:{user_id}.balance

# List all user caches
redis-cli KEYS "user.*"

# Clear all caches
redis-cli FLUSHDB

# Check cache memory usage
redis-cli INFO memory
```

### Cache Testing
```bash
# Test cache hit rate
# First request (no cache)
curl http://localhost:8000/api/wallet/balance -H "Authorization: Bearer {token}"

# Second request (from cache)
curl http://localhost:8000/api/wallet/balance -H "Authorization: Bearer {token}"
# Should show "cached": true in response
```

---

## Database Commands

### Database Inspection
```bash
# Connect to database
php artisan tinker

# Inside tinker:
User::count()
Transaction::count()
Transaction::where('type', 'transfer')->count()
```

### Run Migrations
```bash
# Run all migrations
php artisan migrate

# Run with seeding
php artisan migrate --seed

# Rollback last migration
php artisan migrate:rollback

# Rollback all
php artisan migrate:reset

# Refresh (rollback and migrate)
php artisan migrate:refresh

# Migrate fresh (drop and recreate)
php artisan migrate:fresh --seed
```

---

## Development Commands

### Code Quality
```bash
# Check PHP syntax
php -l app/Http/Controllers/TransactionController.php

# Check all controllers
find app/Http/Controllers -name "*.php" -exec php -l {} \;

# Validate composer
composer validate
```

### Code Formatting
```bash
# Format file (if configured)
php artisan pint app/Http/Controllers/TransactionController.php

# Format all app directory
php artisan pint app/
```

### Static Analysis
```bash
# If PHPStan installed
./vendor/bin/phpstan analyze app/

# If Psalm installed
./vendor/bin/psalm
```

---

## API Testing Commands

### Authentication Flow
```bash
# 1. Login (send OTP)
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"phone_number":"+221234567890"}'

# 2. Verify OTP (get token)
curl -X POST http://localhost:8000/api/auth/verify-otp \
  -H "Content-Type: application/json" \
  -d '{"phone_number":"+221234567890","otp":"123456"}'

# Extract token
TOKEN=$(curl -s -X POST http://localhost:8000/api/auth/verify-otp \
  -H "Content-Type: application/json" \
  -d '{"phone_number":"+221234567890","otp":"123456"}' | jq -r '.data.token')

echo $TOKEN
```

### Wallet Operations
```bash
# Get balance
curl -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/wallet/balance

# Deposit money
curl -X POST http://localhost:8000/api/wallet/deposit \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"amount":10000}'
```

### Transactions
```bash
# Get transaction history
curl -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/transactions/history

# Transfer money
curl -X POST http://localhost:8000/api/transactions/transfer \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"receiver_phone":"+221987654321","amount":5000}'

# Payment to merchant
curl -X POST http://localhost:8000/api/compte/{wallet_id}/payment \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"amount":5000,"merchant_identifier":"+221111111111"}'
```

---

## Docker Commands (if using Docker)

### Build and Run
```bash
# Build image
docker build -t ompay .

# Run container
docker run -p 8000:8000 ompay

# Run tests in container
docker exec ompay composer test

# View logs
docker logs -f ompay

# Exec into container
docker exec -it ompay bash
```

---

## Deployment Commands

### Pre-Deployment Checks
```bash
# Run all tests
composer test

# Check coverage
php artisan test --coverage

# Validate config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear cache
php artisan cache:clear
php artisan config:clear
```

### Production Deploy
```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### Health Check for Deployment
```bash
# Verify API is ready
curl -f http://localhost:8000/api/health || exit 1

# Check detailed health
curl -f http://localhost:8000/api/health/detailed || exit 1
```

---

## Monitoring Commands

### Real-time Monitoring
```bash
# Watch health status
watch -c 'curl -s http://localhost:8000/api/health | jq ".status"'

# Monitor logs
tail -f storage/logs/audit.log | grep -v "^$"

# Monitor cache size
watch -n 5 'redis-cli DBSIZE'

# Monitor queue
php artisan queue:monitor

# Monitor database
php artisan tinker
# DB::connection()->getReadPdo()->getAttribute(PDO::ATTR_CONNECTION_STATUS)
```

### Performance Monitoring
```bash
# Database query log
php artisan tinker
DB::enableQueryLog();
// ... run operations ...
dd(DB::getQueryLog());

# Cache performance
redis-cli INFO stats | grep hits

# Application logs
tail -f storage/logs/laravel.log
```

---

## Common Task Checklists

### Pre-Deployment
```bash
□ php artisan test --coverage              # Tests pass & coverage > 80%
□ composer validate                        # Composer valid
□ php artisan config:cache                 # Cache config
□ php artisan route:cache                  # Cache routes
□ curl http://localhost:8000/api/health   # Health check passes
```

### After Deployment
```bash
□ php artisan migrate --force              # Migrations run
□ curl http://localhost:8000/api/health   # Health check accessible
□ tail -f storage/logs/audit.log           # Logs flowing
□ redis-cli DBSIZE                         # Cache working
□ php artisan tinker                       # Database connected
```

### Debugging Issues
```bash
□ composer test                            # Run tests
□ php artisan test --verbose               # Verbose output
□ tail -f storage/logs/laravel.log         # Check error logs
□ redis-cli MONITOR                        # Monitor cache
□ php artisan tinker                       # Interactive shell
```

---

## Shortcuts

### Alias Setup (add to ~/.bashrc or ~/.zshrc)
```bash
# Test commands
alias test='composer test'
alias test-unit='php artisan test tests/Unit'
alias test-feature='php artisan test tests/Feature'
alias test-coverage='php artisan test --coverage'

# Health check
alias health='curl -s http://localhost:8000/api/health | jq'

# Logs
alias logs='tail -f storage/logs/laravel.log'
alias audit='tail -f storage/logs/audit.log'

# Database
alias tinker='php artisan tinker'
alias migrate='php artisan migrate'

# Cache
alias cache-clear='php artisan cache:clear'
alias redis-cli='redis-cli'
```

### Usage
```bash
test                    # Run all tests
test-coverage          # Generate coverage report
health                 # Check health
logs                   # Watch logs
audit                  # Watch audit logs
```

---

## Useful Shortcuts for Quick Testing

```bash
# Test specific feature
php artisan test tests/Feature/Controllers/TransactionControllerTest.php --filter=transfer

# Test and generate coverage
php artisan test && php artisan test --coverage

# Watch health continuously
watch -n 2 'curl -s localhost:8000/api/health | jq ".status"'

# Monitor all activity
watch -n 1 'echo "=== HEALTH ===" && curl -s localhost:8000/api/health/detailed | jq ".checks" && echo "=== CACHE ===" && redis-cli DBSIZE'
```

---

For more detailed information, see:
- `TESTING_GUIDE.md` - Comprehensive testing guide
- `IMPLEMENTATION_IMPROVEMENTS.md` - Implementation details
- `TASKS_COMPLETED.md` - Summary of all completed tasks
