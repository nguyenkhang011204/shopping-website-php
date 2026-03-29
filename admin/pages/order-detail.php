<?php
// ── Get order ID ───────────────────────────────────────────────────────────────
$order_id = (int) ($_GET['id'] ?? 0);
if ($order_id <= 0) {
    while (ob_get_level() > 0)
        ob_end_clean();
    header("Location: index.php?page=manageOrder&msg=not_found");
    exit;
}

// ── Fetch order details ────────────────────────────────────────────────────────
$orderStmt = $pdo->prepare(
    "SELECT o.id, o.user_id, o.status, o.total_amount, o.payment_method,
            o.payment_status, o.note, o.created_at, u.full_name, u.email, u.phone,
            a.recipient_name, a.street, a.district, a.city, a.phone AS addr_phone
     FROM orders o
     JOIN users u ON o.user_id = u.id
     JOIN addresses a ON o.address_id = a.id
     WHERE o.id = ?"
);
$orderStmt->execute([$order_id]);
$order = $orderStmt->fetch(PDO::FETCH_ASSOC) ?: null;

if (!$order) {
    while (ob_get_level() > 0)
        ob_end_clean();
    header("Location: index.php?page=manageOrder&msg=not_found");
    exit;
}

// ── Fetch order items ─────────────────────────────────────────────────────────
$itemsStmt = $pdo->prepare(
    "SELECT oi.id, oi.product_id, oi.size, oi.quantity, oi.unit_price,
            p.name, p.slug, p.image_data, p.image_mime
     FROM order_items oi
     JOIN products p ON oi.product_id = p.id
     WHERE oi.order_id = ?
     ORDER BY oi.id"
);
$itemsStmt->execute([$order_id]);
$order_items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

// ── CSRF for status update ─────────────────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── POST: Update order status ──────────────────────────────────────────────────
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $msg = 'csrf';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_status') {
            $status = $_POST['status'] ?? '';
            $valid_statuses = ['pending', 'confirmed', 'shipping', 'delivered', 'cancelled'];
            if (in_array($status, $valid_statuses)) {
                $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $order_id]);
                $order['status'] = $status;
                $msg = 'updated';
            }
        }

        if ($action === 'update_payment_status') {
            $pay_status = $_POST['payment_status'] ?? '';
            $valid_pay_statuses = ['unpaid', 'paid', 'refunded'];
            if (in_array($pay_status, $valid_pay_statuses)) {
                $pdo->prepare("UPDATE orders SET payment_status = ? WHERE id = ?")->execute([$pay_status, $order_id]);
                $order['payment_status'] = $pay_status;
                $msg = 'pay_updated';
            }
        }

        if ($action === 'update_note') {
            $note = $_POST['note'] ?? '';
            $pdo->prepare("UPDATE orders SET note = ? WHERE id = ?")->execute([$note, $order_id]);
            $order['note'] = $note;
            $msg = 'note_updated';
        }
    }
}

// ── Helper functions ──────────────────────────────────────────────────────────
function statusLabel(string $status): string
{
    return match ($status) {
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'shipping' => 'Đang giao',
        'delivered' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
        default => 'Không xác định',
    };
}

function paymentStatusLabel(string $status): string
{
    return match ($status) {
        'paid' => 'Đã thanh toán',
        'refunded' => 'Hoàn tiền',
        default => 'Chưa thanh toán',
    };
}

function paymentMethodLabel(string $method): string
{
    return match ($method) {
        'cod' => 'Thanh toán khi nhận hàng',
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'momo' => 'Ví Momo',
        'vnpay' => 'VNPay',
        default => $method
    };
}

function productImageData(?string $data, ?string $mime): string
{
    if (!$data || !$mime)
        return '';
    return 'data:' . htmlspecialchars($mime) . ';base64,' . base64_encode($data);
}
?>

