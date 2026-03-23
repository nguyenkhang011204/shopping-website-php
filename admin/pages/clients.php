<?php
// ── CSRF ─────────────────────────────────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── POST handler ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        while (ob_get_level() > 0) ob_end_clean();
        header("Location: index.php?page=clients&msg=csrf");
        exit;
    }

    $action = $_POST['action'] ?? '';

    // ── Toggle active ─────────────────────────────────────────────────────────
    if ($action === 'toggle_active') {
        $id = (int)($_POST['user_id'] ?? 0);
        if ($id > 0 && $id !== (int)$_SESSION['user_id']) { // cannot lock yourself
            $pdo->prepare("UPDATE users SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
        }
        while (ob_get_level() > 0) ob_end_clean();
        header("Location: index.php?page=clients&msg=updated");
        exit;
    }
}

// ── GET: fetch data ───────────────────────────────────────────────────────────
$search = trim($_GET['search'] ?? '');
$msg    = $_GET['msg']         ?? '';

$params = [];
$where  = ["u.role = 'customer'"];
$sql    = "SELECT u.id, u.full_name, u.email, u.phone, u.is_active, u.created_at,
                  COUNT(o.id) AS order_count
           FROM users u
           LEFT JOIN orders o ON o.user_id = u.id";

if ($search !== '') {
    $where[] = "(u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$sql .= " WHERE " . implode(" AND ", $where);
$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clients = $stmt->fetchAll();

// Stats
$total_active   = array_filter($clients, fn($c) => $c['is_active']);
$total_inactive = array_filter($clients, fn($c) => !$c['is_active']);

// ── Flash messages ────────────────────────────────────────────────────────────
$flash_map = [
    'updated' => ['success', 'Cập nhật trạng thái tài khoản thành công.'],
    'csrf'    => ['error',   'Yêu cầu không hợp lệ.'],
];
?>

<!-- Flash banner -->
<?php if ($msg && isset($flash_map[$msg])): [$type, $text] = $flash_map[$msg]; ?>
<div class="msg-banner <?= $type ?>">
    <i class="fa-solid fa-<?= $type === 'success' ? 'circle-check' : 'circle-exclamation' ?>"></i>
    <?= htmlspecialchars($text) ?>
</div>
<?php endif; ?>

<!-- Stats mini-cards -->
<div class="cards" style="margin-bottom:24px;">
    <div class="card">
        <h4>Tổng khách hàng</h4>
        <h2><?= count($clients) ?></h2>
    </div>
    <div class="card">
        <h4>Đang hoạt động</h4>
        <h2><?= count($total_active) ?></h2>
    </div>
    <div class="card">
        <h4>Bị khóa</h4>
        <h2><?= count($total_inactive) ?></h2>
    </div>
</div>

<!-- Toolbar -->
<div class="page-toolbar">
    <form class="toolbar-search" method="GET" action="index.php">
        <input type="hidden" name="page" value="clients">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
               placeholder="Tìm tên, email, SĐT...">
        <button type="submit">Tìm</button>
    </form>

    <?php if ($search): ?>
        <a href="index.php?page=clients" class="btn btn-outline btn-sm">Xóa lọc</a>
    <?php endif; ?>
</div>

<!-- Clients table -->
<div class="table-container">
    <div class="table-header">
        <h3>Khách hàng
            <span style="font-size:13px;font-weight:400;color:var(--admin-muted);margin-left:8px;">
                (<?= count($clients) ?> kết quả)
            </span>
        </h3>
    </div>

    <div style="overflow-x:auto;">
    <table id="clientsTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Khách hàng</th>
                <th>Email</th>
                <th>Số điện thoại</th>
                <th style="text-align:center;">Đơn hàng</th>
                <th>Trạng thái</th>
                <th>Ngày đăng ký</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($clients)): ?>
            <tr><td colspan="8" style="text-align:center;padding:32px;color:var(--admin-muted);">
                Không tìm thấy khách hàng nào.
            </td></tr>
        <?php else: ?>
            <?php foreach ($clients as $c): ?>
            <?php
                $initial = mb_strtoupper(mb_substr($c['full_name'] ?? '?', 0, 1, 'UTF-8'), 'UTF-8');
            ?>
            <tr>
                <td style="color:var(--admin-muted);font-size:12px;"><?= $c['id'] ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div class="user-initials"><?= $initial ?></div>
                        <span style="font-weight:600;">
                            <?= htmlspecialchars($c['full_name'] ?? '—') ?>
                        </span>
                    </div>
                </td>
                <td><?= htmlspecialchars($c['email']) ?></td>
                <td><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
                <td style="text-align:center;">
                    <span class="badge <?= (int)$c['order_count'] > 0 ? 'active-badge' : 'inactive' ?>">
                        <?= (int)$c['order_count'] ?>
                    </span>
                </td>
                <td>
                    <?php if ($c['is_active']): ?>
                        <span class="badge success">Hoạt động</span>
                    <?php else: ?>
                        <span class="badge danger">Bị khóa</span>
                    <?php endif; ?>
                </td>
                <td style="white-space:nowrap;font-size:12px;color:var(--admin-muted);">
                    <?= date('d/m/Y', strtotime($c['created_at'])) ?>
                </td>
                <td>
                    <?php if ($c['id'] != $_SESSION['user_id']): ?>
                    <form method="POST" action="index.php?page=clients" class="inline-form"
                          onsubmit="return confirm('<?= $c['is_active']
                              ? 'Khóa tài khoản này?'
                              : 'Mở khóa tài khoản này?' ?>')">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="toggle_active">
                        <input type="hidden" name="user_id" value="<?= (int)$c['id'] ?>">
                        <button type="submit"
                                class="btn btn-sm <?= $c['is_active'] ? 'btn-danger' : 'btn-outline' ?>">
                            <?= $c['is_active'] ? '<i class="fa-solid fa-lock"></i> Khóa'
                                                : '<i class="fa-solid fa-lock-open"></i> Mở khóa' ?>
                        </button>
                    </form>
                    <?php else: ?>
                        <span class="text-muted" style="font-size:11px;">Tài khoản của bạn</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    </div>

    <!-- Client-side pagination -->
    <div class="pagination" id="clientsPagination" style="padding:12px 0 4px;"></div>
</div>

<script>
(function () {
    'use strict';

    const ROWS_PER_PAGE = 20;
    const tbody = document.querySelector('#clientsTable tbody');
    const rows  = Array.from(tbody.querySelectorAll('tr'));
    let   currentPage = 1;

    function renderPage(page) {
        currentPage = page;
        const start = (page - 1) * ROWS_PER_PAGE;
        rows.forEach((r, i) => {
            r.style.display = (i >= start && i < start + ROWS_PER_PAGE) ? '' : 'none';
        });
        renderPagination();
    }

    function renderPagination() {
        const totalPages = Math.ceil(rows.length / ROWS_PER_PAGE);
        const pg = document.getElementById('clientsPagination');
        if (totalPages <= 1) { pg.innerHTML = ''; return; }
        let html = '';
        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="${i === currentPage ? 'active' : ''}"
                             onclick="renderPage(${i})">${i}</button>`;
        }
        pg.innerHTML = html;
    }

    window.renderPage = renderPage;
    if (rows.length > ROWS_PER_PAGE) renderPage(1);
})();
</script>
