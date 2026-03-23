<?php
// ── Real data from DB ─────────────────────────────────────────────────────────

// Stat cards
$orders_this_month = (int) $pdo->query(
    "SELECT COUNT(*) FROM orders
     WHERE MONTH(created_at) = MONTH(CURDATE())
       AND YEAR(created_at)  = YEAR(CURDATE())"
)->fetchColumn();

$revenue_total = (float) $pdo->query(
    "SELECT COALESCE(SUM(total_amount), 0) FROM orders
     WHERE status = 'delivered' AND payment_status = 'paid'"
)->fetchColumn();

$products_active = (int) $pdo->query(
    "SELECT COUNT(*) FROM products WHERE is_active = 1"
)->fetchColumn();

$customers_total = (int) $pdo->query(
    "SELECT COUNT(*) FROM users WHERE role = 'customer' AND is_active = 1"
)->fetchColumn();

// Revenue chart — last 7 days (delivered + paid)
$chart_labels = [];
$chart_map    = [];
for ($i = 6; $i >= 0; $i--) {
    $date           = date('Y-m-d', strtotime("-{$i} days"));
    $chart_labels[] = date('d/m', strtotime("-{$i} days"));
    $chart_map[$date] = 0;
}

$stmt = $pdo->query(
    "SELECT DATE(created_at) AS day, SUM(total_amount) AS rev
     FROM orders
     WHERE status = 'delivered'
       AND payment_status = 'paid'
       AND created_at >= CURDATE() - INTERVAL 6 DAY
     GROUP BY DATE(created_at)"
);
foreach ($stmt->fetchAll() as $row) {
    if (array_key_exists($row['day'], $chart_map)) {
        $chart_map[$row['day']] = (float) $row['rev'];
    }
}
$chart_values = array_values($chart_map);

// Recent orders — last 10
$recent_orders = $pdo->query(
    "SELECT o.id, u.full_name, o.total_amount, o.status, o.payment_status, o.created_at
     FROM orders o
     JOIN users u ON o.user_id = u.id
     ORDER BY o.created_at DESC
     LIMIT 10"
)->fetchAll();

// Pending orders count (needs attention)
$pending_count = (int) $pdo->query(
    "SELECT COUNT(*) FROM orders WHERE status = 'pending'"
)->fetchColumn();

// Low-stock products (stock < 5, active)
$low_stock = $pdo->query(
    "SELECT name, stock FROM products WHERE stock < 5 AND is_active = 1 ORDER BY stock ASC LIMIT 5"
)->fetchAll();

// Helper: order status badge class + label
function orderBadge(string $status): string
{
    return match ($status) {
        'pending'   => '<span class="badge pending">Chờ xác nhận</span>',
        'confirmed' => '<span class="badge blue">Đã xác nhận</span>',
        'shipping'  => '<span class="badge orange">Đang giao</span>',
        'delivered' => '<span class="badge success">Hoàn thành</span>',
        'cancelled' => '<span class="badge danger">Đã hủy</span>',
        default     => '<span class="badge inactive">' . htmlspecialchars($status) . '</span>',
    };
}

function payBadge(string $status): string
{
    return match ($status) {
        'paid'     => '<span class="badge success">Đã TT</span>',
        'refunded' => '<span class="badge pending">Hoàn tiền</span>',
        default    => '<span class="badge inactive">Chưa TT</span>',
    };
}
?>

<!-- ── Greeting ─────────────────────────────────────────────────────────────── -->
<div style="margin-bottom:24px;">
    <h2 style="font-family:'Playfair Display',serif;font-size:22px;font-weight:700;color:var(--admin-text);margin:0 0 4px;">
        Xin chào, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?>! 👋
    </h2>
    <p style="font-size:13px;color:var(--admin-muted);margin:0;">
        <?= date('l, d/m/Y') ?> — Đây là tổng quan hoạt động của cửa hàng.
    </p>
</div>

<!-- ── Stat cards ───────────────────────────────────────────────────────────── -->
<div class="cards">

    <div class="card">
        <div class="card-top">
            <h4>Đơn hàng tháng này</h4>
            <i class="fa-solid fa-cart-shopping blue"></i>
        </div>
        <h2><?= number_format($orders_this_month) ?></h2>
        <?php if ($pending_count > 0): ?>
            <span style="font-size:12px;color:#d97706;">
                <i class="fa-solid fa-clock"></i>
                <?= $pending_count ?> đơn chờ xác nhận
            </span>
        <?php else: ?>
            <span style="font-size:12px;color:var(--admin-muted);">Không có đơn chờ</span>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-top">
            <h4>Doanh thu (đã giao)</h4>
            <i class="fa-solid fa-chart-line green"></i>
        </div>
        <h2><?= $revenue_total >= 1_000_000
                ? number_format($revenue_total / 1_000_000, 1) . 'M'
                : number_format($revenue_total / 1_000, 0) . 'K'
            ?></h2>
        <span style="font-size:12px;color:var(--admin-muted);">Tất cả thời gian</span>
    </div>

    <div class="card">
        <div class="card-top">
            <h4>Sản phẩm đang bán</h4>
            <i class="fa-solid fa-shirt orange"></i>
        </div>
        <h2><?= number_format($products_active) ?></h2>
        <?php if (count($low_stock) > 0): ?>
            <span style="font-size:12px;color:#e05c5c;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <?= count($low_stock) ?> sản phẩm sắp hết hàng
            </span>
        <?php else: ?>
            <span style="font-size:12px;color:var(--admin-muted);">Tồn kho bình thường</span>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-top">
            <h4>Khách hàng</h4>
            <i class="fa-solid fa-users" style="background:#f3e8ff;color:#7c3aed;font-size:20px;padding:10px;border-radius:10px;"></i>
        </div>
        <h2><?= number_format($customers_total) ?></h2>
        <span style="font-size:12px;color:var(--admin-muted);">Đang hoạt động</span>
    </div>

