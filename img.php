<?php
/**
 * Image serving endpoint — streams image BLOBs from the database.
 * Usage:
 *   img.php?p=PRODUCT_ID&t=main    → product main image
 *   img.php?p=PRODUCT_ID&t=thumb   → product thumbnail
 *   img.php?g=GALLERY_ID           → gallery image
 */

require_once __DIR__ . '/includes/dbconnect.php';

$p = (int)($_GET['p'] ?? 0);
$t = $_GET['t'] ?? 'main';   // 'main' | 'thumb'
$g = (int)($_GET['g'] ?? 0);

try {
    if ($g > 0) {
        $stmt = $pdo->prepare("SELECT image_data, image_mime FROM product_images WHERE id = ?");
        $stmt->execute([$g]);
    } elseif ($p > 0 && $t === 'thumb') {
        $stmt = $pdo->prepare("SELECT thumbnail_data AS image_data, thumbnail_mime AS image_mime FROM products WHERE id = ?");
        $stmt->execute([$p]);
    } elseif ($p > 0) {
        $stmt = $pdo->prepare("SELECT image_data, image_mime FROM products WHERE id = ?");
        $stmt->execute([$p]);
    } else {
        http_response_code(400); exit;
    }

    $row = $stmt->fetch();
} catch (PDOException $e) {
    http_response_code(500); exit;
}

if (!$row || empty($row['image_data'])) {
    http_response_code(404); exit;
}

$mime = $row['image_mime'] ?: 'image/jpeg';
header('Content-Type: ' . $mime);
header('Cache-Control: public, max-age=86400');
header('Content-Length: ' . strlen($row['image_data']));
echo $row['image_data'];
