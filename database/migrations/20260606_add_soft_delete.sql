-- ============================================================================
-- Migration: Add Soft Delete Support
-- Date: 2026-06-06
-- Description: Add deleted_at column to critical tables for audit trail
-- ============================================================================

-- Add soft delete to payment_requests
ALTER TABLE payment_requests 
ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL
AFTER updated_at;

CREATE INDEX idx_payment_requests_deleted_at ON payment_requests(deleted_at);

-- Add soft delete to notifikasi
ALTER TABLE notifikasi 
ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL
AFTER created_at;

CREATE INDEX idx_notifikasi_deleted_at ON notifikasi(deleted_at);

-- Add soft delete to activity_log (for soft cleanup)
ALTER TABLE activity_log 
ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL
AFTER created_at;

CREATE INDEX idx_activity_log_deleted_at ON activity_log(deleted_at);

-- Add soft delete to grace_email_log
ALTER TABLE grace_email_log 
ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL
AFTER sent_at;

CREATE INDEX idx_grace_email_log_deleted_at ON grace_email_log(deleted_at);

-- Add soft delete to webhook_log
ALTER TABLE webhook_log 
ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL
AFTER created_at;

CREATE INDEX idx_webhook_log_deleted_at ON webhook_log(deleted_at);

-- Add soft delete to password_resets
ALTER TABLE password_resets 
ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL
AFTER used_at;

CREATE INDEX idx_password_resets_deleted_at ON password_resets(deleted_at);

-- Add soft delete to email_queue
ALTER TABLE email_queue 
ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL
AFTER sent_at;

CREATE INDEX idx_email_queue_deleted_at ON email_queue(deleted_at);

-- ============================================================================
-- NOTES:
-- - Soft delete preserves audit trail
-- - Use WHERE deleted_at IS NULL in queries to exclude deleted records
-- - Cleanup old soft-deleted records periodically (e.g., after 90 days)
-- ============================================================================
