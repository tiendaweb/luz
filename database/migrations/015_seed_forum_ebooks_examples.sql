PRAGMA foreign_keys = ON;

INSERT INTO ebooks (title, description, status, provider, local_path, external_url, min_attendance, requires_approved, updated_at)
SELECT 'Guía práctica del Foro de la mañana',
       'Material de apoyo exclusivo para participantes del foro morning.',
       'published',
       'local',
       'guia-practica-foro-manana.pdf',
       NULL,
       75,
       1,
       CURRENT_TIMESTAMP
WHERE NOT EXISTS (
  SELECT 1 FROM ebooks WHERE title = 'Guía práctica del Foro de la mañana'
);

INSERT INTO ebooks (title, description, status, provider, local_path, external_url, min_attendance, requires_approved, updated_at)
SELECT 'Workbook del Foro de la tarde',
       'Workbook descargable para los asistentes del foro afternoon.',
       'published',
       'local',
       'workbook-foro-tarde.pdf',
       NULL,
       70,
       1,
       CURRENT_TIMESTAMP
WHERE NOT EXISTS (
  SELECT 1 FROM ebooks WHERE title = 'Workbook del Foro de la tarde'
);

DELETE FROM forum_ebooks
WHERE ebook_id IN (
  SELECT id FROM ebooks
  WHERE title IN ('Guía práctica del Foro de la mañana', 'Workbook del Foro de la tarde')
);

INSERT OR IGNORE INTO forum_ebooks (forum_id, ebook_id, is_active, updated_at)
SELECT forums.id, ebooks.id, 1, CURRENT_TIMESTAMP
FROM forums
INNER JOIN ebooks ON ebooks.title = 'Guía práctica del Foro de la mañana'
WHERE forums.code = 'morning';

INSERT OR IGNORE INTO forum_ebooks (forum_id, ebook_id, is_active, updated_at)
SELECT forums.id, ebooks.id, 1, CURRENT_TIMESTAMP
FROM forums
INNER JOIN ebooks ON ebooks.title = 'Workbook del Foro de la tarde'
WHERE forums.code = 'afternoon';
