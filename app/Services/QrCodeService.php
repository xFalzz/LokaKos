<?php

namespace App\Services;

use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeService
{
    public static function pngDataUri(string $payload, int $scale = 6): string
    {
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'scale' => $scale,
            'outputBase64' => true,
        ]);

        return (new QRCode($options))->render($payload);
    }
}
