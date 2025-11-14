#!/bin/bash

API_URL="http://127.0.0.1:8000/api"

echo "========================================="
echo "TEST DES NOUVELLES ROUTES"
echo "========================================="

# 1. Enregistrement
echo -e "\n1️⃣  Enregistrement..."
REGISTER_RESPONSE=$(curl -s -X POST "$API_URL/register" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "+22145678999",
    "first_name": "Test",
    "last_name": "User",
    "email": "test999@example.com",
    "pin_code": "1234",
    "cni_number": "1234567890ABC"
  }')
echo "$REGISTER_RESPONSE" | jq '.'
USER_ID=$(echo "$REGISTER_RESPONSE" | jq -r '.data.id')
echo "User ID: $USER_ID"

# 2. Vérification OTP (utiliser le code d'essai)
echo -e "\n2️⃣  Vérification OTP..."
VERIFY_RESPONSE=$(curl -s -X POST "$API_URL/verify-otp" \
  -H "Content-Type: application/json" \
  -d "{
    \"phone_number\": \"+22145678999\",
    \"otp_code\": \"000000\",
    \"password\": \"password123\",
    \"password_confirmation\": \"password123\"
  }")
echo "$VERIFY_RESPONSE" | jq '.'

# 3. Login
echo -e "\n3️⃣  Connexion..."
LOGIN_RESPONSE=$(curl -s -X POST "$API_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "+22145678999",
    "password": "password123"
  }')
echo "$LOGIN_RESPONSE" | jq '.'
TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.token')
echo "Token: $TOKEN"

# 4. Récupérer l'ID du wallet du dashboard
echo -e "\n4️⃣  Récupération du dashboard..."
DASHBOARD=$(curl -s -X GET "$API_URL/compte/dashboard" \
  -H "Authorization: Bearer $TOKEN")
echo "$DASHBOARD" | jq '.'
WALLET_ID=$(echo "$DASHBOARD" | jq -r '.data.compte.id')
echo "Wallet ID: $WALLET_ID"

# 5. Test GET /comptes/{id}/solde
echo -e "\n5️⃣  TEST: GET /comptes/{id}/solde"
SOLDE_RESPONSE=$(curl -s -X GET "$API_URL/comptes/$WALLET_ID/solde" \
  -H "Authorization: Bearer $TOKEN")
echo "$SOLDE_RESPONSE" | jq '.'

# 6. Test POST /compte/{id}/depot
echo -e "\n6️⃣  TEST: POST /compte/{id}/depot"
DEPOT_RESPONSE=$(curl -s -X POST "$API_URL/compte/$WALLET_ID/depot" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 5000,
    "description": "Test dépôt"
  }')
echo "$DEPOT_RESPONSE" | jq '.'

# 7. Vérifier le nouveau solde
echo -e "\n7️⃣  Vérification du nouveau solde..."
SOLDE_FINAL=$(curl -s -X GET "$API_URL/comptes/$WALLET_ID/solde" \
  -H "Authorization: Bearer $TOKEN")
echo "$SOLDE_FINAL" | jq '.'

echo -e "\n========================================="
echo "✅ TEST TERMINÉ"
echo "========================================="
