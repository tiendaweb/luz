-- Insertar foros para Mayo 2026 con múltiples horarios y regiones

INSERT OR IGNORE INTO forums (code, title, description, platform_type, timezone, status, starts_at, objective, modality, seats_total, seats_available, cta_label)
VALUES
-- PROFESIONALES - Colombia (Sábados 15:00)
('PROF-COL-SAB-0509', 'PROFESIONALES: Sábado 9 Mayo 15:00 AR-Colombia', 'Foro profesionales - Ciclo mayo 2026', 'zoom', 'America/Bogota', 'published', '2026-05-09 15:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),
('PROF-COL-SAB-1605', 'PROFESIONALES: Sábado 16 Mayo 15:00 AR-Colombia', 'Foro profesionales - Ciclo mayo 2026', 'zoom', 'America/Bogota', 'published', '2026-05-16 15:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),
('PROF-COL-SAB-2305', 'PROFESIONALES: Sábado 23 Mayo 15:00 AR-Colombia', 'Foro profesionales - Ciclo mayo 2026', 'zoom', 'America/Bogota', 'published', '2026-05-23 15:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),
('PROF-COL-SAB-3005', 'PROFESIONALES: Sábado 30 Mayo 15:00 AR-Colombia', 'Foro profesionales - Ciclo mayo 2026', 'zoom', 'America/Bogota', 'published', '2026-05-30 15:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),

-- PROFESIONALES - Ecuador/Bolivia (Lunes 21:00)
('PROF-ECU-LUN-0405', 'PROFESIONALES: Lunes 4 Mayo 21:00 ECU-Bolivia', 'Foro profesionales - Ciclo mayo 2026', 'zoom', 'America/La_Paz', 'published', '2026-05-04 21:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),
('PROF-ECU-LUN-1105', 'PROFESIONALES: Lunes 11 Mayo 21:00 ECU-Bolivia', 'Foro profesionales - Ciclo mayo 2026', 'zoom', 'America/La_Paz', 'published', '2026-05-11 21:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),
('PROF-ECU-LUN-1805', 'PROFESIONALES: Lunes 18 Mayo 21:00 ECU-Bolivia', 'Foro profesionales - Ciclo mayo 2026', 'zoom', 'America/La_Paz', 'published', '2026-05-18 21:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),
('PROF-ECU-LUN-2505', 'PROFESIONALES: Lunes 25 Mayo 21:00 ECU-Bolivia', 'Foro profesionales - Ciclo mayo 2026', 'zoom', 'America/La_Paz', 'published', '2026-05-25 21:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),

-- PROFESIONALES - México (Sábados 19:00)
('PROF-MEX-SAB-0905', 'PROFESIONALES: Sábado 9 Mayo 19:00 AR-México', 'Foro profesionales - Ciclo mayo 2026', 'zoom', 'America/Mexico_City', 'published', '2026-05-09 19:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),
('PROF-MEX-SAB-1605', 'PROFESIONALES: Sábado 16 Mayo 19:00 AR-México', 'Foro profesionales - Ciclo mayo 2026', 'zoom', 'America/Mexico_City', 'published', '2026-05-16 19:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),
('PROF-MEX-SAB-2305', 'PROFESIONALES: Sábado 23 Mayo 19:00 AR-México', 'Foro profesionales - Ciclo mayo 2026', 'zoom', 'America/Mexico_City', 'published', '2026-05-23 19:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),
('PROF-MEX-SAB-3005', 'PROFESIONALES: Sábado 30 Mayo 19:00 AR-México', 'Foro profesionales - Ciclo mayo 2026', 'zoom', 'America/Mexico_City', 'published', '2026-05-30 19:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),

-- ESTUDIANTES - Colombia (Sábados 15:00)
('EST-COL-SAB-0905', 'ESTUDIANTES: Sábado 9 Mayo 15:00 AR-Colombia', 'Foro estudiantes - Ciclo mayo 2026', 'zoom', 'America/Bogota', 'published', '2026-05-09 15:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),
('EST-COL-SAB-1605', 'ESTUDIANTES: Sábado 16 Mayo 15:00 AR-Colombia', 'Foro estudiantes - Ciclo mayo 2026', 'zoom', 'America/Bogota', 'published', '2026-05-16 15:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),
('EST-COL-SAB-2305', 'ESTUDIANTES: Sábado 23 Mayo 15:00 AR-Colombia', 'Foro estudiantes - Ciclo mayo 2026', 'zoom', 'America/Bogota', 'published', '2026-05-23 15:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),
('EST-COL-SAB-3005', 'ESTUDIANTES: Sábado 30 Mayo 15:00 AR-Colombia', 'Foro estudiantes - Ciclo mayo 2026', 'zoom', 'America/Bogota', 'published', '2026-05-30 15:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),

-- ESTUDIANTES - México (Sábados 11:00)
('EST-MEX-SAB-0905', 'ESTUDIANTES: Sábado 9 Mayo 11:00 AR-México', 'Foro estudiantes - Ciclo mayo 2026', 'zoom', 'America/Mexico_City', 'published', '2026-05-09 11:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),
('EST-MEX-SAB-1605', 'ESTUDIANTES: Sábado 16 Mayo 11:00 AR-México', 'Foro estudiantes - Ciclo mayo 2026', 'zoom', 'America/Mexico_City', 'published', '2026-05-16 11:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),
('EST-MEX-SAB-2305', 'ESTUDIANTES: Sábado 23 Mayo 11:00 AR-México', 'Foro estudiantes - Ciclo mayo 2026', 'zoom', 'America/Mexico_City', 'published', '2026-05-23 11:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),
('EST-MEX-SAB-3005', 'ESTUDIANTES: Sábado 30 Mayo 11:00 AR-México', 'Foro estudiantes - Ciclo mayo 2026', 'zoom', 'America/Mexico_City', 'published', '2026-05-30 11:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),

-- ESTUDIANTES - Guatemala (Miércoles 11:00)
('EST-GTM-MIE-0605', 'ESTUDIANTES: Miércoles 6 Mayo 11:00 AR-Guatemala', 'Foro estudiantes - Ciclo mayo 2026', 'zoom', 'America/Guatemala', 'published', '2026-05-06 11:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),
('EST-GTM-MIE-1305', 'ESTUDIANTES: Miércoles 13 Mayo 11:00 AR-Guatemala', 'Foro estudiantes - Ciclo mayo 2026', 'zoom', 'America/Guatemala', 'published', '2026-05-13 11:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),
('EST-GTM-MIE-2005', 'ESTUDIANTES: Miércoles 20 Mayo 11:00 AR-Guatemala', 'Foro estudiantes - Ciclo mayo 2026', 'zoom', 'America/Guatemala', 'published', '2026-05-20 11:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse'),
('EST-GTM-MIE-2705', 'ESTUDIANTES: Miércoles 27 Mayo 11:00 AR-Guatemala', 'Foro estudiantes - Ciclo mayo 2026', 'zoom', 'America/Guatemala', 'published', '2026-05-27 11:00:00', 'Debate y reflexión sobre salud mental', 'Virtual sincrónico', 50, 50, 'Inscribirse');
