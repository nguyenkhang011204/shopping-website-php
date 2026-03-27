<?php
// ── CSRF ───────────────────────────────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── POST: Update order status ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $msg = 'csrf';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_status') {
            $order_id = (int) ($_POST['order_id'] ?? 0);
            $status = $_POST['status'] ?? '';

            $valid_statuses = ['pending', 'confirmed', 'shipping', 'delivered', 'cancelled'];
            if ($order_id > 0 && in_array($status, $valid_statuses)) {
                $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $order_id]);
                $msg = 'updated';
            }
        }

        if ($action === 'update_payment_status') {
            $order_id = (int) ($_POST['order_id'] ?? 0);
            $pay_status = $_POST['payment_status'] ?? '';

            $valid_pay_statuses = ['unpaid', 'paid', 'refunded'];
            if ($order_id > 0 && in_array($pay_status, $valid_pay_statuses)) {
                $pdo->prepare("UPDATE orders SET payment_status = ? WHERE id = ?")->execute([$pay_status, $order_id]);
                $msg = 'pay_updated';
            }
        }
    }

    // Redirect to prevent form resubmission
    $query_string = '';
    if (isset($_GET['search']))
        $query_string .= '&search=' . urlencode($_GET['search']);
    if (isset($_GET['status']))
        $query_string .= '&status=' . urlencode($_GET['status']);
    if (isset($_GET['payment_status']))
        $query_string .= '&payment_status=' . urlencode($_GET['payment_status']);

    while (ob_get_level() > 0)
        ob_end_clean();
    header("Location: index.php?page=manageOrder" . $query_string . "&msg=" . $msg);
    exit;
}

// ── GET: Fetch filters ──────────────────────────────────────────────────
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';
$payment_filter = $_GET['payment_status'] ?? '';
$msg = $_GET['msg'] ?? '';

$valid_statuses = ['pending', 'confirmed', 'shipping', 'delivered', 'cancelled'];
$valid_pay_statuses = ['unpaid', 'paid', 'refunded'];

if ($status_filter && !in_array($status_filter, $valid_statuses)) {
    $status_filter = '';
}
if ($payment_filter && !in_array($payment_filter, $valid_pay_statuses)) {
    $payment_filter = '';
}

// ── Build query ────────────────────────────────────────────────────────
$params = [];
$where = [];
$sql = "SELECT o.id, o.user_id, o.status, o.total_amount, o.payment_method, 
               o.payment_status, o.created_at, u.full_name, u.email, u.phone,
               COUNT(oi.id) AS item_count
        FROM orders o
        JOIN users u ON o.user_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE 1=1";

if ($search !== '') {
    $where[] = "(u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR o.id LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($status_filter) {
    $where[] = "o.status = ?";
    $params[] = $status_filter;
}

if ($payment_filter) {
    $where[] = "o.payment_status = ?";
    $params[] = $payment_filter;
}

if ($where) {
    $sql .= " AND " . implode(" AND ", $where);
}

$sql .= " GROUP BY o.id ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// ── Flash messages ─────────────────────────────────────────────────────
$flash_map = [
    'updated' => ['success', 'Cập nhật trạng thái đơn hàng thành công.'],
    'pay_updated' => ['success', 'Cập nhật trạng thái thanh toán thành công.'],
    'error' => ['error', 'Có lỗi xảy ra. Vui lòng thử lại.'],
    'csrf' => ['error', 'Yêu cầu không hợp lệ.'],
];

// ── Helper functions ──────────────────────────────────────────────────
function orderStatusBadge(string $status): string
{
    return match ($status) {
        'pending' => '<span class="badge pending">Chờ xác nhận</span>',
        'confirmed' => '<span class="badge blue">Đã xác nhận</span>',
        'shipping' => '<span class="badge orange">Đang giao</span>',
        'delivered' => '<span class="badge success">Hoàn thành</span>',
        'cancelled' => '<span class="badge danger">Đã hủy</span>',
        default => '<span class="badge inactive">Không xác định</span>',
    };
}

function paymentStatusBadge(string $status): string
{
    return match ($status) {
        'paid' => '<span class="badge success">Đã thanh toán</span>',
        'refunded' => '<span class="badge pending">Hoàn tiền</span>',
        default => '<span class="badge inactive">Chưa thanh toán</span>',
    };
}

function paymentMethodBadge(string $method): string
{
    $label = match ($method) {
        'cod' => 'COD',
        'bank_transfer' => 'Chuyển khoản',
        'momo' => 'Momo',
        'vnpay' => 'VNPay',
        default => $method
    };
    return '<span class="method-badge">' . htmlspecialchars($label) . '</span>';
}

// ── Get view details link
function getOrderDetailsLink(int $order_id): string
{
    return 'index.php?page=order-detail&id=' . $order_id;
}
?>

<!-- Flash banner -->
<?php if ($msg && isset($flash_map[$msg])):
    [$type, $text] = $flash_map[$msg]; ?>
    <div class="msg-banner <?= $type ?>">
        <i class="fa-solid fa-<?= $type === 'success' ? 'circle-check' : 'circle-exclamation' ?>"></i>
        <?= htmlspecialchars($text) ?>
    </div>
<?php endif; ?>

