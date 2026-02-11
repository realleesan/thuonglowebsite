-- Migration: Create migrations table to track database versions
-- Created: 2026-02-09
-- Description: This table tracks which migrations have been run

CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL UNIQUE,
    batch INT NOT NULL DEFAULT 1,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert this migration record
INSERT IGNORE INTO migrations (migration, batch) VALUES ('000_create_migrations_table', 1);