<?php
require_once '../includes/dbconnect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Only POST allowed ──
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// ── Require login ──
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$order_id = (int) ($_POST['order_id'] ?? 0);

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

// ── Check order exists and belongs to user ──
$checkStmt = $pdo->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
$checkStmt->execute([$order_id, $user_id]);
$order = $checkStmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

// ── Only pending orders can be cancelled ──
if ($order['status'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'Chỉ có thể hủy đơn hàng chờ xác nhận']);
    exit;
}

// ── Update order status ──
try {
    $updateStmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ? AND user_id = ?");
    $updateStmt->execute(['cancelled', $order_id, $user_id]);

    echo json_encode(['success' => true, 'message' => 'Đơn hàng đã hủy thành công']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
