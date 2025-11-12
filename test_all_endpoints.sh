#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

BASE_URL="http://localhost:8000/api"
API_TOKEN=""
PHONE_NUMBER=""
EMAIL=""

echo -e "${YELLOW}====== OMPAY API - COMPLETE ENDPOINT TEST ======${NC}\n"

# 1. Test Health Check
echo -e "${YELLOW}1. Testing Health Check${NC}"
curl -s http://localhost:8000/ | jq . || echo "Failed: Health check"
echo ""

# 2. Register a new user
echo -e "${YELLOW}2. Testing Register Endpoint${NC}"
PHONE_NUMBER=$(head /dev/urandom | tr -dc 0-9 | head -c 10)
EMAIL_PREFIX=$(uuidgen | cut -d'-' -f1)
EMAIL="${EMAIL_PREFIX}@example.com"
CNI_NUMBER=$(head /dev/urandom | tr -dc 0-9 | head -c 13)

echo "Registration data:"
echo "  Phone: +223$PHONE_NUMBER"
echo "  Email: $EMAIL"
echo "  CNI: $CNI_NUMBER"

REGISTER_RESPONSE=$(curl -s -X POST "$BASE_URL/register" \
  -H "Content-Type: application/json" \
  -d "{
    \"phone_number\": \"+223$PHONE_NUMBER\",
    \"first_name\": \"Test\",
    \"last_name\": \"User\",
    \"email\": \"$EMAIL\",
    \"password\": \"password123\",
    \"password_confirmation\": \"password123\",
    \"pin_code\": \"1234\",
    \"cni_number\": \"$CNI_NUMBER\"
  }")

echo "$REGISTER_RESPONSE" | jq . || echo "$REGISTER_RESPONSE"
echo ""

# Extract user ID from register response
USER_ID=$(echo "$REGISTER_RESPONSE" | jq -r '.data.id // empty' 2>/dev/null)
echo "Registered User ID: $USER_ID"
echo ""

# 3. Login
echo -e "${YELLOW}3. Testing Login Endpoint${NC}"
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d "{
    \"phone_number\": \"+223$PHONE_NUMBER\",
    \"password\": \"password123\"
  }")

echo "$LOGIN_RESPONSE" | jq . || echo "$LOGIN_RESPONSE"
API_TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.token // empty' 2>/dev/null)
echo "Auth Token: ${API_TOKEN:0:20}..."
echo ""

if [ -z "$API_TOKEN" ] || [ "$API_TOKEN" == "null" ]; then
  echo -e "${RED}ERROR: Could not obtain API token${NC}"
  exit 1
fi

# 4. Verify OTP
echo -e "${YELLOW}4. Testing Verify OTP Endpoint${NC}"
curl -s -X POST "$BASE_URL/auth/verify-otp" \
  -H "Content-Type: application/json" \
  -d "{
    \"phone_number\": \"+223$PHONE_NUMBER\",
    \"otp\": \"123456\"
  }" | jq . || echo "Failed: Verify OTP"
echo ""

# 5. Resend OTP
echo -e "${YELLOW}5. Testing Resend OTP Endpoint${NC}"
curl -s -X POST "$BASE_URL/auth/resend-otp" \
  -H "Content-Type: application/json" \
  -d "{
    \"phone_number\": \"+223$PHONE_NUMBER\"
  }" | jq . || echo "Failed: Resend OTP"
echo ""

# 6. Create PIN
echo -e "${YELLOW}6. Testing Create PIN Endpoint${NC}"
curl -s -X POST "$BASE_URL/auth/create-pin" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $API_TOKEN" \
  -d "{
    \"pin\": \"5678\"
  }" | jq . || echo "Failed: Create PIN"
echo ""

# 7. Get Wallet Balance
echo -e "${YELLOW}7. Testing Get Wallet Balance Endpoint${NC}"
curl -s -X GET "$BASE_URL/wallet/balance" \
  -H "Authorization: Bearer $API_TOKEN" | jq . || echo "Failed: Get Balance"
echo ""

# 8. Deposit
echo -e "${YELLOW}8. Testing Deposit Endpoint${NC}"
curl -s -X POST "$BASE_URL/wallet/deposit" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $API_TOKEN" \
  -d "{
    \"amount\": 10000
  }" | jq . || echo "Failed: Deposit"
echo ""

# 9. Register second user for transfer test
echo -e "${YELLOW}9. Registering Second User for Transfer Test${NC}"
PHONE_NUMBER_2=$(head /dev/urandom | tr -dc 0-9 | head -c 10)
EMAIL_PREFIX_2=$(uuidgen | cut -d'-' -f1)
EMAIL_2="${EMAIL_PREFIX_2}@example.com"
CNI_NUMBER_2=$(head /dev/urandom | tr -dc 0-9 | head -c 13)

REGISTER_RESPONSE_2=$(curl -s -X POST "$BASE_URL/register" \
  -H "Content-Type: application/json" \
  -d "{
    \"phone_number\": \"+223$PHONE_NUMBER_2\",
    \"first_name\": \"User\",
    \"last_name\": \"Two\",
    \"email\": \"$EMAIL_2\",
    \"password\": \"password123\",
    \"password_confirmation\": \"password123\",
    \"pin_code\": \"5678\",
    \"cni_number\": \"$CNI_NUMBER_2\"
  }")

echo "$REGISTER_RESPONSE_2" | jq . || echo "$REGISTER_RESPONSE_2"
echo ""

# 10. Transfer Money
echo -e "${YELLOW}10. Testing Transfer Endpoint${NC}"
curl -s -X POST "$BASE_URL/transactions/transfer" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $API_TOKEN" \
  -d "{
    \"receiver_phone\": \"+223$PHONE_NUMBER_2\",
    \"amount\": 5000,
    \"description\": \"Test transfer\"
  }" | jq . || echo "Failed: Transfer"
echo ""

# 11. Transaction History
echo -e "${YELLOW}11. Testing Transaction History Endpoint${NC}"
curl -s -X GET "$BASE_URL/transactions/history" \
  -H "Authorization: Bearer $API_TOKEN" | jq . || echo "Failed: History"
echo ""

# 12. Change PIN
echo -e "${YELLOW}12. Testing Change PIN Endpoint${NC}"
curl -s -X POST "$BASE_URL/auth/change-pin" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $API_TOKEN" \
  -d "{
    \"old_pin\": \"5678\",
    \"new_pin\": \"9999\"
  }" | jq . || echo "Failed: Change PIN"
echo ""

# 13. Refresh Token
echo -e "${YELLOW}13. Testing Refresh Token Endpoint${NC}"
curl -s -X POST "$BASE_URL/auth/refresh-token" \
  -H "Authorization: Bearer $API_TOKEN" | jq . || echo "Failed: Refresh Token"
echo ""

# 14. Logout
echo -e "${YELLOW}14. Testing Logout Endpoint${NC}"
curl -s -X POST "$BASE_URL/auth/logout" \
  -H "Authorization: Bearer $API_TOKEN" | jq . || echo "Failed: Logout"
echo ""

echo -e "${GREEN}====== TEST COMPLETED ======${NC}"
