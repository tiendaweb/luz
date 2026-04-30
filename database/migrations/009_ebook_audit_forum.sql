ALTER TABLE ebook_download_audit ADD COLUMN forum_id INTEGER REFERENCES forums(id);
CREATE INDEX IF NOT EXISTS idx_ebook_download_audit_forum_id ON ebook_download_audit(forum_id);
