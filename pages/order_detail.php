<?php
require_once '../includes/dbconnect.php';

$page_title = "Chi tiết đơn hàng";
$page_css = "../assets/css/order_detail.css";
$base_path = "../";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Get order ID from URL ──
$order_id = (int) ($_GET['id'] ?? 0);
if ($order_id <= 0) {
    header('Location: order.php');
    exit;
}

// ── Query order ──
$orderStmt = $pdo->prepare(
    "SELECT o.id, o.user_id, o.status, o.total_amount, o.payment_method, 
            o.payment_status, o.note, o.created_at,
            a.recipient_name, a.phone, a.street, a.district, a.city
     FROM orders o
     LEFT JOIN addresses a ON a.id = o.address_id
     WHERE o.id = ? AND o.user_id = ?
     LIMIT 1"
);

$orderStmt->execute([$order_id, $_SESSION['user_id']]);
$order = $orderStmt->fetch();

if (!$order) {
    header('Location: order.php');
    exit;
}

// ── Query order items ──
$itemsStmt = $pdo->prepare(
    "SELECT oi.product_id, oi.quantity, oi.unit_price, p.name
     FROM order_items oi
     LEFT JOIN products p ON p.id = oi.product_id
     WHERE oi.order_id = ?
     ORDER BY oi.id"
);
$itemsStmt->execute([$order_id]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

// ── Helper function ──
if (!function_exists('formatCurrencyVND')) {
    function formatCurrencyVND(float $value): string
    {
        return number_format($value, 0, ',', '.') . 'đ';
    }
}

// ── Status labels ──
$statusLabels = [
    'pending' => ['Chờ xác nhận', '#FFA500'],
    'confirmed' => ['Đã xác nhận', '#4CAF50'],
    'shipping' => ['Đang giao', '#2196F3'],
    'delivered' => ['Đã giao', '#4CAF50'],
    'cancelled' => ['Đã hủy', '#F44336'],
];

$paymentStatusLabels = [
    'paid' => ['Đã thanh toán', '#4CAF50'],
    'unpaid' => ['Chưa thanh toán', '#FFA500'],
    'refunded' => ['Đã hoàn tiền', '#9E9E9E'],
];

$paymentMethodLabels = [
    'cod' => 'Thanh toán khi nhận hàng',
    'bank_transfer' => 'Chuyển khoản ngân hàng',
    'momo' => 'Ví MoMo',
    'vnpay' => 'VN Pay',
];

ob_start();
?>

<section class="order-detail-page">
    <div class="order-detail-container">

        <!-- Header -->
        <div class="order-header">
            <h1>Chi tiết đơn hàng</h1>
            <p class="order-date">
                Ngày đặt: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
            </p>
        </div>

        <!-- Status section -->
        <div class="status-section">
            <div class="status-badge" style="background-color: <?= $statusLabels[$order['status']][1] ?>">
                <?= $statusLabels[$order['status']][0] ?>
            </div>
            <div class="payment-status"
                style="background-color: <?= $paymentStatusLabels[$order['payment_status']][1] ?>">
                <?= $paymentStatusLabels[$order['payment_status']][0] ?>
            </div>
        </div>

        <div class="order-grid">

            <!-- Left: Order items & address -->
            <div class="order-main">

                <!-- Items section -->
                <div class="order-section">
                    <h2>Sản phẩm đã đặt</h2>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <a href="product_detail.php?id=<?= (int) $item['product_id'] ?>" class="item-name">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </a>
                                    </td>
                                    <td><?= formatCurrencyVND($item['unit_price']) ?></td>
                                    <td><?= (int) $item['quantity'] ?></td>
                                    <td class="item-total">
                                        <?= formatCurrencyVND((float) $item['unit_price'] * (int) $item['quantity']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Address section -->
                <div class="order-section">
                    <h2>Địa chỉ giao hàng</h2>
                    <div class="address-box">
                        <strong><?= htmlspecialchars($order['recipient_name'] ?? 'N/A') ?></strong>
                        <p><?= htmlspecialchars($order['phone'] ?? '') ?></p>
                        <p>
                            <?= htmlspecialchars($order['street'] ?? '') ?><br>
                            <?= htmlspecialchars($order['district'] ?? '') ?>,
                            <?= htmlspecialchars($order['city'] ?? '') ?>
                        </p>
                    </div>
                </div>

                <!-- Notes section -->
                <?php if (!empty($order['note'])): ?>
                    <div class="order-section">
                        <h2>Ghi chú đơn hàng</h2>
                        <p class="note-box"><?= htmlspecialchars($order['note']) ?></p>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Right: Summary -->
            <aside class="order-summary">
                <h3>Tóm tắt đơn hàng</h3>

                <!-- Order info -->
                <div class="summary-item">
                    <span>Mã đơn hàng:</span>
                    <strong>#<?= (int) $order['id'] ?></strong>
                </div>

                <div class="summary-item">
                    <span>Phương thức thanh toán:</span>
                    <strong><?= htmlspecialchars($paymentMethodLabels[$order['payment_method']] ?? 'N/A') ?></strong>
                </div>

                <div class="summary-item">
                    <span>Trạng thái thanh toán:</span>
                    <strong style="color: <?= $paymentStatusLabels[$order['payment_status']][1] ?>">
                        <?= $paymentStatusLabels[$order['payment_status']][0] ?>
                    </strong>
                </div>

                <div class="summary-item">
                    <span>Trạng thái đơn hàng:</span>
                    <strong style="color: <?= $statusLabels[$order['status']][1] ?>">
                        <?= $statusLabels[$order['status']][0] ?>
                    </strong>
                </div>

                <div class="summary-divider"></div>

                <!-- Price breakdown -->
                <div class="price-breakdown">
                    <?php
                    $subtotal = 0;
                    foreach ($items as $item) {
                        $subtotal += (float) $item['unit_price'] * (int) $item['quantity'];
                    }
                    $shippingFee = 30000;
                    $grandTotal = (float) $order['total_amount'];
                    ?>

                    <div class="breakdown-row">
                        <span>Tạm tính:</span>
                        <strong><?= formatCurrencyVND($subtotal) ?></strong>
                    </div>
                    <div class="breakdown-row">
                        <span>Phí vận chuyển:</span>
                        <strong><?= formatCurrencyVND($shippingFee) ?></strong>
                    </div>

                    <div class="breakdown-divider"></div>

                    <div class="breakdown-total">
                        <span>Tổng thanh toán:</span>
                        <strong><?= formatCurrencyVND($grandTotal) ?></strong>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="order-actions">
                    <a href="product.php" class="btn-secondary">Tiếp tục mua sắm</a>
                    <a href="order.php" class="btn-secondary">Xem tất cả đơn hàng</a>
                </div>
            </aside>

        </div><!-- /order-grid -->

    </div><!-- /order-detail-container -->
</section>

<?php
$page_content = ob_get_clean();
include('../includes/layout.php');
?>