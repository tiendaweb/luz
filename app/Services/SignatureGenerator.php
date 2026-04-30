<?php

declare(strict_types=1);

class SignatureGenerator
{
    public static function generateFakeSignature(string $name): string
    {
        $hash = md5($name . 'signature_seed_v1');
        $curves = self::generateCurves($hash, $name);

        $svg = '
<svg viewBox="0 0 300 100" xmlns="http://www.w3.org/2000/svg" style="background: white;">
  <path d="' . $curves . '" stroke="#333333" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private static function generateCurves(string $hash, string $name): string
    {
        $initials = self::extractInitials($name);
        $seed = hexdec(substr($hash, 0, 8));
        srand($seed);

        $x = 20;
        $y = 50;
        $path = "M $x $y";

        foreach (str_split($initials) as $i => $char) {
            $char_seed = ord($char) + ($i * 100) + $seed;
            srand($char_seed);

            $numCurves = rand(3, 5);
            for ($j = 0; $j < $numCurves; $j++) {
                $ctrl1x = $x + rand(15, 35);
                $ctrl1y = $y + rand(-20, 20);
                $ctrl2x = $x + rand(40, 70);
                $ctrl2y = $y + rand(-20, 20);
                $endx = $x + rand(60, 100);
                $endy = $y + rand(-10, 10);

                $path .= " C $ctrl1x $ctrl1y, $ctrl2x $ctrl2y, $endx $endy";
                $x = $endx;
                $y = $endy;
            }

            $x += 30;
        }

        $finalCurves = rand(2, 4);
        for ($i = 0; $i < $finalCurves; $i++) {
            $ctrl1x = $x + rand(5, 15);
            $ctrl1y = $y + rand(-15, 15);
            $ctrl2x = $x + rand(20, 35);
            $ctrl2y = $y + rand(-15, 15);
            $endx = $x + rand(30, 50);
            $endy = $y + rand(-10, 10);

            $path .= " C $ctrl1x $ctrl1y, $ctrl2x $ctrl2y, $endx $endy";
            $x = $endx;
            $y = $endy;
        }

        return $path;
    }

    private static function extractInitials(string $name): string
    {
        $parts = explode(' ', trim($name));
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            if (!empty($part)) {
                $initials .= strtoupper($part[0]);
            }
        }
        return $initials ?: 'X';
    }

    public static function getSignatureDataUrl(array $registration): string
    {
        if (!empty($registration['signature_data_url'])) {
            return $registration['signature_data_url'];
        }

        if (!empty($registration['signature_data'])) {
            return $registration['signature_data'];
        }

        return self::generateFakeSignature($registration['full_name'] ?? 'User');
    }
}
