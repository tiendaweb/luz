CREATE TABLE IF NOT EXISTS content_blocks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    block_key TEXT NOT NULL,
    context TEXT NOT NULL,
    locale TEXT NOT NULL DEFAULT 'es',
    content_type TEXT NOT NULL DEFAULT 'text',
    value TEXT NOT NULL,
    version INTEGER NOT NULL DEFAULT 1,
    updated_by_user_id INTEGER,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(block_key, context, locale)
);

CREATE TABLE IF NOT EXISTS content_block_versions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    content_block_id INTEGER NOT NULL,
    block_key TEXT NOT NULL,
    context TEXT NOT NULL,
    locale TEXT NOT NULL,
    content_type TEXT NOT NULL DEFAULT 'text',
    value TEXT NOT NULL,
    version INTEGER NOT NULL,
    changed_by_user_id INTEGER,
    changed_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (content_block_id) REFERENCES content_blocks(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_content_blocks_context_locale ON content_blocks(context, locale);
CREATE INDEX IF NOT EXISTS idx_content_block_versions_block ON content_block_versions(content_block_id, version DESC);
