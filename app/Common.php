<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (!function_exists('validate_uploaded_file_ext')) {
    /**
     * Memvalidasi file upload (gambar atau dokumen) secara mendalam menggunakan magic bytes dan client extension.
     * Mengatasi kendala ketika Flutter/klien mengirimkan Content-Type application/octet-stream atau image/jpg.
     *
     * @param mixed $file CodeIgniter\HTTP\Files\UploadedFile
     * @param array $allowedExts Daftar ekstensi yang diizinkan (default: ['jpg', 'jpeg', 'png', 'webp', 'pdf'])
     * @return string|null Mengembalikan ekstensi aman ('jpg', 'png', 'webp', 'pdf') atau null jika tidak valid
     */
    function validate_uploaded_file_ext($file, array $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'pdf']): ?string
    {
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $tempPath = $file->getTempName();
        $detectedExt = null;

        // 1. Cek tipe gambar riil menggunakan getimagesize (magic bytes binary)
        if (!empty($tempPath) && file_exists($tempPath) && is_readable($tempPath)) {
            $imgInfo = @getimagesize($tempPath);
            if ($imgInfo !== false && isset($imgInfo[2])) {
                switch ($imgInfo[2]) {
                    case IMAGETYPE_JPEG:
                        $detectedExt = 'jpg';
                        break;
                    case IMAGETYPE_PNG:
                        $detectedExt = 'png';
                        break;
                    case IMAGETYPE_WEBP:
                        $detectedExt = 'webp';
                        break;
                    case IMAGETYPE_GIF:
                        $detectedExt = 'gif';
                        break;
                }
            }

            // 2. Jika bukan gambar biasa, cek apakah file PDF riil (magic bytes %PDF)
            if (!$detectedExt && in_array('pdf', $allowedExts)) {
                $handle = @fopen($tempPath, 'rb');
                if ($handle) {
                    $header = fread($handle, 4);
                    fclose($handle);
                    if ($header === '%PDF') {
                        $detectedExt = 'pdf';
                    }
                }
            }
        }

        // 3. Fallback: Cek ekstensi file asli dari nama file klien (getClientExtension / getClientName)
        if (!$detectedExt) {
            $clientExt = strtolower($file->getClientExtension() ?? pathinfo($file->getClientName() ?? '', PATHINFO_EXTENSION));
            if (in_array($clientExt, ['jpg', 'jpeg'])) {
                $detectedExt = 'jpg';
            } elseif (in_array($clientExt, ['png', 'webp', 'pdf', 'gif'])) {
                $detectedExt = $clientExt;
            }
        }

        // 4. Fallback: Cek guessExtension CI4 jika bukan 'bin'
        if (!$detectedExt) {
            $guess = strtolower($file->guessExtension() ?? '');
            if ($guess !== 'bin' && !empty($guess)) {
                if (in_array($guess, ['jpg', 'jpeg'])) {
                    $detectedExt = 'jpg';
                } elseif (in_array($guess, ['png', 'webp', 'pdf', 'gif'])) {
                    $detectedExt = $guess;
                }
            }
        }

        // Normalisasi format (jpeg -> jpg)
        if ($detectedExt === 'jpeg') {
            $detectedExt = 'jpg';
        }

        // 5. Validasi terhadap allowedExts
        if ($detectedExt) {
            if (in_array($detectedExt, $allowedExts)) {
                return $detectedExt;
            }
            if ($detectedExt === 'jpg' && in_array('jpeg', $allowedExts)) {
                return 'jpg';
            }
        }

        return null;
    }
}

