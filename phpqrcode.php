<?php
/*
 * PHP QR Code encoder
 *
 * This file contains the main class for generating QR codes.
 */

class QRcode {
    // Error correction levels
    const QR_ECLEVEL_L = 0; // Low
    const QR_ECLEVEL_M = 1; // Medium
    const QR_ECLEVEL_Q = 2; // Quartile
    const QR_ECLEVEL_H = 3; // High

    /**
     * Generate a PNG QR code image.
     *
     * @param string $text   The text to encode in the QR code.
     * @param string $file   The file path to save the QR code image (optional).
     * @param int    $level  Error correction level (default: QR_ECLEVEL_L).
     * @param int    $size   Size of the QR code (default: 10).
     * @param int    $margin Margin around the QR code (default: 4).
     */
    public static function png($text, $file = false, $level = self::QR_ECLEVEL_L, $size = 10, $margin = 4) {
        // Check if GD library is installed
        if (!function_exists('imagecreate')) {
            throw new Exception('GD library is not installed.');
        }

        // Generate QR code using GD library
        $qrSize = $size * 21 + 2 * $margin;
        $qr = imagecreate($qrSize, $qrSize);

        // Define colors
        $bg = imagecolorallocate($qr, 255, 255, 255); // Background color (white)
        $fg = imagecolorallocate($qr, 0, 0, 0); // Foreground color (black)

        // Fill the background
        imagefilledrectangle($qr, 0, 0, $qrSize, $qrSize, $bg);

        // Generate QR code data (simplified for demonstration)
        $data = self::generateQRData($text, $level);

        // Draw QR code
        for ($i = 0; $i < 21; $i++) {
            for ($j = 0; $j < 21; $j++) {
                if ($data[$i][$j]) {
                    imagefilledrectangle($qr, $margin + $j * $size, $margin + $i * $size, $margin + ($j + 1) * $size - 1, $margin + ($i + 1) * $size - 1, $fg);
                }
            }
        }

        // Save or output the image
        if ($file) {
            imagepng($qr, $file);
        } else {
            header('Content-Type: image/png');
            imagepng($qr);
        }

        imagedestroy($qr);
    }

    /**
     * Generate QR code data (simplified for demonstration).
     *
     * @param string $text  The text to encode.
     * @param int    $level Error correction level.
     * @return array        QR code data.
     */
    private static function generateQRData($text, $level) {
        // Simplified QR code data generation
        // This is a placeholder and does not generate valid QR codes.
        // For a full implementation, use a proper QR code library.
        $data = [];
        for ($i = 0; $i < 21; $i++) {
            $data[$i] = [];
            for ($j = 0; $j < 21; $j++) {
                $data[$i][$j] = ($i + $j) % 2;
            }
        }
        return $data;
    }
}
?>