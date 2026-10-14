<?php
if (($_GET['key'] ?? '') !== 'merchant_auth_fix_2026') {
    die("Unauthorized");
}

$zipFile = __DIR__ . '/merchant_auth_update.zip';
if (!file_exists($zipFile)) {
    die("Zip not found: " . $zipFile);
}

$zip = new ZipArchive();
if ($zip->open($zipFile) === TRUE) {
    $targetDir = dirname(__DIR__);
    $extracted = [];
    
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);
        $dest = $targetDir . '/' . $filename;
        $destDir = dirname($dest);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0777, true);
        }
        if (file_exists($dest)) {
            @unlink($dest);
        }
        $content = $zip->getFromIndex($i);
        file_put_contents($dest, $content);
        @chmod($dest, 0666);
        $extracted[] = "$filename (" . strlen($content) . " bytes)";
    }
    $zip->close();
    @unlink($zipFile);
    @unlink(__FILE__);
    echo "<h1>MerchantAuth Updated Successfully!</h1>\n<ul><li>" . implode("</li><li>", $extracted) . "</li></ul>\n";
} else {
    echo "Failed to open zip\n";
}
