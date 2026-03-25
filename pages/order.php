<?php
require_once '../includes/dbconnect.php';

$page_title = "Đơn hàng của tôi";
$page_css = "../assets/css/order.css";
$base_path = "../";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Require login ──
if (!isset($_SESSION['user_id'])) {
    header('Location: signIn.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// ── Get status filter from URL ──
$filterStatus = trim($_GET['status'] ?? '');
$validStatuses = ['pending', 'confirmed', 'shipping', 'delivered', 'cancelled'];
if ($filterStatus && !in_array($filterStatus, $validStatuses)) {
    $filterStatus = '';
}

// ── Pagination ──
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// ── Build query ──
$where = "WHERE o.user_id = ?";
$params = [$user_id];

if ($filterStatus) {
    $where .= " AND o.status = ?";
    $params[] = $filterStatus;
}

// ── Count total ──
$countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders o $where");
$countStmt->execute($params);
$totalCount = $countStmt->fetch()['total'];
$totalPages = ceil($totalCount / $perPage);

// Validate page
if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

// ── Get orders ──
$ordersStmt = $pdo->prepare(
    "SELECT o.id, o.status, o.total_amount, o.payment_status, o.created_at, COUNT(oi.id) as item_count
     FROM orders o
     LEFT JOIN order_items oi ON oi.order_id = o.id
     $where
     GROUP BY o.id
     ORDER BY o.created_at DESC
     LIMIT ? OFFSET ?"
);
$ordersStmt->execute([...$params, $perPage, $offset]);
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

// ── Helper functions ──
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
    'cancelled' => ['']
];

ob_start();
?>

<section class="orders-page">
    <div class="orders-container">

        <!-- Page header -->
        <div class="page-header">
            <h1>Đơn hàng của tôi</h1>
            <p><?= $totalCount ?> đơn hàng</p>
        </div>

        <!-- Filter tabs -->
        <div class="filter-tabs">
            <a href="order.php" class="tab <?= empty($filterStatus) ? 'active' : '' ?>">
                Tất cả
            </a>
            <?php foreach ($validStatuses as $status): ?>
                <a href="?status=<?= htmlspecialchars($status) ?>"
                    class="tab <?= $filterStatus === $status ? 'active' : '' ?>">
                    <?= htmlspecialchars($statusLabels[$status][0]) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Orders list or empty state -->
        <?php if (empty($orders)): ?>

            <div class="empty-state">
                <i class="fa-solid fa-bag-shopping"></i>
                <h2>Không có đơn hàng</h2>
                <p><?= $filterStatus ? 'Không có đơn hàng với trạng thái này.' : 'Bạn chưa có đơn hàng nào.' ?></p>
                <a href="<?= $base_path ?>pages/product.php" class="btn-primary">Bắt đầu mua sắm</a>
            </div>

        <?php else: ?>

            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-card-header">
                            <div class="order-id-date">
                                <h3>Mã đơn hàng: #<?= (int) $order['id'] ?></h3>
                                <p><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                            </div>
                            <div class="order-badges">
                                <?php if ($order['status'] === 'cancelled'): ?>
                                    <span class="badge status" style="background-color: <?= $statusLabels[$order['status']][1] ?>">
                                        <?= $statusLabels[$order['status']][0] ?>
                                    </span>
                                <?php elseif ($order['status'] === 'pending'): ?>
                                    <span class="badge status" style="background-color: <?= $statusLabels[$order['status']][1] ?>">
                                        <?= $statusLabels[$order['status']][0] ?>
                                    </span>
                                    <span class="badge payment"
                                        style="background-color: <?= $paymentStatusLabels[$order['payment_status']][1] ?>">
                                        <?= $paymentStatusLabels[$order['payment_status']][0] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge status" style="background-color: <?= $statusLabels[$order['status']][1] ?>">
                                        <?= $statusLabels[$order['status']][0] ?>
                                    </span>
                                    <span class="badge payment"
                                        style="background-color: <?= $paymentStatusLabels[$order['payment_status']][1] ?>">
                                        <?= $paymentStatusLabels[$order['payment_status']][0] ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="order-card-body">
                            <div class="order-info">
                                <p>
                                    <strong><?= (int) $order['item_count'] ?> sản phẩm</strong>
                                    • Tổng tiền: <strong><?= formatCurrencyVND($order['total_amount']) ?></strong>
                                </p>
                            </div>
                        </div>

                        <div class="order-card-footer">
                            <a href="order_detail.php?id=<?= (int) $order['id'] ?>" class="btn-detail">
                                <i class="fa-solid fa-arrow-right"></i> Chi tiết
                            </a>
                            <?php if ($order['status'] === 'pending'): ?>
                                <button class="btn-cancel-small" onclick="cancelOrder(<?= (int) $order['id'] ?>)">
                                    <i class="fa-solid fa-x"></i> Hủy đơn
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?= $filterStatus ? '&status=' . htmlspecialchars($filterStatus) : '' ?>"
                            class="page-link">Trang đầu</a>
                        <a href="?page=<?= $page - 1 ?><?= $filterStatus ? '&status=' . htmlspecialchars($filterStatus) : '' ?>"
                            class="page-link">← Trước</a>
                    <?php endif; ?>

                    <div class="page-numbers">
                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);

                        if ($startPage > 1) {
                            echo '<span class="page-link">...</span>';
                        }

                        for ($i = $startPage; $i <= $endPage; $i++) {
                            $active = ($i === $page) ? 'active' : '';
                            $statusParam = $filterStatus ? '&status=' . htmlspecialchars($filterStatus) : '';
                            echo '<a href="?page=' . $i . $statusParam . '" class="page-link ' . $active . '">' . $i . '</a>';
                        }

                        if ($endPage < $totalPages) {
                            echo '<span class="page-link">...</span>';
                        }
                        ?>
                    </div>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?><?= $filterStatus ? '&status=' . htmlspecialchars($filterStatus) : '' ?>"
                            class="page-link">Sau →</a>
                        <a href="?page=<?= $totalPages ?><?= $filterStatus ? '&status=' . htmlspecialchars($filterStatus) : '' ?>"
                            class="page-link">Trang cuối</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div><!-- /orders-container -->
</section>

<script>
    function cancelOrder(orderId) {
        if (confirm('Bạn có chắc chắn muốn hủy đơn hàng này không? Hành động này không thể hoàn tác.')) {
            const formData = new FormData();
            formData.append('order_id', orderId);

            fetch('cancel_order.php', { //Gửi request POST tới file cancel_order.php để xử lý hủy đơn hàng
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) { //
                        alert('Đơn hàng đã hủy thành công');
                        location.reload();
                    } else {
                        alert('Lỗi: ' + (data.message || 'Không thể hủy đơn hàng'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra');
                });
        }
    }
</script>

<?php
$page_content = ob_get_clean();
include('../includes/layout.php');
?>