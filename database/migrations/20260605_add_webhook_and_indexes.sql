-- Migration: Add webhook log table, indexes, and missing columns
-- Date: 2026-06-05
-- Phase 1 Fixes: Issue #3 (indexes) and Issue #2 (webhook table)

-- 1. Add index on magic_token for fast lookup (Issue #3)
ALTER TABLE payment_requests 
ADD INDEX idx_magic_token (magic_token);

-- 2. Add index on order_id for idempotency check (Issue #2)
ALTER TABLE payment_requests 
ADD INDEX idx_order_id (order_id);

-- 3. Add order_id column if not exists
ALTER TABLE payment_requests 
ADD COLUMN IF NOT EXISTS order_id VARCHAR(100) NULL AFTER id,
ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NULL AFTER nominal,
ADD COLUMN IF NOT EXISTS webhook_payload TEXT NULL AFTER magic_token_used_at;

-- 4. Create webhook_log table for audit trail
CREATE TABLE IF NOT EXISTS webhook_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(100) NOT NULL,
    id_gudang INT NULL,
    status VARCHAR(50) NOT NULL COMMENT 'processed, error, pending',
    payload TEXT NOT NULL COMMENT 'Full webhook payload JSON',
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_id (order_id),
    INDEX idx_id_gudang (id_gudang),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Add indexes on frequently queried columns
ALTER TABLE activity_log 
ADD INDEX IF NOT EXISTS idx_id_gudang (id_gudang),
ADD INDEX IF NOT EXISTS idx_action (action),
ADD INDEX IF NOT EXISTS idx_created_at (created_at);

ALTER TABLE notifikasi 
ADD INDEX IF NOT EXISTS idx_id_user_unread (id_user, is_read),
ADD INDEX IF NOT EXISTS idx_created_at (created_at);

ALTER TABLE grace_email_log 
ADD INDEX IF NOT EXISTS idx_id_gudang_trigger (id_gudang, trigger),
ADD INDEX IF NOT EXISTS idx_sent_at (sent_at);

-- 6. Add composite index for common query patterns
ALTER TABLE payment_requests 
ADD INDEX IF NOT EXISTS idx_gudang_status (id_gudang, status),
ADD INDEX IF NOT EXISTS idx_status_created (status, created_at DESC);

-- 7. Add index on subscription_until for grace period calculations
ALTER TABLE gudang 
ADD INDEX IF NOT EXISTS idx_subscription_active (subscription_until, is_active);

COMMIT;

-- Verification queries (optional - comment out in production)
-- SHOW INDEX FROM payment_requests;
-- SHOW INDEX FROM webhook_log;
-- SHOW INDEX FROM activity_log;
