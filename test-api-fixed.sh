#!/bin/bash

API_BASE="http://localhost:8001"
PHONE_NUMBER="+22245678901"
EMAIL="test@example.com"
PASSWORD="password123"
PIN="1234"
CNI="1234567890ABC"
FIRST_NAME="Test"
LAST_NAME="User"

echo "=== Testing Orange Money API (Fixed Routes) ==="
echo ""

# Test 1: Register User (using auth/register)
echo "1. Testing POST /v1/auth/register"
REGISTER_RESPONSE=$(curl -s -X POST "$API_BASE/v1/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "'$PHONE_NUMBER'",
    "first_name": "'$FIRST_NAME'",
    "last_name": "'$LAST_NAME'",
    "email": "'$EMAIL'",
    "password": "'$PASSWORD'",
    "password_confirmation": "'$PASSWORD'",
    "pin_code": "'$PIN'",
    "cni_number": "'$CNI'"
  }')
echo "Response: $REGISTER_RESPONSE"
echo ""

# Test 2: Login
echo "2. Testing POST /v1/auth/login"
LOGIN_RESPONSE=$(curl -s -X POST "$API_BASE/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "'$PHONE_NUMBER'",
    "password": "'$PASSWORD'"
  }')
echo "Response: $LOGIN_RESPONSE"
echo ""

# Extract token from login response
TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)
echo "Extracted Token: $TOKEN"
echo ""

if [ -z "$TOKEN" ]; then
  echo "Warning: Could not extract token. Protected routes will fail."
  TOKEN="dummy-token"
fi

# Test 3: Get Wallet Balance
echo "3. Testing GET /v1/wallet/balance"
curl -s -X GET "$API_BASE/v1/wallet/balance" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
echo ""
echo ""

# Test 4: Deposit Money
echo "4. Testing POST /v1/wallet/deposit"
curl -s -X POST "$API_BASE/v1/wallet/deposit" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 5000
  }'
echo ""
echo ""

# Test 5: Transfer Money
echo "5. Testing POST /v1/transactions/transfer"
curl -s -X POST "$API_BASE/v1/transactions/transfer" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "receiver_phone": "771234568",
    "amount": 1000,
    "description": "Test transfer"
  }'
echo ""
echo ""

# Test 6: Get Transaction History
echo "6. Testing GET /v1/transactions/history"
curl -s -X GET "$API_BASE/v1/transactions/history?page=1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
echo ""
echo ""

echo "=== All tests completed ==="
