<?php
require 'vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');

    DB::statement('
        CREATE TABLE IF NOT EXISTS users (
            id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
            phone_number VARCHAR(255) UNIQUE NOT NULL,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE,
            email_verified_at TIMESTAMP NULL,
            password VARCHAR(255) NOT NULL,
            balance DECIMAL(15, 2) DEFAULT 0,
            status VARCHAR(255) DEFAULT \'active\',
            pin_code VARCHAR(255),
            cni_number VARCHAR(255) UNIQUE NOT NULL,
            kyc_status VARCHAR(255) DEFAULT \'pending\',
            biometrics_active BOOLEAN DEFAULT true,
            last_login_at TIMESTAMP NULL,
            otp_code VARCHAR(255),
            otp_expires_at TIMESTAMP NULL,
            is_verified BOOLEAN DEFAULT false,
            remember_token VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ');

    DB::statement('
        CREATE TABLE IF NOT EXISTS wallets (
            id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
            user_id uuid NOT NULL,
            currency VARCHAR(255) DEFAULT \'XOF\',
            balance DECIMAL(15, 2) DEFAULT 0,
            status VARCHAR(255) DEFAULT \'active\',
            account_number VARCHAR(255) UNIQUE,
            qr_code TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ');

    DB::statement('
        CREATE TABLE IF NOT EXISTS transactions (
            id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
            sender_id uuid,
            receiver_id uuid,
            sender_wallet_id uuid,
            receiver_wallet_id uuid,
            amount DECIMAL(15, 2) NOT NULL,
            fees DECIMAL(8, 2) DEFAULT 0,
            type VARCHAR(255) NOT NULL,
            status VARCHAR(255) DEFAULT \'pending\',
            reference VARCHAR(255) UNIQUE NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (sender_wallet_id) REFERENCES wallets(id) ON DELETE SET NULL,
            FOREIGN KEY (receiver_wallet_id) REFERENCES wallets(id) ON DELETE SET NULL
        )
    ');

    echo "✅ Toutes les tables créées!\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
