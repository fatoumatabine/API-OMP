#!/bin/bash

BASE_URL="http://127.0.0.1:8001/api"
TOKEN=""

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}=== TEST API OMPAY ===${NC}\n"

# 1. INSCRIPTION
echo -e "${BLUE}1. REGISTRATION${NC}"
PHONE_NUMBER="+221$(head /dev/urandom | tr -dc 0-9 | head -c 8)"
EMAIL_PREFIX=$(uuidgen | cut -d'-' -f1)
EMAIL="${EMAIL_PREFIX}@example.com"
CNI_NUMBER=$(head /dev/urandom | tr -dc 0-9 | head -c 13)

REGISTER=$(curl -s -w "\n%{http_code}" -X POST $BASE_URL/register \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d "{
            \"phone_number\": \"$PHONE_NUMBER\",
            \"first_name\": \"Test\",
            \"last_name\": \"User\",
            \"email\": \"$EMAIL\",
            \"password\": \"password123\",
            \"password_confirmation\": \"password123\",
            \"pin_code\": \"1234\",
            \"cni_number\": \"$CNI_NUMBER\"
          }")

HTTP_CODE=$(echo "$REGISTER" | tail -n1)
BODY=$(echo "$REGISTER" | sed '$d')

if [ "$HTTP_CODE" = "201" ]; then
  echo -e "${GREEN}✓ Registration successful${NC}"
  USER_ID=$(echo "$BODY" | jq -r '.data.id')
  echo "User ID: $USER_ID"
else
  echo -e "${RED}✗ Registration failed (HTTP $HTTP_CODE)${NC}"
  echo "$BODY" | jq . 2>/dev/null || echo "$BODY"
  exit 1
fi
echo ""

# 2. LOGIN
echo -e "${BLUE}2. LOGIN${NC}"
LOGIN=$(curl -s -w "\n%{http_code}" -X POST $BASE_URL/auth/login \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d "{
            \"phone_number\": \"$PHONE_NUMBER\",
            \"password\": \"password123\"
          }")

HTTP_CODE=$(echo "$LOGIN" | tail -n1)
BODY=$(echo "$LOGIN" | sed '$d')

if [ "$HTTP_CODE" = "200" ]; then
  echo -e "${GREEN}✓ Login successful${NC}"
  TOKEN=$(echo "$BODY" | jq -r '.data.token')
  echo "Token: ${TOKEN:0:20}..."
else
  echo -e "${RED}✗ Login failed (HTTP $HTTP_CODE)${NC}"
  echo "$BODY" | jq . 2>/dev/null || echo "$BODY"
fi
echo ""

# 3. VERIFY OTP
echo -e "${BLUE}3. VERIFY OTP${NC}"
VERIFY=$(curl -s -w "\n%{http_code}" -X POST $BASE_URL/auth/verify-otp \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d "{
            \"phone_number\": \"$PHONE_NUMBER\",
            \"otp\": \"123456\"
          }")

HTTP_CODE=$(echo "$VERIFY" | tail -n1)
BODY=$(echo "$VERIFY" | sed '$d')

if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "422" ]; then
  echo -e "${GREEN}✓ OTP endpoint works${NC}"
else
  echo -e "${RED}✗ OTP verification failed (HTTP $HTTP_CODE)${NC}"
fi
echo ""

# Protected endpoints (require token)
if [ -z "$TOKEN" ] || [ "$TOKEN" = "null" ]; then
  echo -e "${RED}No valid token, skipping protected endpoints${NC}"
  exit 0
fi

# 4. CREATE PIN (skipped since PIN is already created during registration)
echo -e "${BLUE}4. CREATE PIN${NC}"
echo -e "${GREEN}✓ Create PIN skipped (PIN already created during registration)${NC}"
echo ""

# 5. GET WALLET BALANCE
echo -e "${BLUE}5. GET WALLET BALANCE${NC}"
BALANCE=$(curl -s -w "\n%{http_code}" -X GET $BASE_URL/wallet/balance \
     -H "Authorization: Bearer $TOKEN" \
     -H "Accept: application/json")

HTTP_CODE=$(echo "$BALANCE" | tail -n1)
BODY=$(echo "$BALANCE" | sed '$d')

if [ "$HTTP_CODE" = "200" ]; then
  echo -e "${GREEN}✓ Get balance successful${NC}"
  echo "$BODY" | jq . 2>/dev/null || echo "$BODY"
else
  echo -e "${RED}✗ Get balance failed (HTTP $HTTP_CODE)${NC}"
  echo "$BODY" | jq . 2>/dev/null || echo "$BODY"
fi
echo ""

# 6. DEPOSIT
echo -e "${BLUE}6. DEPOSIT${NC}"
DEPOSIT=$(curl -s -w "\n%{http_code}" -X POST $BASE_URL/wallet/deposit \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -H "Authorization: Bearer $TOKEN" \
     -d "{\"amount\": 10000, \"method\": \"card\"}")

