<?php
require_once '../includes/dbconnect.php';

$page_title = "Thanh toán";
$page_css = "../assets/css/checkout.css";
$base_path = "../";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Require login ──
if (!isset($_SESSION['user_id'])) {
    $_SESSION['checkout_redirect'] = true;
    header('Location: signIn.php');
    exit;
}

// ── Functions ──
if (!function_exists('formatCurrencyVND')) {
    function formatCurrencyVND(float $value): string {
        return number_format($value, 0, ',', '.') . 'đ';
    }
}

$user_id = (int) $_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['user_name'] ?? '');
$user_email = htmlspecialchars($_SESSION['user_email'] ?? '');
$user_phone = htmlspecialchars($_SESSION['user_phone'] ?? '');

$cartMessage = '';
$cartItems = [];
$subTotal = 0;

// ── Get cart items ──
$cartIds = array_map('intval', array_keys($_SESSION['cart'] ?? []));
$cartIds = array_values(array_filter($cartIds, fn($id) => $id > 0));

if (empty($cartIds)) {
    header('Location: cart.php');
    exit;
}

$placeholders = implode(',', array_fill(0, count($cartIds), '?'));
$stmt = $pdo->prepare(
    "SELECT id, name, price, stock
     FROM products
     WHERE is_active = 1 AND id IN ($placeholders)"
);
$stmt->execute($cartIds);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$productMap = [];
foreach ($products as $product) {
    $productMap[(int) $product['id']] = $product;
}

foreach ($cartIds as $id) {
    if (!isset($productMap[$id])) {
        unset($_SESSION['cart'][$id]);
        continue;
    }

    $product = $productMap[$id];
    $requestedQty = (int) $_SESSION['cart'][$id];
    $stock = (int) $product['stock'];

    if ($stock <= 0) {
        unset($_SESSION['cart'][$id]);
        continue;
    }

    $qty = max(1, min($requestedQty, $stock, 99));
    $_SESSION['cart'][$id] = $qty;

    $lineTotal = (float) $product['price'] * $qty;
    $subTotal += $lineTotal;

    $cartItems[] = [
        'id' => $id,
        'name' => $product['name'],
        'price' => (float) $product['price'],
        'stock' => $stock,
        'qty' => $qty,
        'line_total' => $lineTotal,
    ];
}

// ── Get user addresses ──
$addrStmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC");
$addrStmt->execute([$user_id]);
$addresses = $addrStmt->fetchAll(PDO::FETCH_ASSOC);

$defaultAddress = null;
foreach ($addresses as $addr) {
    if ($addr['is_default']) {
        $defaultAddress = $addr;
        break;
    }
}

// ── Handle POST (place order) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address_id = (int) ($_POST['address_id'] ?? 0);
    $payment_method = trim($_POST['payment_method'] ?? 'cod');
    $note = trim($_POST['note'] ?? '');

    // Validate
    if ($address_id <= 0) {
        $cartMessage = 'Vui lòng chọn địa chỉ giao hàng.';
    } elseif (!in_array($payment_method, ['cod', 'bank_transfer', 'momo', 'vnpay'])) {
        $cartMessage = 'Phương thức thanh toán không hợp lệ.';
    } else {
        // Check address belongs to user
        $addrCheck = $pdo->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ?");
        $addrCheck->execute([$address_id, $user_id]);
        if (!$addrCheck->fetch()) {
            $cartMessage = 'Địa chỉ không hợp lệ.';
        } else {
            // Create order
            $shippingFee = 30000;
            $totalAmount = $subTotal + $shippingFee;

            try {
                $pdo->beginTransaction();

                $orderStmt = $pdo->prepare(
                    "INSERT INTO orders (user_id, address_id, status, total_amount, payment_method, payment_status, note)
                     VALUES (?, ?, 'pending', ?, ?, 'unpaid', ?)"
                );
                $orderStmt->execute([$user_id, $address_id, $totalAmount, $payment_method, $note]);
                $order_id = $pdo->lastInsertId();

                // Insert order items
                $itemStmt = $pdo->prepare(
                    "INSERT INTO order_items (order_id, product_id, quantity, unit_price)
                     VALUES (?, ?, ?, ?)"
                );
                foreach ($cartItems as $item) {
                    $itemStmt->execute([$order_id, $item['id'], $item['qty'], $item['price']]);
                }

                // Clear cart
                $_SESSION['cart'] = [];
                
                $pdo->commit();

                // Redirect to order detail
                header('Location: order_detail.php?id=' . $order_id);
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $cartMessage = 'Có lỗi khi tạo đơn hàng. Vui lòng thử lại.';
            }
        }
    }
}

