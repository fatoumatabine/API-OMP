<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"'); // Commenté temporairement

        // DB::statement('
        //     CREATE TABLE users (
        //         id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
        //         phone_number VARCHAR(255) UNIQUE NOT NULL,
        //         first_name VARCHAR(255) NOT NULL,
        //         last_name VARCHAR(255) NOT NULL,
        //         email VARCHAR(255) UNIQUE,
        //         email_verified_at TIMESTAMP NULL,
        //         password VARCHAR(255) NOT NULL,
        //         balance DECIMAL(15, 2) DEFAULT 0,
        //         status VARCHAR(255) DEFAULT \'active\',
        //         pin_code VARCHAR(255),
        //         cni_number VARCHAR(255) UNIQUE NOT NULL,
        //         kyc_status VARCHAR(255) DEFAULT \'pending\',
        //         biometrics_active BOOLEAN DEFAULT true,
        //         last_login_at TIMESTAMP NULL,
        //         otp_code VARCHAR(255),
        //         otp_expires_at TIMESTAMP NULL,
        //         is_verified BOOLEAN DEFAULT false,
        //         remember_token VARCHAR(100),
        //         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        //     )
        // ');

        // DB::statement('
        //     CREATE TABLE wallets (
        //         id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
        //         user_id uuid NOT NULL,
        //         currency VARCHAR(255) DEFAULT \'XOF\',
        //         balance DECIMAL(15, 2) DEFAULT 0,
        //         status VARCHAR(255) DEFAULT \'active\',
        //         account_number VARCHAR(255) UNIQUE,
        //         qr_code TEXT,
        //         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        //     )
        // ');

        // DB::statement('
        //     CREATE TABLE transactions (
        //         id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
        //         sender_id uuid,
        //         receiver_id uuid,
        //         sender_wallet_id uuid,
        //         receiver_wallet_id uuid,
        //         amount DECIMAL(15, 2) NOT NULL,
        //         fees DECIMAL(8, 2) DEFAULT 0,
        //         type VARCHAR(255) NOT NULL,
        //         status VARCHAR(255) DEFAULT \'pending\',
        //         reference VARCHAR(255) UNIQUE NOT NULL,
        //         description TEXT,
        //         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL,
        //         FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE SET NULL,
        //         FOREIGN KEY (sender_wallet_id) REFERENCES wallets(id) ON DELETE SET NULL,
        //         FOREIGN KEY (receiver_wallet_id) REFERENCES wallets(id) ON DELETE SET NULL
        //     )
        // ');

        // DB::statement('
        //     CREATE TABLE sessions (
        //         id VARCHAR(255) PRIMARY KEY,
        //         user_id uuid,
        //         ip_address VARCHAR(45),
        //         user_agent TEXT,
        //         payload LONGTEXT NOT NULL,
        //         last_activity INTEGER NOT NULL,
        //         FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        //     )
        // ');

        // DB::statement('
        //     CREATE TABLE cache (
        //         key VARCHAR(255) PRIMARY KEY,
        //         value MEDIUMTEXT NOT NULL,
        //         expiration INTEGER NOT NULL
        //     )
        // ');

        // DB::statement('
        //     CREATE TABLE cache_locks (
        //         key VARCHAR(255) PRIMARY KEY,
        //         owner VARCHAR(255) NOT NULL,
        //         expiration INTEGER NOT NULL
        //     )
        // ');

        // DB::statement('
        //     CREATE TABLE password_reset_tokens (
        //         email VARCHAR(255) PRIMARY KEY,
        //         token VARCHAR(255) NOT NULL,
        //         created_at TIMESTAMP NULL
        //     )
        // ');

        // DB::statement('
        //     CREATE TABLE jobs (
        //         id BIGSERIAL PRIMARY KEY,
        //         queue VARCHAR(255) NOT NULL,
        //         payload LONGTEXT NOT NULL,
        //         attempts SMALLINT DEFAULT 0,
        //         reserved_at INTEGER,
        //         available_at INTEGER NOT NULL,
        //         created_at INTEGER NOT NULL
        //     )
        // ');

        // DB::statement('CREATE INDEX jobs_queue_idx ON jobs (queue)');
        // DB::statement('CREATE INDEX jobs_queue_reserved_idx ON jobs (queue, reserved_at)');

        // DB::statement('
        //     CREATE TABLE job_batches (
        //         id VARCHAR(255) PRIMARY KEY,
        //         name VARCHAR(255) NOT NULL,
        //         total_jobs INTEGER NOT NULL,
        //         pending_jobs INTEGER NOT NULL,
        //         failed_jobs INTEGER NOT NULL,
        //         failed_job_ids LONGTEXT NOT NULL,
        //         options TEXT,
        //         created_at TIMESTAMP NOT NULL,
        //         finished_at TIMESTAMP NULL
        //     )
        // ');

        // DB::statement('
        //     CREATE TABLE failed_jobs (
        //         id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
        //         connection VARCHAR(255) NOT NULL,
        //         queue VARCHAR(255) NOT NULL,
        //         payload LONGTEXT NOT NULL,
        //         exception LONGTEXT NOT NULL,
        //         failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        //     )
        // ');

        // DB::statement('
        //     CREATE TABLE authentications (
        //         id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
        //         user_id uuid NOT NULL,
        //         method VARCHAR(255),
        //         last_ip VARCHAR(255),
        //         last_login TIMESTAMP,
        //         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        //     )
        // ');

        // DB::statement('
        //     CREATE TABLE security_settings (
        //         id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
        //         user_id uuid NOT NULL,
        //         two_factor_enabled BOOLEAN DEFAULT false,
        //         two_factor_method VARCHAR(255),
        //         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        //     )
        // ');

        // DB::statement('
        //     CREATE TABLE contacts (
        //         id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
        //         user_id uuid NOT NULL,
        //         name VARCHAR(255) NOT NULL,
        //         phone_number VARCHAR(255) UNIQUE NOT NULL,
        //         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        //     )
        // ');

        // DB::statement('
        //     CREATE TABLE histories (
        //         id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
        //         user_id uuid NOT NULL,
        //         action VARCHAR(255) NOT NULL,
        //         details TEXT,
        //         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        //     )
        // ');

        // DB::statement('
        //     CREATE TABLE transfers (
        //         id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
        //         transaction_id uuid NOT NULL,
        //         sender_id uuid NOT NULL,
        //         receiver_id uuid NOT NULL,
        //         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
        //         FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
        //     )
        // ');

        // DB::statement('
        //     CREATE TABLE merchants (
        //         id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
        //         name VARCHAR(255) NOT NULL,
        //         phone_number VARCHAR(255) UNIQUE NOT NULL,
        //         address VARCHAR(255),
        //         logo VARCHAR(255),
        //         is_active BOOLEAN DEFAULT true,
        //         accepts_qr BOOLEAN DEFAULT true,
        //         accepts_code BOOLEAN DEFAULT true,
        //         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        //     )
        // ');

        // DB::statement('
        //     CREATE TABLE qr_codes (
        //         id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
        //         merchant_id uuid NOT NULL,
        //         data VARCHAR(255) NOT NULL,
        //         amount DECIMAL(15, 2) NOT NULL,
        //         generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         expires_at TIMESTAMP NOT NULL,
        //         is_used BOOLEAN DEFAULT false,
        //         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE
        //     )
        // ');

        // DB::statement('
        //     CREATE TABLE payment_codes (
        //         id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
        //         merchant_id uuid NOT NULL,
        //         code VARCHAR(255) UNIQUE NOT NULL,
        //         amount DECIMAL(15, 2) NOT NULL,
        //         generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         expires_at TIMESTAMP NOT NULL,
        //         is_used BOOLEAN DEFAULT false,
        //         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE
        //     )
        // ');

        // DB::statement('
        //     CREATE TABLE payments (
        //         id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
        //         transaction_id uuid NOT NULL,
        //         merchant_id uuid NOT NULL,
        //         payment_method VARCHAR(255) NOT NULL,
        //         transaction_code VARCHAR(255),
        //         payment_details JSON,
        //         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
        //         FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE
        //     )
        // ');
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS payments');
        DB::statement('DROP TABLE IF EXISTS payment_codes');
        DB::statement('DROP TABLE IF EXISTS qr_codes');
        DB::statement('DROP TABLE IF EXISTS merchants');
        DB::statement('DROP TABLE IF EXISTS transfers');
        DB::statement('DROP TABLE IF EXISTS histories');
        DB::statement('DROP TABLE IF EXISTS contacts');
        DB::statement('DROP TABLE IF EXISTS security_settings');
        DB::statement('DROP TABLE IF EXISTS authentications');
        DB::statement('DROP TABLE IF EXISTS failed_jobs');
        DB::statement('DROP TABLE IF EXISTS job_batches');
        DB::statement('DROP TABLE IF EXISTS jobs');
        DB::statement('DROP TABLE IF EXISTS password_reset_tokens');
        DB::statement('DROP TABLE IF EXISTS cache_locks');
        DB::statement('DROP TABLE IF EXISTS cache');
        DB::statement('DROP TABLE IF EXISTS sessions');
        DB::statement('DROP TABLE IF EXISTS transactions');
        DB::statement('DROP TABLE IF EXISTS wallets');
        DB::statement('DROP TABLE IF EXISTS users');
    }
};
