CREATE TABLE IF NOT EXISTS message_threads (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(255) NOT NULL,
    created_by_user_id INT(10) UNSIGNED NULL,
    last_message_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_message_threads_last_message_at (last_message_at),
    CONSTRAINT fk_message_threads_created_by_user
        FOREIGN KEY (created_by_user_id) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS messages (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    thread_id INT(10) UNSIGNED NOT NULL,
    sender_user_id INT(10) UNSIGNED NOT NULL,
    parent_message_id INT(10) UNSIGNED NULL,
    subject VARCHAR(255) NOT NULL,
    body MEDIUMTEXT NOT NULL,
    is_draft TINYINT(1) NOT NULL DEFAULT 0,
    sender_deleted_at DATETIME NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_messages_thread_id (thread_id),
    KEY idx_messages_sender_user_id (sender_user_id),
    KEY idx_messages_parent_message_id (parent_message_id),
    KEY idx_messages_is_draft (is_draft),
    CONSTRAINT fk_messages_thread
        FOREIGN KEY (thread_id) REFERENCES message_threads(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_messages_sender_user
        FOREIGN KEY (sender_user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_messages_parent_message
        FOREIGN KEY (parent_message_id) REFERENCES messages(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS message_recipients (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    message_id INT(10) UNSIGNED NOT NULL,
    recipient_user_id INT(10) UNSIGNED NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    read_at DATETIME NULL DEFAULT NULL,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    deleted_at DATETIME NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_message_recipient (message_id, recipient_user_id),
    KEY idx_message_recipients_recipient_user_id (recipient_user_id),
    KEY idx_message_recipients_is_read (is_read),
    CONSTRAINT fk_message_recipients_message
        FOREIGN KEY (message_id) REFERENCES messages(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_message_recipients_user
        FOREIGN KEY (recipient_user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS message_attachments (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    message_id INT(10) UNSIGNED NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(120) NOT NULL,
    size_bytes BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_message_attachments_message_id (message_id),
    CONSTRAINT fk_message_attachments_message
        FOREIGN KEY (message_id) REFERENCES messages(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
