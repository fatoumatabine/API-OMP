#!/bin/bash

API_URL="http://127.0.0.1:8000/api"

echo "========================================="
echo "TEST DES NOUVEAUX ENDPOINTS"
echo "========================================="

# 1. Enregistrement User 1
echo -e "\n1️⃣  Enregistrement User 1..."
REG1=$(curl -s -X POST "$API_URL/register" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "+22145670001",
    "first_name": "Alice",
    "last_name": "Sender",
    "email": "alice@example.com",
    "pin_code": "1234",
    "cni_number": "CNI0001"
  }')
echo "$REG1" | jq '.'

# 2. Enregistrement User 2
echo -e "\n2️⃣  Enregistrement User 2..."
REG2=$(curl -s -X POST "$API_URL/register" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "+22145670002",
    "first_name": "Bob",
    "last_name": "Receiver",
    "email": "bob@example.com",
    "pin_code": "1234",
    "cni_number": "CNI0002"
  }')
echo "$REG2" | jq '.'

# 3. Verify OTP User 1
echo -e "\n3️⃣  Vérification OTP User 1..."
VERIFY1=$(curl -s -X POST "$API_URL/verify-otp" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "+22145670001",
    "otp_code": "000000",
    "password": "password123",
    "password_confirmation": "password123"
  }')
echo "$VERIFY1" | jq '.'

# 4. Verify OTP User 2
echo -e "\n4️⃣  Vérification OTP User 2..."
VERIFY2=$(curl -s -X POST "$API_URL/verify-otp" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "+22145670002",
    "otp_code": "000000",
    "password": "password123",
    "password_confirmation": "password123"
  }')
echo "$VERIFY2" | jq '.'

# 5. Login User 1
echo -e "\n5️⃣  Login User 1..."
LOGIN1=$(curl -s -X POST "$API_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "+22145670001",
    "password": "password123"
  }')
echo "$LOGIN1" | jq '.'
TOKEN1=$(echo "$LOGIN1" | jq -r '.data.token')
echo "Token User 1: $TOKEN1"

# 6. Login User 2
echo -e "\n6️⃣  Login User 2..."
LOGIN2=$(curl -s -X POST "$API_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "+22145670002",
    "password": "password123"
  }')
echo "$LOGIN2" | jq '.'
TOKEN2=$(echo "$LOGIN2" | jq -r '.data.token')
echo "Token User 2: $TOKEN2"

# 7. Get Dashboard User 1
echo -e "\n7️⃣  Dashboard User 1 (pour récupérer WALLET_ID)..."
DASHBOARD1=$(curl -s -X GET "$API_URL/compte/dashboard" \
  -H "Authorization: Bearer $TOKEN1")
echo "$DASHBOARD1" | jq '.'
WALLET_ID1=$(echo "$DASHBOARD1" | jq -r '.data.compte.id')
echo "WALLET_ID User 1: $WALLET_ID1"

# 8. Get Dashboard User 2
echo -e "\n8️⃣  Dashboard User 2..."
DASHBOARD2=$(curl -s -X GET "$API_URL/compte/dashboard" \
  -H "Authorization: Bearer $TOKEN2")
echo "$DASHBOARD2" | jq '.'
WALLET_ID2=$(echo "$DASHBOARD2" | jq -r '.data.compte.id')
echo "WALLET_ID User 2: $WALLET_ID2"

# 9. Depot User 1 (ajouter de l'argent)
echo -e "\n9️⃣  Dépôt 10000 CFA sur User 1..."
DEPOT=$(curl -s -X POST "$API_URL/compte/$WALLET_ID1/depot" \
  -H "Authorization: Bearer $TOKEN1" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 10000,
    "description": "Dépôt initial"
  }')
echo "$DEPOT" | jq '.'

# 10. Solde User 1
echo -e "\n🔟 Solde User 1 après dépôt..."
SOLDE1=$(curl -s -X GET "$API_URL/comptes/$WALLET_ID1/solde" \
  -H "Authorization: Bearer $TOKEN1")
echo "$SOLDE1" | jq '.'

# 11. Test TRANSFERT (User 1 → User 2)
echo -e "\n1️⃣1️⃣  TRANSFERT: User 1 → User 2 (5000 CFA)..."
TRANSFER=$(curl -s -X POST "$API_URL/transactions/transfer" \
  -H "Authorization: Bearer $TOKEN1" \
  -H "Content-Type: application/json" \
  -d '{
    "receiver_phone": "+22145670002",
    "amount": 5000,
    "description": "Transfert de test"
  }')
echo "$TRANSFER" | jq '.'

# 12. Test GET TRANSACTIONS (User 1)
echo -e "\n1️⃣2️⃣  GET /compte/{id}/transactions - Historique User 1..."
TRANS1=$(curl -s -X GET "$API_URL/compte/$WALLET_ID1/transactions" \
  -H "Authorization: Bearer $TOKEN1")
echo "$TRANS1" | jq '.'

# 13. Test PAYMENT (User 1 → Marchand)
echo -e "\n1️⃣3️⃣  POST /compte/{id}/payment - Paiement marchand User 1 (2000 CFA)..."
PAYMENT=$(curl -s -X POST "$API_URL/compte/$WALLET_ID1/payment" \
  -H "Authorization: Bearer $TOKEN1" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 2000,
    "merchant_identifier": "MERCH001",
    "description": "Achat carburant"
  }')
echo "$PAYMENT" | jq '.'

# 14. Vérifier les transactions User 2
echo -e "\n1️⃣4️⃣  GET /compte/{id}/transactions - Historique User 2..."
TRANS2=$(curl -s -X GET "$API_URL/compte/$WALLET_ID2/transactions" \
  -H "Authorization: Bearer $TOKEN2")
echo "$TRANS2" | jq '.'

# 15. Soldes finaux
echo -e "\n1️⃣5️⃣  Solde final User 1..."
curl -s -X GET "$API_URL/comptes/$WALLET_ID1/solde" \
  -H "Authorization: Bearer $TOKEN1" | jq '.'

echo -e "\n1️⃣6️⃣  Solde final User 2..."
curl -s -X GET "$API_URL/comptes/$WALLET_ID2/solde" \
  -H "Authorization: Bearer $TOKEN2" | jq '.'

echo -e "\n========================================="
echo "✅ TESTS TERMINÉS"
echo "========================================="
