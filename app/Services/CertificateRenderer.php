<?php

declare(strict_types=1);

require_once __DIR__ . '/SignatureGenerator.php';

/**
 * Renderiza el HTML de un certificado a partir de los datos del registro.
 *
 * Las plantillas viven en /app/Templates/certificates/{type}.php
 * Para agregar un tipo nuevo: crear el archivo y agregar el slug a SUPPORTED_TYPES.
 */
final class CertificateRenderer
{
    public const SUPPORTED_TYPES = ['attendance', 'completion'];

    public const DIRECTOR_DEFAULT_NAME = 'María Luz Genovese';

    private const DIRECTOR_DEFAULT_SIGNATURE = 'data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMzAwIDEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiBzdHlsZT0iYmFja2dyb3VuZDogd2hpdGU7Ij4KICA8cGF0aCBkPSJNIDEwIDUwIEwgNDAgNTAgQyA0MCAwIDUwIDAgNTAgNTAgTCA1MCAxMDAgTCA0MCAxMDAgQyA0MCAxMDAgMzAgMTAwIDIwIDEwMCBMMTAgNTAgWiIgc3Ryb2tlPSIjMGYzYThjIiBzdHJva2Utd2lkdGg9IjIiIGZpbGw9Im5vbmUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgogIDxwYXRoIGQ9Ik0gODAgNDAgQyAxMDAgMzAgMTEwIDI1IDEzMCAzMiBDIDE1MCAzOCAxNTAgNjAgMTMwIDcwIEMgMTEwIDc5IDkwIDcwIDgwIDYwIiBzdHJva2U9IiMwZjNhOGMiIHN0cm9rZS13aWR0aD0iMiIgZmlsbD0ibm9uZSIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CiAgPHBhdGggZD0iTSAxNjAgNDAgQyAxNzggMzAgMTkwIDI1IDIxMCAzMiBDIDIzMCAzOCAyMzAgNjAgMjEwIDcwIEMgMTkwIDc5IDE3MCA3MCAxNjAgNjAiIHN0cm9rZT0iIzBmM2E4YyIgc3Ryb2tlLXdpZHRoPSIyIiBmaWxsPSJub25lIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KICA8cGF0aCBkPSJNIDI0MCA0MCBDIDI2MCAzMiAyNzAgMzAgMjk0IDQwIEMgMjk2IDI1IDI5OCAxMCAzMDAgNTAiIHN0cm9rZT0iIzBmM2E4YyIgc3Ryb2tlLXdpZHRoPSIyIiBmaWxsPSJub25lIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KPC9zdmc+';

    /**
     * Devuelve true si el slug es un tipo soportado.
     */
    public static function isValidType(string $type): bool
    {
        return in_array($type, self::SUPPORTED_TYPES, true);
    }

    /**
     * Normaliza un type recibido por query string. Default: completion.
     */
    public static function normalizeType(?string $type): string
    {
        $candidate = strtolower(trim((string)$type));
        return self::isValidType($candidate) ? $candidate : 'completion';
    }

    /**
     * Renderiza la plantilla correspondiente y devuelve el HTML resultante.
     *
     * @param string $type 'attendance' | 'completion'
     * @param array  $data {participantName, forumCode, forumTitle, dateIssued, signatureDataUrl, directorName?, directorSignature?}
     */
    public static function render(string $type, array $data): string
    {
        if (!self::isValidType($type)) {
            throw new InvalidArgumentException('Tipo de certificado no soportado: ' . $type);
        }

        $variables = [
            'participantName' => (string)($data['participantName'] ?? ''),
            'forumCode' => (string)($data['forumCode'] ?? ''),
            'forumTitle' => (string)($data['forumTitle'] ?? ''),
            'dateIssued' => (string)($data['dateIssued'] ?? date('d/m/Y')),
            'signatureDataUrl' => (string)($data['signatureDataUrl'] ?? ''),
            'directorName' => (string)($data['directorName'] ?? self::DIRECTOR_DEFAULT_NAME),
            'directorSignature' => (string)($data['directorSignature'] ?? self::DIRECTOR_DEFAULT_SIGNATURE),
        ];

        if ($variables['signatureDataUrl'] === '') {
            $variables['signatureDataUrl'] = SignatureGenerator::generateFakeSignature($variables['participantName']);
        }

        $templatePath = __DIR__ . '/../Templates/certificates/' . $type . '.php';
        if (!is_file($templatePath)) {
            throw new RuntimeException('Plantilla de certificado no encontrada: ' . $templatePath);
        }

        ob_start();
        extract($variables, EXTR_OVERWRITE);
        include $templatePath;
        $html = ob_get_clean();

        if ($html === false) {
            throw new RuntimeException('Error al renderizar la plantilla del certificado.');
        }

        return $html;
    }

    /**
     * Formatea la fecha ISO/UTC del registro a "DD/MM/AAAA" en español.
     */
    public static function formatDate(string $iso): string
    {
        try {
            $dt = new DateTimeImmutable($iso);
            return $dt->format('d/m/Y');
        } catch (Throwable $e) {
            return date('d/m/Y');
        }
    }
}
