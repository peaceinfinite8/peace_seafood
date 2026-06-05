-- Migration: Email Queue System
-- Date: 2026-06-05
-- Phase 2 Fix #1: Asynchronous email processing

CREATE TABLE IF NOT EXISTS email_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    to_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('pending', 'processing', 'sent', 'failed') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    error_message TEXT NULL,
    priority INT DEFAULT 5 COMMENT '1=highest, 10=lowest',
    scheduled_at DATETIME NULL COMMENT 'For delayed sending',
    processing_at DATETIME NULL,
    sent_at DATETIME NULL,
    failed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_priority_created (priority ASC, created_at ASC),
    INDEX idx_scheduled (scheduled_at),
    INDEX idx_attempts (attempts)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