<!-- Header section -->
<div style="margin-bottom: 24px;">
    <h2
        style="font-family:'Playfair Display',serif;font-size:22px;font-weight:700;color:var(--admin-text);margin: 0 0 4px;">
        <i class="fa-solid fa-shopping-bag" style="color: var(--brand-orange); margin-right: 10px;"></i>
        Quản lý đơn hàng
    </h2>
    <p style="font-size:13px;color:var(--admin-muted);margin:0;">
        Tổng số đơn hàng: <strong><?= count($orders) ?></strong>
    </p>
</div>

<!-- Toolbar - Search & Filters -->
<div class="page-toolbar">
    <form class="toolbar-search" method="GET" action="index.php" style="flex: 1;">
        <input type="hidden" name="page" value="manageOrder">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
            placeholder="Tìm tên khách, email, SĐT, mã đơn...">
        <button type="submit">Tìm</button>
    </form>

    <?php if ($search || $status_filter || $payment_filter): ?>
        <a href="index.php?page=manageOrder" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-times"></i> Xóa lọc
        </a>
    <?php endif; ?>
</div>

<!-- Filter tabs -->
<div class="filter-group">
    <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px;">
        <!-- Status Filter -->
        <div class="filter-select-wrapper">
            <form method="GET" action="index.php" class="filter-form">
                <input type="hidden" name="page" value="manageOrder">
                <?php if ($search): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                <?php endif; ?>
                <?php if ($payment_filter): ?>
                    <input type="hidden" name="payment_status" value="<?= htmlspecialchars($payment_filter) ?>">
                <?php endif; ?>
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Chờ xác nhận</option>
                    <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                    <option value="shipping" <?= $status_filter === 'shipping' ? 'selected' : '' ?>>Đang giao</option>
                    <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Hoàn thành</option>
                    <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                </select>
            </form>
        </div>

        <!-- Payment Status Filter -->
        <div class="filter-select-wrapper">
            <form method="GET" action="index.php" class="filter-form">
                <input type="hidden" name="page" value="manageOrder">
                <?php if ($search): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                <?php endif; ?>
                <?php if ($status_filter): ?>
                    <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                <?php endif; ?>
                <select name="payment_status" class="filter-select" onchange="this.form.submit()">
                    <option value="">-- Tất cả thanh toán --</option>
                    <option value="unpaid" <?= $payment_filter === 'unpaid' ? 'selected' : '' ?>>Chưa thanh toán</option>
                    <option value="paid" <?= $payment_filter === 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                    <option value="refunded" <?= $payment_filter === 'refunded' ? 'selected' : '' ?>>Hoàn tiền</option>
                </select>
            </form>
        </div>
    </div>
</div>

<!-- Orders table -->
<div class="table-container">
    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-inbox"></i>
            <h3>Không tìm thấy đơn hàng</h3>
            <p>Vui lòng thay đổi bộ lọc hoặc tìm kiếm lại.</p>
        </div>
    <?php else: ?>
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Liên hệ</th>
                    <th>Giá trị</th>
                    <th>Trạng thái</th>
                    <th>Thanh toán</th>
                    <th>Phương thức</th>
                    <th>Ngày</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr class="order-row">
                        <td class="order-id">
                            <a href="<?= getOrderDetailsLink((int) $order['id']) ?>" class="link-id">
                                #<?= str_pad((int) $order['id'], 5, '0', STR_PAD_LEFT) ?>
                            </a>
                        </td>
                        <td class="customer-name">
                            <strong><?= htmlspecialchars($order['full_name']) ?></strong>
                            <br>
                            <span class="text-muted"><?= htmlspecialchars($order['email']) ?></span>
                        </td>
                        <td class="customer-phone">
                            <?= htmlspecialchars($order['phone'] ?? '-') ?>
                        </td>
                        <td class="order-total">
                            <strong><?= number_format((float) $order['total_amount'], 0, ',', '.') ?>đ</strong>
                            <span class="item-count">(<?= (int) $order['item_count'] ?> sp)</span>
                        </td>
                        <td class="order-status">
                            <form method="POST" class="status-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                <select name="status" class="status-select" onchange="this.form.submit()">
                                    <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Chờ xác nhận
                                    </option>
                                    <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận
                                    </option>
                                    <option value="shipping" <?= $order['status'] === 'shipping' ? 'selected' : '' ?>>Đang giao
                                    </option>
                                    <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Hoàn thành
                                    </option>
                                    <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Đã hủy
                                    </option>
                                </select>
                            </form>
                        </td>
                        <td class="payment-status">
                            <form method="POST" class="payment-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="action" value="update_payment_status">
                                <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                <select name="payment_status" class="payment-select" onchange="this.form.submit()">
                                    <option value="unpaid" <?= $order['payment_status'] === 'unpaid' ? 'selected' : '' ?>>Chưa TT
                                    </option>
                                    <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>Đã TT
                                    </option>
                                    <option value="refunded" <?= $order['payment_status'] === 'refunded' ? 'selected' : '' ?>>Hoàn
                                        tiền</option>
                                </select>
                            </form>
                        </td>
                        <td class="payment-method">
                            <?= paymentMethodBadge($order['payment_method']) ?>
                        </td>
                        <td class="order-date">
                            <span class="date"><?= date('d/m/Y', strtotime($order['created_at'])) ?></span>
                            <span class="time"><?= date('H:i', strtotime($order['created_at'])) ?></span>
                        </td>
                        <td class="order-actions">
                            <a href="<?= getOrderDetailsLink((int) $order['id']) ?>" class="btn-icon" title="Xem chi tiết">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Set page CSS -->
<?php
$page_css = 'assets/css/manageOrder.css';
?>