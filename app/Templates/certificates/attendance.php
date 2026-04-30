<?php
/**
 * PLANTILLA DE CERTIFICADO DE ASISTENCIA
 *
 * Se entrega a todos los inscriptos que hayan sido aprobados administrativamente
 * por el coordinador del foro (status=approved). No requiere asistencia mínima
 * ni validación de pago.
 *
 * Para personalizar este certificado:
 *  - Cambiar textos: secciones <h1>, <p>, etc. más abajo.
 *  - Cambiar colores: variables CSS al inicio del <style>.
 *  - Cambiar logo: el div .logo (actualmente texto "PSME").
 *  - Agregar firmas: duplicar el div .signature-block en .footer.
 *
 * Variables disponibles (vienen de CertificateRenderer):
 *  $participantName   string  - nombre del inscripto
 *  $forumCode         string  - código del foro
 *  $forumTitle        string  - título completo del foro
 *  $dateIssued        string  - fecha emisión formateada
 *  $signatureDataUrl  string  - data URL de la firma del participante
 *  $directorName      string  - nombre de la directora
 *  $directorSignature string  - data URL de la firma institucional
 */
declare(strict_types=1);

/** @var string $participantName */
/** @var string $forumCode */
/** @var string $forumTitle */
/** @var string $dateIssued */
/** @var string $signatureDataUrl */
/** @var string $directorName */
/** @var string $directorSignature */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado de Asistencia - <?= htmlspecialchars($participantName, ENT_QUOTES) ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        :root {
            --brand-primary: #0ea5e9;
            --brand-secondary: #4f46e5;
            --brand-dark: #075985;
            --brand-light: #e0f2fe;
            --accent-warm: #f59e0b;
            --neutral-text: #1f2937;
            --neutral-soft: #6b7280;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Georgia', serif; background: #f1f5f9; padding: 20px; }
        .certificate {
            width: 100%;
            max-width: 920px;
            margin: 0 auto;
            background: white;
            padding: 60px;
            text-align: center;
            border: 10px solid var(--brand-primary);
            box-shadow: 0 20px 60px rgba(7, 89, 133, 0.25);
            position: relative;
            overflow: hidden;
        }
        .certificate::before {
            content: '';
            position: absolute; inset: 0;
            background:
                repeating-linear-gradient(-45deg, transparent, transparent 30px, rgba(14, 165, 233, 0.08) 30px, rgba(14, 165, 233, 0.08) 60px);
            pointer-events: none;
        }
        .certificate::after {
            content: '';
            position: absolute; inset: 14px;
            border: 2px dashed var(--brand-secondary);
            pointer-events: none;
            border-radius: 4px;
        }
        .content { position: relative; z-index: 1; }
        .header { margin-bottom: 30px; }
        .logo {
            width: 90px; height: 90px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-dark) 100%);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 30px; font-weight: bold;
            letter-spacing: -1px;
            box-shadow: 0 8px 20px rgba(7, 89, 133, 0.35);
        }
        h1 {
            font-size: 50px;
            color: var(--brand-dark);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 4px;
            font-weight: 800;
        }
        .subtitle {
            font-size: 22px;
            color: var(--brand-secondary);
            margin-bottom: 20px;
            font-style: italic;
            font-weight: 600;
        }
        .divider {
            width: 220px; height: 3px;
            background: linear-gradient(90deg, transparent, var(--brand-primary), transparent);
            margin: 25px auto;
        }
        .body { margin: 30px 0; line-height: 1.8; }
        .body p { font-size: 16px; color: var(--neutral-text); margin: 12px 0; }
        .highlight {
            font-size: 32px;
            font-weight: bold;
            color: var(--brand-dark);
            margin: 20px 0;
            font-family: 'Georgia', serif;
        }
        .forum-info {
            background: var(--brand-light);
            padding: 22px;
            border-radius: 12px;
            margin: 28px auto;
            max-width: 640px;
            border-left: 5px solid var(--brand-primary);
            text-align: left;
        }
        .forum-info p { margin: 6px 0; }
        .forum-code {
            font-weight: bold;
            color: var(--brand-secondary);
            font-size: 14px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .forum-title { font-size: 20px; color: var(--brand-dark); font-weight: 700; }
        .seal {
            display: inline-block;
            background: var(--accent-warm);
            color: white;
            padding: 8px 18px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 14px;
        }
        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-around;
            align-items: flex-end;
            min-height: 130px;
            gap: 30px;
        }
        .signature-block {
            flex: 1;
            border-top: 2px solid var(--neutral-text);
            padding-top: 8px;
        }
        .signature-image { width: 160px; height: 70px; margin: 0 auto 8px; }
        .signature-image img { width: 100%; height: 100%; object-fit: contain; }
        .signature-label { font-size: 12px; color: var(--neutral-soft); font-weight: bold; }
        .director-name { font-weight: bold; color: var(--brand-dark); }
        .director-title { font-size: 11px; color: var(--neutral-soft); margin-top: 4px; }
        .date {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: var(--neutral-soft);
            font-style: italic;
        }
        .actions {
            display: flex; gap: 12px; justify-content: center; margin-bottom: 20px;
        }
        .actions button {
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            font-weight: bold;
        }
        .actions button:hover { opacity: 0.9; }
        .btn-print { background: var(--brand-primary); color: white; }
        .btn-pdf { background: var(--brand-secondary); color: white; }
        @media print {
            body { background: white; padding: 0; }
            .actions { display: none; }
            .certificate { box-shadow: none; max-width: 100%; margin: 0; padding: 40px; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button class="btn-print" onclick="window.print()">Imprimir</button>
        <button class="btn-pdf" onclick="downloadPDF()">Descargar PDF</button>
    </div>

    <div class="certificate">
        <div class="content">
            <div class="header">
                <div class="logo">PSME</div>
                <h1>Certificado</h1>
                <p class="subtitle">De Asistencia y Participación</p>
            </div>

            <div class="divider"></div>

            <div class="body">
                <p>Se certifica que</p>
                <p class="highlight"><?= htmlspecialchars($participantName, ENT_QUOTES) ?></p>
                <p>ha asistido y participado en el foro:</p>
            </div>

            <div class="forum-info">
                <p class="forum-code"><?= htmlspecialchars($forumCode, ENT_QUOTES) ?></p>
                <p class="forum-title"><?= htmlspecialchars($forumTitle, ENT_QUOTES) ?></p>
            </div>

            <p style="margin-top: 24px; font-size: 14px; color: var(--neutral-text);">
                Reconocemos su presencia activa en los espacios sincrónicos de debate<br>
                y reflexión grupal. Su participación contribuye al ejercicio colectivo<br>
                de pensar la salud mental como derecho y como práctica situada.
            </p>

            <div class="seal">Asistencia · Foros PSME</div>

            <div class="footer">
                <div class="signature-block">
                    <div class="signature-image">
                        <img src="<?= htmlspecialchars($signatureDataUrl, ENT_QUOTES) ?>" alt="Firma participante">
                    </div>
                    <p class="signature-label"><?= htmlspecialchars($participantName, ENT_QUOTES) ?></p>
                    <p class="signature-label">Participante</p>
                </div>

                <div class="signature-block">
                    <div class="signature-image">
                        <img src="<?= htmlspecialchars($directorSignature, ENT_QUOTES) ?>" alt="Firma directora">
                    </div>
                    <p class="director-name"><?= htmlspecialchars($directorName, ENT_QUOTES) ?></p>
                    <p class="director-title">Directora · Foros PSME</p>
                    <p class="director-title">Psicóloga Social</p>
                </div>
            </div>

            <div class="date">
                <p>Emitido el <?= htmlspecialchars($dateIssued, ENT_QUOTES) ?></p>
            </div>
        </div>
    </div>

    <script>
    function downloadPDF() {
        const element = document.querySelector('.certificate');
        const filename = 'Certificado_Asistencia_<?= htmlspecialchars(preg_replace('/\s+/', '_', $participantName), ENT_QUOTES) ?>_' + new Date().toISOString().split('T')[0] + '.pdf';
        html2pdf().set({
            margin: 0,
            filename: filename,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { orientation: 'landscape', unit: 'mm', format: 'a4' }
        }).from(element).save();
    }
    </script>
</body>
</html>
