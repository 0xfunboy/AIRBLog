-- AIR Agent Blog schema
SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    display_name VARCHAR(100) NOT NULL,
    wallet_address CHAR(42) NOT NULL UNIQUE,
    email VARCHAR(150) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NOT NULL,
    session_token CHAR(64) NOT NULL UNIQUE,
    user_agent VARCHAR(255) NULL,
    ip_address VARBINARY(16) NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_nonces (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nonce CHAR(64) NOT NULL UNIQUE,
    admin_id INT UNSIGNED NULL,
    expires_at DATETIME NOT NULL,
    consumed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_address VARCHAR(64) NOT NULL,
    model VARCHAR(64) NOT NULL,
    field_key VARCHAR(80) NOT NULL,
    id_ref VARCHAR(150) NULL,
    old_value TEXT NULL,
    new_value TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS agents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    chain VARCHAR(80) NOT NULL,
    status ENUM('Live','In Development') NOT NULL DEFAULT 'Live',
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    summary TEXT NULL,
    site_url VARCHAR(255) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    badge VARCHAR(120) NULL,
    featured_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS agent_post_types (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(32) NOT NULL UNIQUE,
    label VARCHAR(60) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS agent_api_keys (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    agent_id INT UNSIGNED NOT NULL,
    key_hash CHAR(64) NOT NULL UNIQUE,
    plain_token VARCHAR(128) NULL,
    label VARCHAR(120) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE,
    INDEX idx_agent_api_keys_agent (agent_id),
    INDEX idx_agent_api_keys_active (agent_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS agent_posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    agent_id INT UNSIGNED NOT NULL,
    post_type_id TINYINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt_280 VARCHAR(280) NULL,
    body_html LONGTEXT NOT NULL,
    image_url VARCHAR(255) NULL,
    tags TEXT NULL,
    ticker VARCHAR(20) NULL,
    chain VARCHAR(40) NULL,
    timeframe VARCHAR(20) NULL,
    entry_price DECIMAL(18,8) NULL,
    stop_price DECIMAL(18,8) NULL,
    target_prices JSON NULL,
    confidence TINYINT UNSIGNED NULL,
    price_at_post DECIMAL(18,8) NULL,
    publish_mode ENUM('auto','needs_approval') NOT NULL DEFAULT 'auto',
    status ENUM('pending','approved','published','rejected') NOT NULL DEFAULT 'pending',
    approved_by_admin_id INT UNSIGNED NULL,
    published_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE,
    FOREIGN KEY (post_type_id) REFERENCES agent_post_types(id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by_admin_id) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_agent_posts_agent (agent_id),
    INDEX idx_agent_posts_type (post_type_id),
    INDEX idx_agent_posts_status (status),
    INDEX idx_agent_posts_published_at (published_at),
    INDEX idx_agent_posts_ticker (ticker)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
