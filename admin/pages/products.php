<?php
// ── Helpers ────────────────────────────────────────────────────────────────
function admin_img(?string $path): string
{
    if (!$path) return '';
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
    return '../../' . ltrim($path, '/');
}

function delete_local_image(?string $path): void
{
    if (!$path || str_starts_with($path, 'http')) return;
    $abs = __DIR__ . '/../../' . ltrim($path, '/');
    if (is_file($abs)) @unlink($abs);
}

// ── CSRF ───────────────────────────────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── POST: delete / toggle only ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        while (ob_get_level() > 0) ob_end_clean();
        header("Location: index.php?page=products&msg=csrf");
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'delete_product') {
        $id = (int)($_POST['product_id'] ?? 0);
        if ($id > 0) {
            // BLOBs are deleted via CASCADE on the DB side
            $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
        }
        while (ob_get_level() > 0) ob_end_clean();
        header("Location: index.php?page=products&msg=deleted");
        exit;
    }

    if ($action === 'toggle_active') {
        $id = (int)($_POST['product_id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("UPDATE products SET is_active = 1 - is_active WHERE id=?")->execute([$id]);
        }
        while (ob_get_level() > 0) ob_end_clean();
        header("Location: index.php?page=products&msg=updated");
        exit;
    }
}

// ── GET: fetch products ────────────────────────────────────────────────────
$search     = trim($_GET['search'] ?? '');
$cat_filter = (int)($_GET['cat']   ?? 0);
$msg        = $_GET['msg']         ?? '';

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

$params = [];
$where = [];
$sql = "SELECT p.id, p.name, p.slug, p.sku, p.category_id, p.price, p.stock,
               p.is_active, p.is_featured, p.created_at,
               c.name AS cat_name,
               (p.image_data     IS NOT NULL) AS has_image,
               (p.thumbnail_data IS NOT NULL) AS has_thumbnail
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id";

if ($search !== '') {
    $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($cat_filter > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $cat_filter;
}
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// ── Flash messages ─────────────────────────────────────────────────────────
$flash_map = [
    'added'    => ['success', 'Thêm sản phẩm thành công.'],
    'updated'  => ['success', 'Cập nhật sản phẩm thành công.'],
    'deleted'  => ['success', 'Đã xóa sản phẩm.'],
    'slug_dup' => ['error',   'Slug đã tồn tại. Vui lòng dùng slug khác.'],
    'error'    => ['error',   'Có lỗi xảy ra. Vui lòng thử lại.'],
    'csrf'     => ['error',   'Yêu cầu không hợp lệ.'],
];
?>

<!-- Flash banner -->
<?php if ($msg && isset($flash_map[$msg])): [$type, $text] = $flash_map[$msg]; ?>
    <div class="msg-banner <?= $type ?>">
        <i class="fa-solid fa-<?= $type === 'success' ? 'circle-check' : 'circle-exclamation' ?>"></i>
        <?= htmlspecialchars($text) ?>
    </div>
<?php endif; ?>

<!-- Toolbar -->
<div class="page-toolbar">
    <form class="toolbar-search" method="GET" action="index.php">
        <input type="hidden" name="page" value="products">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
            placeholder="Tìm tên, SKU...">
        <?php if ($cat_filter > 0): ?>
            <input type="hidden" name="cat" value="<?= $cat_filter ?>">
        <?php endif; ?>
        <button type="submit">Tìm</button>
    </form>

    <form method="GET" action="index.php" id="catFilterForm">
        <input type="hidden" name="page" value="products">
        <?php if ($search !== ''): ?>
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
        <?php endif; ?>
        <select name="cat" class="toolbar-filter"
            onchange="document.getElementById('catFilterForm').submit()">
            <option value="0">-- Tất cả danh mục --</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>"
                    <?= $cat_filter === (int)$c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($search || $cat_filter): ?>
        <a href="index.php?page=products" class="btn btn-outline btn-sm">Xóa lọc</a>
    <?php endif; ?>

    <div class="toolbar-spacer"></div>

    <a href="index.php?page=product-form" class="btn">
        <i class="fa-solid fa-plus"></i> Thêm sản phẩm
    </a>
</div>

<!-- Product table -->
<div class="table-container">
    <div class="table-header">
        <h3>Sản phẩm
            <span style="font-size:13px;font-weight:400;color:var(--admin-muted);margin-left:8px;">
                (<?= count($products) ?> kết quả)
            </span>
        </h3>
    </div>

    <div style="overflow-x:auto;">
        <table id="productTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Giá</th>
                    <th>Tồn kho</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:32px;color:var(--admin-muted);">
                            Không tìm thấy sản phẩm nào.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p):
                        if ($p['has_thumbnail'])     $thumb_src = "../img.php?p={$p['id']}&t=thumb";
                        elseif ($p['has_image'])     $thumb_src = "../img.php?p={$p['id']}&t=main";
                        else                         $thumb_src = '';
                    ?>
                        <tr>
                            <td style="color:var(--admin-muted);font-size:12px;"><?= $p['id'] ?></td>
                            <td>
                                <?php if ($thumb_src): ?>
                                    <img src="<?= htmlspecialchars($thumb_src) ?>"
                                        alt="" class="product-thumb"
                                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                    <div class="product-thumb-placeholder" style="display:none;">
                                        <i class="fa-solid fa-image"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="product-thumb-placeholder">
                                        <i class="fa-solid fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($p['name']) ?></strong>
                                <?php if ($p['sku']): ?>
                                    <br><span class="text-muted">SKU: <?= htmlspecialchars($p['sku']) ?></span>
                                <?php endif; ?>
                                <?php if ($p['is_featured']): ?>
                                    <i class="fa-solid fa-star text-orange" title="Nổi bật"
                                        style="font-size:11px;margin-left:4px;"></i>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($p['cat_name'] ?? '—') ?></td>
                            <td style="white-space:nowrap;font-weight:600;">
                                <?= number_format((float)$p['price'], 0, ',', '.') ?> VNĐ
                            </td>
                            <td style="text-align:center;"><?= (int)$p['stock'] ?></td>
                            <td>
                                <?php if ($p['is_active']): ?>
                                    <span class="badge success">Đang bán</span>
                                <?php else: ?>
                                    <span class="badge inactive">Ẩn</span>
                                <?php endif; ?>
                            </td>
                            <td style="white-space:nowrap;display:flex;align-items:center;gap:6px;">
                                <a href="index.php?page=product-form&id=<?= (int)$p['id'] ?>"
                                    class="btn btn-sm">
                                    <i class="fa-regular fa-pen-to-square"></i> Sửa
                                </a>
                                <span style="width:1px;height:20px;background:var(--admin-border);display:inline-block;"></span>
                                <form method="POST" action="index.php?page=products" class="inline-form">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="toggle_active">
                                    <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline">
                                        <?= $p['is_active'] ? 'Ẩn' : 'Hiện' ?>
                                    </button>
                                </form>

                                <form method="POST" action="index.php?page=products" class="inline-form"
                                    onsubmit="return confirm('Xóa sản phẩm «<?= htmlspecialchars(addslashes($p['name'])) ?>»?')">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="delete_product">
                                    <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination" id="productPagination" style="padding:12px 0 4px;"></div>
</div>

<script>
    (function() {
        const ROWS_PER_PAGE = 15;
        const tbody = document.querySelector('#productTable tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        let currentPage = 1;

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
            const pg = document.getElementById('productPagination');
            if (totalPages <= 1) {
                pg.innerHTML = '';
                return;
            }
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