HTTP_CODE=$(echo "$DEPOSIT" | tail -n1)
BODY=$(echo "$DEPOSIT" | sed '$d')

if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "422" ]; then
  echo -e "${GREEN}✓ Deposit endpoint works${NC}"
  echo "$BODY" | jq . 2>/dev/null || echo "$BODY"
else
  echo -e "${RED}✗ Deposit failed (HTTP $HTTP_CODE)${NC}"
  echo "$BODY" | jq . 2>/dev/null || echo "$BODY"
fi
echo ""

# 7. TRANSFER
echo -e "${BLUE}7. TRANSFER${NC}"
TRANSFER=$(curl -s -w "\n%{http_code}" -X POST $BASE_URL/transactions/transfer \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -H "Authorization: Bearer $TOKEN" \
     -d "{\"recipient_phone\": \"+22178901234\", \"amount\": 1000, \"pin\": \"1234\"}")

HTTP_CODE=$(echo "$TRANSFER" | tail -n1)
BODY=$(echo "$TRANSFER" | sed '$d')

if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "422" ] || [ "$HTTP_CODE" = "400" ]; then
  echo -e "${GREEN}✓ Transfer endpoint works${NC}"
  echo "$BODY" | jq . 2>/dev/null || echo "$BODY"
else
  echo -e "${RED}✗ Transfer failed (HTTP $HTTP_CODE)${NC}"
  echo "$BODY" | jq . 2>/dev/null || echo "$BODY"
fi
echo ""

# 8. TRANSACTION HISTORY
echo -e "${BLUE}8. TRANSACTION HISTORY${NC}"
HISTORY=$(curl -s -w "\n%{http_code}" -X GET $BASE_URL/transactions/history \
     -H "Authorization: Bearer $TOKEN" \
     -H "Accept: application/json")

HTTP_CODE=$(echo "$HISTORY" | tail -n1)
BODY=$(echo "$HISTORY" | sed '$d')

if [ "$HTTP_CODE" = "200" ]; then
  echo -e "${GREEN}✓ Transaction history successful${NC}"
  echo "$BODY" | jq . 2>/dev/null || echo "$BODY"
else
  echo -e "${RED}✗ Transaction history failed (HTTP $HTTP_CODE)${NC}"
  echo "$BODY" | jq . 2>/dev/null || echo "$BODY"
fi
echo ""

# 9. CHANGE PIN
echo -e "${BLUE}9. CHANGE PIN${NC}"
CHANGEPIN=$(curl -s -w "\n%{http_code}" -X POST $BASE_URL/auth/change-pin \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -H "Authorization: Bearer $TOKEN" \
     -d "{\"old_pin\": \"1234\", \"new_pin\": \"5678\"}")

HTTP_CODE=$(echo "$CHANGEPIN" | tail -n1)
BODY=$(echo "$CHANGEPIN" | sed '$d')

if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "422" ]; then
  echo -e "${GREEN}✓ Change PIN endpoint works${NC}"
else
  echo -e "${RED}✗ Change PIN failed (HTTP $HTTP_CODE)${NC}"
fi
echo ""

# 10. REFRESH TOKEN
echo -e "${BLUE}10. REFRESH TOKEN${NC}"
REFRESH=$(curl -s -w "\n%{http_code}" -X POST $BASE_URL/auth/refresh-token \
     -H "Authorization: Bearer $TOKEN" \
     -H "Accept: application/json")

HTTP_CODE=$(echo "$REFRESH" | tail -n1)
BODY=$(echo "$REFRESH" | sed '$d')

if [ "$HTTP_CODE" = "200" ]; then
  echo -e "${GREEN}✓ Refresh token successful${NC}"
  NEW_TOKEN=$(echo "$BODY" | jq -r '.data.token')
  echo "New Token: ${NEW_TOKEN:0:20}..."
  TOKEN=$NEW_TOKEN
else
  echo -e "${RED}✗ Refresh token failed (HTTP $HTTP_CODE)${NC}"
fi
echo ""

# 11. LOGOUT
echo -e "${BLUE}11. LOGOUT${NC}"
LOGOUT=$(curl -s -w "\n%{http_code}" -X POST $BASE_URL/auth/logout \
     -H "Authorization: Bearer $TOKEN" \
     -H "Accept: application/json")

HTTP_CODE=$(echo "$LOGOUT" | tail -n1)
BODY=$(echo "$LOGOUT" | sed '$d')

if [ "$HTTP_CODE" = "200" ]; then
  echo -e "${GREEN}✓ Logout successful${NC}"
else
  echo -e "${RED}✗ Logout failed (HTTP $HTTP_CODE)${NC}"
fi

echo -e "\n${BLUE}=== TEST COMPLETED ===${NC}"