</div>

<!-- ── Chart + Low stock panel ───────────────────────────────────────────────── -->
<div style="display:grid;grid-template-columns:1fr 280px;gap:24px;margin-bottom:28px;" class="dash-grid">

    <!-- Revenue chart -->
    <div class="chart" style="margin-bottom:0;">
        <h3>Doanh thu 7 ngày gần nhất</h3>
        <canvas id="revenueChart" height="90"></canvas>
    </div>

    <!-- Low stock / alerts -->
    <div class="table-container" style="padding:20px;">
        <div class="table-header" style="margin-bottom:12px;">
            <h3>Sắp hết hàng</h3>
        </div>
        <?php if (empty($low_stock)): ?>
            <p style="font-size:13px;color:var(--admin-muted);text-align:center;padding:20px 0;">
                <i class="fa-solid fa-circle-check" style="color:#16a34a;display:block;font-size:28px;margin-bottom:8px;"></i>
                Tất cả sản phẩm<br>còn hàng đầy đủ
            </p>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <?php foreach ($low_stock as $ls): ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;
                            padding:10px 12px;background:#fff9f0;border-radius:8px;
                            border-left:3px solid #f4a62a;">
                        <span style="font-size:13px;font-weight:500;color:var(--admin-text);
                                 white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
                                 max-width:160px;" title="<?= htmlspecialchars($ls['name']) ?>">
                            <?= htmlspecialchars($ls['name']) ?>
                        </span>
                        <span style="font-size:12px;font-weight:700;
                                 color:<?= $ls['stock'] === 0 ? '#dc2626' : '#d97706' ?>;">
                            <?= $ls['stock'] === 0 ? 'Hết' : $ls['stock'] . ' còn' ?>
                        </span>
                    </div>
                <?php endforeach; ?>
                <a href="index.php?page=products" style="font-size:12px;color:var(--brand-orange);
                   text-decoration:none;text-align:center;margin-top:4px;font-weight:600;">
                    Xem tất cả sản phẩm →
                </a>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ── Recent orders table ───────────────────────────────────────────────────── -->
<div class="table-container">
    <div class="table-header">
        <h3>Đơn hàng gần đây</h3>
        <a href="index.php?page=orders" class="btn btn-outline btn-sm">Xem tất cả</a>
    </div>

    <?php if (empty($recent_orders)): ?>
        <p style="text-align:center;padding:32px;color:var(--admin-muted);">Chưa có đơn hàng nào.</p>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thanh toán</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $o): ?>
                        <tr>
                            <td style="color:var(--admin-muted);font-size:12px;">#<?= $o['id'] ?></td>
                            <td style="font-weight:600;"><?= htmlspecialchars($o['full_name']) ?></td>
                            <td style="font-weight:600;white-space:nowrap;">
                                <?= number_format((float)$o['total_amount'], 0, ',', '.') ?> VNĐ
                            </td>
                            <td><?= orderBadge($o['status']) ?></td>
                            <td><?= payBadge($o['payment_status']) ?></td>
                            <td style="font-size:12px;color:var(--admin-muted);white-space:nowrap;">
                                <?= date('d/m/Y H:i', strtotime($o['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
    (function() {
        const labels = <?= json_encode($chart_labels) ?>;
        const values = <?= json_encode($chart_values) ?>;

        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: values,
                    backgroundColor: 'rgba(244,166,42,0.25)',
                    borderColor: '#f4a62a',
                    borderWidth: 2,
                    borderRadius: 6,
                    hoverBackgroundColor: 'rgba(244,166,42,0.45)',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ' + ctx.parsed.y.toLocaleString('vi-VN') + ' VNĐ'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        },
                        ticks: {
                            font: {
                                family: 'DM Sans',
                                size: 11
                            },
                            color: '#9ca3af',
                            callback: v => v >= 1_000_000 ?
                                (v / 1_000_000).toFixed(1) + 'M' :
                                v >= 1_000 ? (v / 1_000).toFixed(0) + 'K' : v
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'DM Sans',
                                size: 11
                            },
                            color: '#9ca3af'
                        }
                    }
                }
            }
        });
    })();
</script>

<style>
    /* Responsive: stack chart + panel on smaller screens */
    @media (max-width: 900px) {
        .dash-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>