ob_start();
?>

<section class="checkout-page">
    <div class="checkout-container">
        
        <!-- Left: Delivery & Payment Info -->
        <div class="checkout-form">
            <?php if ($cartMessage !== ''): ?>
                <div class="checkout-alert"><?= htmlspecialchars($cartMessage) ?></div>
            <?php endif; ?>

            <form method="post" class="checkout-form-wrapper">
                
                <!-- Shipping Address Section -->
                <div class="form-section">
                    <h2>Địa chỉ giao hàng</h2>
                    
                    <?php if (empty($addresses)): ?>
                        <p style="color: #666; margin-bottom: 1rem;">Bạn chưa có địa chỉ nào. 
                            <a href="<?= $base_path ?>pages/profile.php" style="color: #f4a62a; text-decoration: underline;">Thêm địa chỉ</a>
                        </p>
                    <?php else: ?>
                        <div class="address-list">
                            <?php foreach ($addresses as $addr): ?>
                                <label class="address-radio">
                                    <input type="radio" name="address_id" value="<?= (int) $addr['id'] ?>"
                                        <?= ($defaultAddress && $addr['id'] == $defaultAddress['id']) ? 'checked' : '' ?> required>
                                    <div class="address-info">
                                        <strong><?= htmlspecialchars($addr['recipient_name']) ?></strong> | <?= htmlspecialchars($addr['phone']) ?>
                                        <p><?= htmlspecialchars($addr['street'] . ', ' . $addr['district'] . ', ' . $addr['city']) ?></p>
                                        <?php if ($addr['is_default']): ?>
                                            <span class="badge-default">Mặc định</span>
                                        <?php endif; ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Payment Method Section -->
                <div class="form-section">
                    <h2>Phương thức thanh toán</h2>
                    <div class="payment-methods">
                        <label class="payment-radio">
                            <input type="radio" name="payment_method" value="cod" checked required>
                            <div class="payment-info">
                                <strong>Thanh toán khi nhận hàng (COD)</strong>
                                <p>Bạn có thể thanh toán tiền mặt khi nhận hàng</p>
                            </div>
                        </label>
                        <label class="payment-radio">
                            <input type="radio" name="payment_method" value="bank_transfer">
                            <div class="payment-info">
                                <strong>Chuyển khoản ngân hàng</strong>
                                <p>Chuyển khoản vào tài khoản của cửa hàng</p>
                            </div>
                        </label>
                        <label class="payment-radio">
                            <input type="radio" name="payment_method" value="momo">
                            <div class="payment-info">
                                <strong>Ví MoMo</strong>
                                <p>Thanh toán qua ứng dụng MoMo</p>
                            </div>
                        </label>
                        <label class="payment-radio">
                            <input type="radio" name="payment_method" value="vnpay">
                            <div class="payment-info">
                                <strong>VN Pay</strong>
                                <p>Thanh toán qua cổng VN Pay</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="form-section">
                    <h2>Ghi chú đơn hàng (tùy chọn)</h2>
                    <textarea name="note" placeholder="Nhập ghi chú cho đơn hàng của bạn..." rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit;"></textarea>
                </div>

                <button type="submit" class="btn-checkout">Đặt hàng</button>
            </form>
        </div>

        <!-- Right: Order Summary -->
        <aside class="checkout-summary">
            <h3>Tóm tắt đơn hàng</h3>
            
            <div class="summary-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="summary-item">
                        <div class="summary-item-info">
                            <p class="item-name"><?= htmlspecialchars($item['name']) ?></p>
                            <p class="item-qty">x<?= (int) $item['qty'] ?></p>
                        </div>
                        <p class="item-price"><?= formatCurrencyVND($item['line_total']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="summary-divider"></div>

            <div class="summary-row">
                <span>Tạm tính:</span>
                <strong><?= formatCurrencyVND($subTotal) ?></strong>
            </div>
            <div class="summary-row">
                <span>Phí vận chuyển:</span>
                <strong><?= formatCurrencyVND(30000) ?></strong>
            </div>

            <div class="summary-divider"></div>

            <div class="summary-total">
                <span>Tổng cộng:</span>
                <strong><?= formatCurrencyVND($subTotal + 30000) ?></strong>
            </div>

            <a href="cart.php" class="btn-back-cart">← Quay lại giỏ hàng</a>
        </aside>

    </div>
</section>

<?php
$page_content = ob_get_clean();
include('../includes/layout.php');
?>