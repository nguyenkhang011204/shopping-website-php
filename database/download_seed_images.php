<?php
/**
 * Downloads seed product images from external URLs and stores them
 * directly in the database as BLOB data.
 *
 * Run once after importing schema.sql + seed.sql:
 *   php database/download_seed_images.php
 * Or open in browser:
 *   http://localhost/shopping-website-php/database/download_seed_images.php
 */

define('BASE_DIR', __DIR__ . '/..');
require BASE_DIR . '/includes/dbconnect.php';

// ── Image source URLs ─────────────────────────────────────────────────────
// product_id => ['image' => url, 'thumbnail' => url]
$product_images = [
    1 => [
        'image'     => 'https://i5.walmartimages.com/seo/Alimens-Gentle-Mens-Long-Sleeve-Stretch-Dress-Shirts-Casual-Button-Down-Shirt_d5cf7f15-87a2-48ac-84b9-a27401d84970.25eba54662edc99079389825a966ee00.png?odnWidth=180&odnHeight=180&odnBg=ffffff',
        'thumbnail' => 'https://i5.walmartimages.com/seo/Alimens-Gentle-Mens-Long-Sleeve-Stretch-Dress-Shirts-Casual-Button-Down-Shirt_d5cf7f15-87a2-48ac-84b9-a27401d84970.25eba54662edc99079389825a966ee00.png?odnWidth=180&odnHeight=180&odnBg=ffffff',
    ],
    2 => [
        'image'     => 'https://m.media-amazon.com/images/I/B1pppR4gVKL._CLa%7C500%2C468%7C71LyF4uUprL.png%7C0%2C0%2C500%2C468%2B0.0%2C0.0%2C500.0%2C468.0_AC_SR400_.png',
        'thumbnail' => 'https://m.media-amazon.com/images/I/B1pppR4gVKL._CLa%7C500%2C468%7C71LyF4uUprL.png%7C0%2C0%2C500%2C468%2B0.0%2C0.0%2C500.0%2C468.0_AC_SR400_.png',
    ],
    3 => [
        'image'     => 'https://m.media-amazon.com/images/I/B1pppR4gVKL._CLa%7C500%2C468%7C71tdMD3TItL.png%7C0%2C0%2C500%2C468%2B0.0%2C0.0%2C500.0%2C468.0_AC_SR400_.png',
        'thumbnail' => 'https://m.media-amazon.com/images/I/B1pppR4gVKL._CLa%7C500%2C468%7C71tdMD3TItL.png%7C0%2C0%2C500%2C468%2B0.0%2C0.0%2C500.0%2C468.0_AC_SR400_.png',
    ],
    4 => [
        'image'     => 'https://m.media-amazon.com/images/I/41JR4lcBXuS._AC_SR70_.jpg',
        'thumbnail' => 'https://m.media-amazon.com/images/I/41JR4lcBXuS._AC_SR70_.jpg',
    ],
    5 => [
        'image'     => 'https://m.media-amazon.com/images/I/41dL+vU8-SL._AC_SR70_.jpg',
        'thumbnail' => 'https://m.media-amazon.com/images/I/41dL+vU8-SL._AC_SR70_.jpg',
    ],
    6 => [
        'image'     => 'https://m.media-amazon.com/images/I/61ZHnVNnFwL._AC_SR70_.jpg',
        'thumbnail' => 'https://m.media-amazon.com/images/I/61ZHnVNnFwL._AC_SR70_.jpg',
    ],
    7 => [
        'image'     => 'https://m.media-amazon.com/images/I/51VZvNUGxcL._AC_SR70_.jpg',
        'thumbnail' => 'https://m.media-amazon.com/images/I/51VZvNUGxcL._AC_SR70_.jpg',
    ],
    8 => [
        'image'     => 'https://m.media-amazon.com/images/I/61x2QrZHFtL._AC_SR70_.jpg',
        'thumbnail' => 'https://m.media-amazon.com/images/I/61x2QrZHFtL._AC_SR70_.jpg',
    ],
];

