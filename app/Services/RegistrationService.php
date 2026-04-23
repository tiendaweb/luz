<?php

declare(strict_types=1);

final class RegistrationService
{
    /**
     * @param array<string,mixed> $input
     * @return array<string,mixed>
     */
    public function validateAndNormalize(array $input): array
    {
        $forumId = (int)($input['forumId'] ?? 0);
        $forumSlot = trim((string)($input['forumSlot'] ?? ''));
        $fullName = trim((string)($input['fullName'] ?? ''));
        $documentId = trim((string)($input['documentId'] ?? ''));
        $needsCert = !empty($input['needsCert']);
        $acceptanceChecked = !empty($input['acceptanceChecked']);
        $signatureDataUrl = trim((string)($input['signatureDataUrl'] ?? ''));
        $referralCode = strtoupper(trim((string)($input['referralCode'] ?? '')));

        if ($forumId < 1 && ($forumSlot === '' || mb_strlen($forumSlot) < 5 || mb_strlen($forumSlot) > 180)) {
            throw new InvalidArgumentException('Foro elegido inválido.');
        }
        if ($fullName === '' || mb_strlen($fullName) < 5 || mb_strlen($fullName) > 120) {
            throw new InvalidArgumentException('Nombre y apellidos inválidos.');
        }
        if (!preg_match('/^[\p{L}\s\-\'\.,]+$/u', $fullName)) {
            throw new InvalidArgumentException('Nombre y apellidos contiene caracteres no permitidos.');
        }
        if ($documentId === '' || mb_strlen($documentId) < 5 || mb_strlen($documentId) > 30) {
            throw new InvalidArgumentException('Documento inválido.');
        }
        if (!preg_match('/^[A-Za-z0-9\-\.\s]+$/', $documentId)) {
            throw new InvalidArgumentException('Documento contiene caracteres no permitidos.');
        }
        if (!$acceptanceChecked) {
            throw new InvalidArgumentException('Debe aceptar el compromiso para continuar.');
        }
        if (!str_starts_with($signatureDataUrl, 'data:image/png;base64,')) {
            throw new InvalidArgumentException('La firma digital es obligatoria y debe estar en formato PNG.');
        }
        if (strlen($signatureDataUrl) < 300) {
            throw new InvalidArgumentException('La firma digital parece incompleta.');
        }
        if ($referralCode !== '' && !preg_match('/^[A-Z0-9\-_]{4,32}$/', $referralCode)) {
            throw new InvalidArgumentException('Código de referido inválido.');
        }

        $paymentProof = is_array($input['paymentProof'] ?? null) ? $input['paymentProof'] : null;
        $proofName = $paymentProof['name'] ?? null;
        $proofMime = $paymentProof['mime'] ?? null;
        $proofSize = $paymentProof['size'] ?? null;
        $proofBase64 = $paymentProof['base64'] ?? null;

        if ($needsCert) {
            if (!$proofName || !$proofMime || !$proofBase64) {
                throw new InvalidArgumentException('Debe adjuntar comprobante si solicita certificación.');
            }
            $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
            if (!in_array((string)$proofMime, $allowedMimes, true)) {
                throw new InvalidArgumentException('Formato de comprobante no permitido.');
            }
            if (!is_numeric($proofSize) || (int)$proofSize < 1 || (int)$proofSize > 5 * 1024 * 1024) {
                throw new InvalidArgumentException('El comprobante debe pesar entre 1B y 5MB.');
            }
            if (!is_string($proofBase64) || !preg_match('/^[A-Za-z0-9+\/=\s]+$/', $proofBase64)) {
                throw new InvalidArgumentException('El contenido del comprobante no es válido.');
            }
        }

        return [
            'forumId' => $forumId > 0 ? $forumId : null,
            'forumSlot' => $forumSlot,
            'fullName' => $fullName,
            'documentId' => $documentId,
            'needsCert' => $needsCert,
            'acceptanceChecked' => true,
            'signatureDataUrl' => $signatureDataUrl,
            'referralCode' => $referralCode === '' ? null : $referralCode,
            'paymentProofName' => $proofName,
            'paymentProofMime' => $proofMime,
            'paymentProofSize' => is_numeric($proofSize) ? (int)$proofSize : null,
            'paymentProofBase64' => $proofBase64,
        ];
    }
}
