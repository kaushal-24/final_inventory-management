<?php

namespace App\Services;

class BarcodeService
{
    public function generateBarcode($text, $type = 'code128')
    {
        return [
            'text' => $text,
            'type' => $type,
            'data_url' => $this->generateDataUrl($text, $type),
        ];
    }

    public function generateQRCode($text, $size = 256)
    {
        return [
            'text' => $text,
            'size' => $size,
            'data_url' => $this->generateQRDataUrl($text, $size),
        ];
    }

    private function generateDataUrl($text, $type)
    {
        return 'data:image/svg+xml;base64,' . base64_encode($this->generateBarcodeSVG($text));
    }

    private function generateBarcodeSVG($text)
    {
        $width = 200;
        $height = 100;
        $svg = '<?xml version="1.0" encoding="UTF-8"?>
                <svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">
                    <rect width="100%" height="100%" fill="white"/>
                    <text x="50%" y="70" font-family="Arial" font-size="14" text-anchor="middle" fill="black">' . htmlspecialchars($text) . '</text>
                </svg>';
        return $svg;
    }

    private function generateQRDataUrl($text, $size)
    {
        return 'data:image/svg+xml;base64,' . base64_encode($this->generateQRSVG($text, $size));
    }

    private function generateQRSVG($text, $size)
    {
        $svg = '<?xml version="1.0" encoding="UTF-8"?>
                <svg width="' . $size . '" height="' . $size . '" xmlns="http://www.w3.org/2000/svg">
                    <rect width="100%" height="100%" fill="white"/>
                    <text x="50%" y="50%" font-family="Arial" font-size="12" text-anchor="middle" fill="black">' . htmlspecialchars($text) . '</text>
                </svg>';
        return $svg;
    }
}