// product_id => [[url, sort_order], ...]
$gallery_images = [
    1 => [
        ['https://i5.walmartimages.com/seo/Alimens-Gentle-Mens-Long-Sleeve-Stretch-Dress-Shirts-Casual-Button-Down-Shirt_d5cf7f15-87a2-48ac-84b9-a27401d84970.25eba54662edc99079389825a966ee00.png?odnWidth=180&odnHeight=180&odnBg=ffffff', 0],
        ['https://i5.walmartimages.com/asr/930fcc8b-a370-429c-83d0-ddc836c04675.ca5fe2e823d2422a128de2c0b5f25784.png?odnWidth=180&odnHeight=180&odnBg=ffffff', 1],
        ['https://i5.walmartimages.com/asr/38c66944-d740-4879-a372-2e9327975244.36a15c64c8c4d722d17eaeaef0969f77.jpeg?odnWidth=180&odnHeight=180&odnBg=ffffff', 2],
        ['https://i5.walmartimages.com/asr/e8058bc3-d868-4d04-98c6-4ec33a91db0c.4843a95153f555d0482b794bdd8027cd.png?odnWidth=180&odnHeight=180&odnBg=ffffff', 3],
    ],
    2 => [['https://m.media-amazon.com/images/I/B1pppR4gVKL._CLa%7C500%2C468%7C71LyF4uUprL.png%7C0%2C0%2C500%2C468%2B0.0%2C0.0%2C500.0%2C468.0_AC_SR400_.png', 0]],
    3 => [['https://m.media-amazon.com/images/I/B1pppR4gVKL._CLa%7C500%2C468%7C71tdMD3TItL.png%7C0%2C0%2C500%2C468%2B0.0%2C0.0%2C500.0%2C468.0_AC_SR400_.png', 0]],
    4 => [
        ['https://m.media-amazon.com/images/I/41JR4lcBXuS._AC_SR70_.jpg', 0],
        ['https://m.media-amazon.com/images/I/51CWEG5aaqS._AC_SR70_.jpg', 1],
    ],
    5 => [
        ['https://m.media-amazon.com/images/I/41dL+vU8-SL._AC_SR70_.jpg', 0],
        ['https://m.media-amazon.com/images/I/41f8H6XGruL._AC_SR70_.jpg', 1],
    ],
    6 => [['https://m.media-amazon.com/images/I/61ZHnVNnFwL._AC_SR70_.jpg', 0]],
    7 => [['https://m.media-amazon.com/images/I/51VZvNUGxcL._AC_SR70_.jpg', 0]],
    8 => [['https://m.media-amazon.com/images/I/61x2QrZHFtL._AC_SR70_.jpg', 0]],
];

// ── Download helper ───────────────────────────────────────────────────────
function fetch_blob(string $url): ?array {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 5,
            CURLOPT_USERAGENT => 'Mozilla/5.0', CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $data = curl_exec($ch);
        $ok   = curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
        @curl_close($ch);
        if (!$ok || !$data) return null;
    } else {
        $data = @file_get_contents($url);
        if (!$data) return null;
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($data);
    if (!str_starts_with($mime, 'image/')) return null;
    return ['data' => $data, 'mime' => $mime];
}

// Output helper
$is_browser = php_sapi_name() !== 'cli';
function out(string $line): void {
    global $is_browser;
    echo $is_browser ? nl2br(htmlspecialchars($line)) . "\n" : $line . "\n";
    if ($is_browser) ob_flush();
    flush();
}

if ($is_browser) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8">'
       . '<title>Download Seed Images</title>'
       . '<style>body{font-family:monospace;padding:20px;background:#111;color:#0f0;}</style>'
       . '</head><body><pre>';
}

out("=== Downloading seed images into database ===\n");

// ── Products: main image + thumbnail ─────────────────────────────────────
$upd = $pdo->prepare(
    "UPDATE products SET image_data=?, image_mime=?, thumbnail_data=?, thumbnail_mime=? WHERE id=?"
);

foreach ($product_images as $pid => $urls) {
    out("[product #{$pid}]");

    $img_blob   = null;
    $thumb_blob = null;

    out("  image: {$urls['image']}");
    $img_blob = fetch_blob($urls['image']);
    out($img_blob ? "  → OK (" . strlen($img_blob['data']) . " bytes, {$img_blob['mime']})" : "  → FAILED");

    if ($urls['thumbnail'] === $urls['image']) {
        $thumb_blob = $img_blob; // reuse already-downloaded data
        out("  thumbnail: (same as image)");
    } else {
        out("  thumbnail: {$urls['thumbnail']}");
        $thumb_blob = fetch_blob($urls['thumbnail']);
        out($thumb_blob ? "  → OK (" . strlen($thumb_blob['data']) . " bytes)" : "  → FAILED");
    }

    $upd->execute([
        $img_blob   ? $img_blob['data']   : null,
        $img_blob   ? $img_blob['mime']   : null,
        $thumb_blob ? $thumb_blob['data'] : null,
        $thumb_blob ? $thumb_blob['mime'] : null,
        $pid,
    ]);
}

// ── Gallery images ────────────────────────────────────────────────────────
out("\n[gallery]");
$ins = $pdo->prepare(
    "INSERT INTO product_images (product_id, image_data, image_mime, sort_order) VALUES (?,?,?,?)"
);

foreach ($gallery_images as $pid => $entries) {
    foreach ($entries as [$url, $sort]) {
        out("  product #{$pid} sort={$sort}: {$url}");
        $blob = fetch_blob($url);
        if ($blob) {
            $ins->execute([$pid, $blob['data'], $blob['mime'], $sort]);
            out("  → OK (" . strlen($blob['data']) . " bytes)");
        } else {
            out("  → FAILED (skipped)");
        }
    }
}

out("\n=== Done ===");

if ($is_browser) echo '</pre></body></html>';
