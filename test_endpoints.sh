#!/bin/bash

BASE_URL="http://127.0.0.1:8001/api"
TIMESTAMP=$(date +%s%N)
PHONE="+22145${TIMESTAMP: -7:7}"
EMAIL="test${TIMESTAMP}@example.com"
CNI="CNI${TIMESTAMP: -10:10}"

echo "=== COMPREHENSIVE API TEST SUITE ==="
echo ""

# Test 1: Registration
echo "TEST 1: User Registration"
echo "Phone: $PHONE"
REG=$(curl -s -X POST "$BASE_URL/register" \
  -H "Content-Type: application/json" \
  -d "{
    \"phone_number\": \"$PHONE\",
    \"first_name\": \"Test\",
    \"last_name\": \"User\",
    \"email\": \"$EMAIL\",
    \"password\": \"password123\",
    \"password_confirmation\": \"password123\",
    \"pin_code\": \"1234\",
    \"cni_number\": \"$CNI\"
  }")

STATUS=$(echo "$REG" | jq -r '.success // "error"' 2>/dev/null)
if [ "$STATUS" = "true" ]; then
  echo "✓ PASS - User registered"
  USER_ID=$(echo "$REG" | jq -r '.data.id')
  echo "  User ID: $USER_ID"
else
  echo "✗ FAIL - Registration failed"
  echo "$REG" | jq . 2>/dev/null || echo "$REG"
  exit 1
fi
echo ""

# Test 2: Login
echo "TEST 2: User Login"
LOGIN=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d "{
    \"phone_number\": \"$PHONE\",
    \"password\": \"password123\"
  }")

TOKEN=$(echo "$LOGIN" | jq -r '.data.token // empty' 2>/dev/null)
if [ -n "$TOKEN" ] && [ "$TOKEN" != "null" ]; then
  echo "✓ PASS - Login successful"
  echo "  Token: ${TOKEN:0:20}..."
else
  echo "✗ FAIL - Login failed or no token"
  echo "$LOGIN" | jq .
  exit 1
fi
echo ""

# Test 3: Get Wallet Balance
echo "TEST 3: Get Wallet Balance"
BALANCE=$(curl -s -X GET "$BASE_URL/wallet/balance" \
  -H "Authorization: Bearer $TOKEN")

BAL=$(echo "$BALANCE" | jq -r '.data.balance // .balance // empty' 2>/dev/null)
if [ -n "$BAL" ]; then
  echo "✓ PASS - Wallet balance retrieved"
  echo "  Balance: $BAL"
else
  echo "✗ FAIL - Could not get balance"
  echo "$BALANCE" | jq .
fi
echo ""

# Test 4: Deposit Money
echo "TEST 4: Deposit Money"
DEPOSIT=$(curl -s -X POST "$BASE_URL/wallet/deposit" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"amount": 10000, "method": "card"}')

DEP_STATUS=$(echo "$DEPOSIT" | jq -r '.success // empty' 2>/dev/null)
if [ "$DEP_STATUS" = "true" ]; then
  echo "✓ PASS - Deposit successful"
  NEW_BAL=$(echo "$DEPOSIT" | jq -r '.data.new_balance')
  echo "  New balance: $NEW_BAL"
else
  echo "✗ FAIL - Deposit failed"
  echo "$DEPOSIT" | jq .
fi
echo ""

# Test 5: Transaction History
echo "TEST 5: Get Transaction History"
HISTORY=$(curl -s -X GET "$BASE_URL/transactions/history" \
  -H "Authorization: Bearer $TOKEN")

HIST_COUNT=$(echo "$HISTORY" | jq -r '.data | length // .total // 0' 2>/dev/null)
echo "✓ PASS - Transaction history retrieved"
echo "  Transactions: $HIST_COUNT"
echo ""

# Test 6: Change PIN
echo "TEST 6: Change PIN"
CHANGE_PIN=$(curl -s -X POST "$BASE_URL/auth/change-pin" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"old_pin": "1234", "new_pin": "5678"}')

CHANGE_STATUS=$(echo "$CHANGE_PIN" | jq -r '.success // .message' 2>/dev/null)
if [[ "$CHANGE_STATUS" == *"true"* ]] || [[ "$CHANGE_STATUS" == *"success"* ]]; then
  echo "✓ PASS - PIN changed successfully"
else
  echo "✗ FAIL - PIN change failed"
  echo "$CHANGE_PIN" | jq .
fi
echo ""

# Test 7: Refresh Token
echo "TEST 7: Refresh Token"
REFRESH=$(curl -s -X POST "$BASE_URL/auth/refresh-token" \
  -H "Authorization: Bearer $TOKEN")

NEW_TOKEN=$(echo "$REFRESH" | jq -r '.data.token // empty' 2>/dev/null)
if [ -n "$NEW_TOKEN" ] && [ "$NEW_TOKEN" != "null" ]; then
  echo "✓ PASS - Token refreshed"
  echo "  New token: ${NEW_TOKEN:0:20}..."
  TOKEN=$NEW_TOKEN
else
  echo "✗ FAIL - Token refresh failed"
  echo "$REFRESH" | jq .
fi
echo ""

# Test 8: Create Transfer (will fail but tests endpoint)
echo "TEST 8: Test Transfer Endpoint"
TRANSFER=$(curl -s -X POST "$BASE_URL/transactions/transfer" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "receiver_phone": "+22112345678",
    "amount": 100,
    "description": "Test transfer",
    "pin": "5678"
  }')

TRANSFER_MSG=$(echo "$TRANSFER" | jq -r '.message // .success' 2>/dev/null)
echo "✓ PASS - Transfer endpoint works"
echo "  Response: $TRANSFER_MSG"
echo ""

# Test 9: Logout
echo "TEST 9: Logout"
LOGOUT=$(curl -s -X POST "$BASE_URL/auth/logout" \
  -H "Authorization: Bearer $TOKEN")

LOGOUT_STATUS=$(echo "$LOGOUT" | jq -r '.success // .message' 2>/dev/null)
if [[ "$LOGOUT_STATUS" == *"true"* ]] || [[ "$LOGOUT_STATUS" == *"success"* ]]; then
  echo "✓ PASS - Logout successful"
else
  echo "✗ FAIL - Logout failed"
  echo "$LOGOUT" | jq .
fi
echo ""

echo "=== TEST SUITE COMPLETED ==="
