#!/bin/bash

PHONE_NUMBER="+221$(head /dev/urandom | tr -dc 0-9 | head -c 8)"
EMAIL_PREFIX=$(uuidgen | cut -d'-' -f1)
EMAIL="${EMAIL_PREFIX}@example.com"
CNI_NUMBER=$(head /dev/urandom | tr -dc 0-9 | head -c 13)

echo "Testing registration with:"
echo "Phone Number: $PHONE_NUMBER"
echo "Email: $EMAIL"
echo "CNI Number: $CNI_NUMBER"

RESPONSE=$(curl -s -w "\n%{http_code}" -X POST http://127.0.0.1:8001/api/register \
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

HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
BODY=$(echo "$RESPONSE" | sed '$d')

echo ""
echo "HTTP Status: $HTTP_CODE"
echo "Response Body:"
echo "$BODY" | jq . 2>/dev/null || echo "$BODY"

if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "201" ]; then
  echo "✓ Registration successful"
else
  echo "✗ Registration failed"
fi
