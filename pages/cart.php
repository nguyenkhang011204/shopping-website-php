<?php
require_once '../includes/dbconnect.php';

$page_title = "Giỏ Hàng";
$page_css = "../assets/css/cart.css";
$base_path = "../";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!function_exists('formatCurrencyVND')) {
    function formatCurrencyVND(float $value): string
    {
        return number_format($value, 0, ',', '.') . 'đ';
    }
}

$cartMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_id'])) {
        $id = (int) $_POST['remove_id'];
        if ($id > 0) {
            unset($_SESSION['cart'][$id]);
            $cartMessage = 'Đã xóa sản phẩm khỏi giỏ hàng.';
        }
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'update' && isset($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $productId => $qty) {
            $id = (int) $productId;
            $quantity = (int) $qty;

            if ($id <= 0) {
                continue;
            }

            if ($quantity <= 0) {
                unset($_SESSION['cart'][$id]);
                continue;
            }

            $_SESSION['cart'][$id] = min(99, $quantity);
        }

        $cartMessage = 'Đã cập nhật giỏ hàng.';
    }

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        $cartMessage = 'Đã xóa toàn bộ giỏ hàng.';
    }
}

if (($_GET['action'] ?? '') === 'add') {
    $id = (int) ($_GET['id'] ?? 0);
    $qty = max(1, (int) ($_GET['qty'] ?? 1));

    if ($id > 0) {
        $currentQty = (int) ($_SESSION['cart'][$id] ?? 0);
        $_SESSION['cart'][$id] = min(99, $currentQty + $qty);
        $cartMessage = 'Đã thêm sản phẩm vào giỏ hàng.';
    }
}

$cartItems = [];
$subTotal = 0;

$cartIds = array_map('intval', array_keys($_SESSION['cart']));
$cartIds = array_values(array_filter($cartIds, fn($id) => $id > 0));

if (!empty($cartIds)) {
    $placeholders = implode(',', array_fill(0, count($cartIds), '?'));
    $stmt = $pdo->prepare(
        "SELECT id, name, price, image, stock
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
            'image' => $product['image'],
            'price' => (float) $product['price'],
            'stock' => $stock,
            'qty' => $qty,
            'line_total' => $lineTotal,
        ];
    }
}

$shippingFee = empty($cartItems) ? 0 : 30000;
$discount = 0;
$grandTotal = $subTotal + $shippingFee - $discount;

ob_start();
?>

<section class="cart-page">
    <div class="cart-header">
        <h1>Giỏ hàng của bạn</h1>
        <p><?= count($cartItems) ?> sản phẩm</p>
    </div>

    <?php if ($cartMessage !== ''): ?>
        <div class="cart-alert"><?= htmlspecialchars($cartMessage) ?></div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
        <div class="cart-empty">
            <i class="fa-solid fa-bag-shopping"></i>
            <h2>Giỏ hàng đang trống</h2>
            <p>Thêm sản phẩm để bắt đầu đơn hàng của bạn.</p>
            <a href="product.php" class="btn-primary">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <div class="cart-grid">
            <form method="post" class="cart-table-wrap">
                <input type="hidden" name="action" value="update">

                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Tạm tính</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td>
                                    <div class="cart-product">
                                        <img src="<?= htmlspecialchars($item['image']) ?>"
                                            alt="<?= htmlspecialchars($item['name']) ?>"
                                            onerror="this.src='../assets/images/placeholder.png'">
                                        <div>
                                            <a href="product_detail.php?id=<?= (int) $item['id'] ?>">
                                                <?= htmlspecialchars($item['name']) ?>
                                            </a>
                                            <small>Còn <?= (int) $item['stock'] ?> sản phẩm</small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= formatCurrencyVND($item['price']) ?></td>
                                <td>
                                    <input type="number" name="qty[<?= (int) $item['id'] ?>]" min="1"
                                        max="<?= (int) $item['stock'] ?>" value="<?= (int) $item['qty'] ?>" class="qty-input">
                                </td>
                                <td class="line-total"><?= formatCurrencyVND($item['line_total']) ?></td>
                                <td>
                                    <button type="submit" name="remove_id" value="<?= (int) $item['id'] ?>" class="btn-remove"
                                        formnovalidate>
                                        Xóa
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="cart-actions">
                    <a href="product.php" class="btn-secondary">Tiếp tục mua sắm</a>
                    <div class="cart-actions-right">
                        <button type="submit" class="btn-secondary">Cập nhật giỏ hàng</button>
                        <button type="submit" name="action" value="clear" class="btn-danger">Xóa tất cả</button>
                    </div>
                </div>
            </form>

            <aside class="cart-summary">
                <h3>Tóm tắt đơn hàng</h3>

                <div class="summary-row">
                    <span>Tạm tính</span>
                    <strong><?= formatCurrencyVND($subTotal) ?></strong>
                </div>
                <div class="summary-row">
                    <span>Phí vận chuyển</span>
                    <strong><?= formatCurrencyVND($shippingFee) ?></strong>
                </div>
                <div class="summary-row">
                    <span>Giảm giá</span>
                    <strong>-<?= formatCurrencyVND($discount) ?></strong>
                </div>

                <div class="summary-total">
                    <span>Tổng thanh toán</span>
                    <strong><?= formatCurrencyVND($grandTotal) ?></strong>
                </div>

                <a href="checkout.php" class="btn-primary btn-checkout">Tiến hành thanh toán</a>
            </aside>
        </div>
    <?php endif; ?>

</section>

<?php

$page_content = ob_get_clean();


include('../includes/layout.php');
?>