<?php
/**
 * Creates (or resets) the admin user with a freshly generated bcrypt hash.
 * Run once after importing schema.sql:
 *   php database/setup_admin.php
 * Or open in browser: http://localhost/shopping-website-php/database/setup_admin.php
 */

define('BASE_DIR', __DIR__ . '/..');
require BASE_DIR . '/includes/dbconnect.php';

$email    = 'admin';
$password = 'admin123';
$hash     = password_hash($password, PASSWORD_BCRYPT);

// Upsert: insert or update if already exists
$stmt = $pdo->prepare("
    INSERT INTO users (email, password_hash, full_name, role, is_active)
    VALUES (?, ?, 'Admin HKT', 'admin', 1)
    ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)
");
$stmt->execute([$email, $hash]);

echo "✓ Admin user ready.\n";
echo "  Email   : {$email}\n";
echo "  Password: {$password}\n";
echo "  Hash    : {$hash}\n";
