CREATE TABLE documents (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 storage_key VARCHAR(100) NOT NULL UNIQUE,
 name VARCHAR(180) NOT NULL,
 mime_type VARCHAR(100) NOT NULL,
 size_bytes BIGINT UNSIGNED NOT NULL,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL,
 INDEX idx_documents_updated (updated_at),
 INDEX idx_documents_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE document_tags (
 document_id BIGINT UNSIGNED NOT NULL,
 tag VARCHAR(40) NOT NULL,
 PRIMARY KEY(document_id,tag),
 INDEX idx_document_tags_tag(tag),
 CONSTRAINT fk_document_tags_document FOREIGN KEY(document_id) REFERENCES documents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