<!-- Flash banner -->
<?php if ($msg): ?>
    <?php
    $flash_map = [
        'updated' => ['success', 'Cập nhật trạng thái thành công.'],
        'pay_updated' => ['success', 'Cập nhật thanh toán thành công.'],
        'note_updated' => ['success', 'Lưu ghi chú thành công.'],
        'csrf' => ['error', 'Yêu cầu không hợp lệ.'],
    ];
    if (isset($flash_map[$msg])):
        [$type, $text] = $flash_map[$msg];
        ?>
        <div class="msg-banner <?= $type ?>">
            <i class="fa-solid fa-<?= $type === 'success' ? 'circle-check' : 'circle-exclamation' ?>"></i>
            <?= htmlspecialchars($text) ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Back button & header -->
<div class="detail-header">
    <a href="index.php?page=manageOrder" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Quay lại
    </a>
    <div>
        <h2
            style="font-family:'Playfair Display',serif;font-size:22px;font-weight:700;color:var(--admin-text);margin:0 0 4px;">
            Đơn hàng #<?= str_pad($order_id, 5, '0', STR_PAD_LEFT) ?>
        </h2>
        <p style="font-size:13px;color:var(--admin-muted);margin:0;">
            <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
        </p>
    </div>
</div>

<div class="detail-grid">
    <!-- Left column - Order & Customer info -->
    <div class="detail-column">
        <!-- Order Status Section -->
        <div class="detail-card">
            <h3 class="card-title">Thông tin đơn hàng</h3>

            <div class="info-group">
                <label>Trạng thái</label>
                <form method="POST" class="status-update-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="update_status">
                    <select name="status" class="input-select" onchange="this.form.submit()">
                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Chờ xác nhận
                        </option>
                        <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận
                        </option>
                        <option value="shipping" <?= $order['status'] === 'shipping' ? 'selected' : '' ?>>Đang giao
                        </option>
                        <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Hoàn thành
                        </option>
                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                </form>
            </div>

            <div class="info-group">
                <label>Thanh toán</label>
                <form method="POST" class="payment-update-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="update_payment_status">
                    <select name="payment_status" class="input-select" onchange="this.form.submit()">
                        <option value="unpaid" <?= $order['payment_status'] === 'unpaid' ? 'selected' : '' ?>>Chưa thanh
                            toán</option>
                        <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>Đã thanh toán
                        </option>
                        <option value="refunded" <?= $order['payment_status'] === 'refunded' ? 'selected' : '' ?>>Hoàn tiền
                        </option>
                    </select>
                </form>
            </div>

            <div class="info-group">
                <label>Phương thức thanh toán</label>
                <div class="info-value">
                    <?= htmlspecialchars(paymentMethodLabel($order['payment_method'])) ?>
                </div>
            </div>

            <div class="info-group">
                <label>Ngày tạo</label>
                <div class="info-value">
                    <?= date('d/m/Y H:i:s', strtotime($order['created_at'])) ?>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="detail-card">
            <h3 class="card-title">Thông tin khách hàng</h3>

            <div class="info-group">
                <label>Tên khách</label>
                <div class="info-value"><?= htmlspecialchars($order['full_name']) ?></div>
            </div>

            <div class="info-group">
                <label>Email</label>
                <div class="info-value">
                    <a href="mailto:<?= htmlspecialchars($order['email']) ?>">
                        <?= htmlspecialchars($order['email']) ?>
                    </a>
                </div>
            </div>

            <div class="info-group">
                <label>Số điện thoại</label>
                <div class="info-value">
                    <a href="tel:<?= htmlspecialchars($order['phone']) ?>">
                        <?= htmlspecialchars($order['phone']) ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Shipping Address -->
        <div class="detail-card">
            <h3 class="card-title">Địa chỉ giao hàng</h3>

            <div class="info-group">
                <label>Người nhận</label>
                <div class="info-value"><?= htmlspecialchars($order['recipient_name']) ?></div>
            </div>

            <div class="info-group">
                <label>Số điện thoại</label>
                <div class="info-value"><?= htmlspecialchars($order['addr_phone']) ?></div>
            </div>

            <div class="info-group">
                <label>Địa chỉ</label>
                <div class="info-value">
                    <?= htmlspecialchars($order['street']) ?><br>
                    <?php if ($order['district']): ?>
                        <?= htmlspecialchars($order['district']) ?>,
                    <?php endif; ?>
                    <?= htmlspecialchars($order['city']) ?>
                </div>
            </div>
        </div>

        <!-- Order Notes -->
        <div class="detail-card">
            <h3 class="card-title">Ghi chú đơn hàng</h3>
            <form method="POST" class="note-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="update_note">
                <textarea name="note" class="input-textarea"
                    placeholder="Thêm ghi chú cho đơn hàng..."><?= htmlspecialchars($order['note'] ?? '') ?></textarea>
                <button type="submit" class="btn btn-small">
                    <i class="fa-solid fa-save"></i> Lưu ghi chú
                </button>
            </form>
        </div>
    </div>

    <!-- Right column - Order items & total -->
    <div class="detail-column">
        <!-- Order Items -->
        <div class="detail-card">
            <h3 class="card-title">Sản phẩm trong đơn</h3>

            <?php if (empty($order_items)): ?>
                <div class="empty-items">
                    <i class="fa-solid fa-box-open"></i>
                    <p>Không có sản phẩm nào</p>
                </div>
            <?php else: ?>
                <div class="items-list">
                    <?php
                    $subtotal = 0;
                    foreach ($order_items as $item):
                        $item_total = (float) $item['unit_price'] * (int) $item['quantity'];
                        $subtotal += $item_total;
                        ?>
                        <div class="item">
                            <?php if ($item['image_data']): ?>
                                <img src="<?= productImageData($item['image_data'], $item['image_mime']) ?>"
                                    alt="<?= htmlspecialchars($item['name']) ?>" class="item-image">
                            <?php else: ?>
                                <div class="item-image-placeholder">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            <?php endif; ?>

                            <div class="item-info">
                                <a href="../../pages/product_detail.php?slug=<?= htmlspecialchars($item['slug']) ?>"
                                    class="item-name" target="_blank">
                                    <?= htmlspecialchars($item['name']) ?>
                                </a>
                                <?php if ($item['size']): ?>
                                    <span class="item-size">Size: <?= htmlspecialchars($item['size']) ?></span>
                                <?php endif; ?>
                                <div class="item-quantity">
                                    SL: <strong><?= (int) $item['quantity'] ?></strong>
                                </div>
                            </div>

                            <div class="item-pricing">
                                <div class="item-unit-price">
                                    <?= number_format((float) $item['unit_price'], 0, ',', '.') ?>đ
                                </div>
                                <div class="item-total-price">
                                    <?= number_format($item_total, 0, ',', '.') ?>đ
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Order Summary -->
        <div class="detail-card summary-card">
            <h3 class="card-title">Tóm tắt đơn hàng</h3>

            <div class="summary-row">
                <span>Tạm tính:</span>
                <span><?= number_format($subtotal, 0, ',', '.') ?>đ</span>
            </div>

            <div class="summary-row">
                <span>Phí vận chuyển:</span>
                <span>0đ</span>
            </div>

            <div class="summary-row">
                <span>Chiết khấu:</span>
                <span>0đ</span>
            </div>

            <div class="summary-divider"></div>

            <div class="summary-row total">
                <span>Tổng cộng:</span>
                <span><?= number_format((float) $order['total_amount'], 0, ',', '.') ?>đ</span>
            </div>

            <div class="payment-status-badge">
                <?php
                $pay_status = $order['payment_status'];
                $pay_label = paymentStatusLabel($pay_status);
                $pay_class = match ($pay_status) {
                    'paid' => 'success',
                    'refunded' => 'warning',
                    default => 'pending'
                };
                ?>
                <span class="badge <?= $pay_class ?>">
                    <?= htmlspecialchars($pay_label) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Set page CSS -->
<?php
$page_css = 'assets/css/orderDetail.css';
?>