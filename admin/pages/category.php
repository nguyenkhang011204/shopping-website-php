<?php
// ── CSRF ─────────────────────────────────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── POST handler ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        while (ob_get_level() > 0) ob_end_clean();
        header("Location: index.php?page=category&msg=csrf");
        exit;
    }

    $action = $_POST['action'] ?? '';

    // ── Delete ────────────────────────────────────────────────────────────────
    if ($action === 'delete_category') {
        $id = (int)($_POST['category_id'] ?? 0);
        if ($id > 0) {
            // Check if any products belong to this category
            $count = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
            $count->execute([$id]);
            if ((int)$count->fetchColumn() > 0) {
                while (ob_get_level() > 0) ob_end_clean();
                header("Location: index.php?page=category&msg=has_products");
                exit;
            }
            $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        }
        while (ob_get_level() > 0) ob_end_clean();
        header("Location: index.php?page=category&msg=deleted");
        exit;
    }

    // ── Save (add / edit) ─────────────────────────────────────────────────────
    if ($action === 'save_category') {
        $category_id = (int)($_POST['category_id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $slug        = trim($_POST['slug'] ?? '');

        if ($name === '' || $slug === '') {
            while (ob_get_level() > 0) ob_end_clean();
            header("Location: index.php?page=category&msg=error");
            exit;
        }

        try {
            if ($category_id > 0) {
                $pdo->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?")
                    ->execute([$name, $slug, $category_id]);
                $msg = 'updated';
            } else {
                $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)")
                    ->execute([$name, $slug]);
                $msg = 'added';
            }
        } catch (PDOException $e) {
            $msg = ($e->getCode() === '23000') ? 'slug_dup' : 'error';
        }

        while (ob_get_level() > 0) ob_end_clean();
        header("Location: index.php?page=category&msg={$msg}");
        exit;
    }
}

// ── GET: fetch data ───────────────────────────────────────────────────────────
$msg = $_GET['msg'] ?? '';

$categories = $pdo->query(
    "SELECT c.*, COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id
     ORDER BY c.name"
)->fetchAll();

// ── Flash messages ────────────────────────────────────────────────────────────
$flash_map = [
    'added'        => ['success', 'Thêm danh mục thành công.'],
    'updated'      => ['success', 'Cập nhật danh mục thành công.'],
    'deleted'      => ['success', 'Đã xóa danh mục.'],
    'has_products' => ['error',   'Không thể xóa: danh mục còn sản phẩm. Hãy chuyển sản phẩm trước.'],
    'slug_dup'     => ['error',   'Slug đã tồn tại. Vui lòng dùng slug khác.'],
    'error'        => ['error',   'Có lỗi xảy ra. Vui lòng thử lại.'],
    'csrf'         => ['error',   'Yêu cầu không hợp lệ.'],
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
    <div class="toolbar-spacer"></div>
    <button class="btn" onclick="openAddModal()">
        <i class="fa-solid fa-plus"></i> Thêm danh mục
    </button>
</div>

<!-- Category table -->
<div class="table-container">
    <div class="table-header">
        <h3>Danh mục
            <span style="font-size:13px;font-weight:400;color:var(--admin-muted);margin-left:8px;">
                (<?= count($categories) ?> danh mục)
            </span>
        </h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tên danh mục</th>
                <th>Slug</th>
                <th style="text-align:center;">Sản phẩm</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($categories)): ?>
            <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--admin-muted);">
                Chưa có danh mục nào.
            </td></tr>
        <?php else: ?>
            <?php foreach ($categories as $c): ?>
            <tr>
                <td style="color:var(--admin-muted);font-size:12px;"><?= $c['id'] ?></td>
                <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                <td><span class="text-muted" style="font-size:12px;"><?= htmlspecialchars($c['slug']) ?></span></td>
                <td style="text-align:center;">
                    <span class="badge <?= $c['product_count'] > 0 ? 'active-badge' : 'inactive' ?>">
                        <?= (int)$c['product_count'] ?>
                    </span>
                </td>
                <td style="white-space:nowrap;">
                    <button class="btn btn-sm"
                            onclick="openEditModal(<?= (int)$c['id'] ?>, <?= htmlspecialchars(json_encode($c['name']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($c['slug']), ENT_QUOTES) ?>)">
                        <i class="fa-regular fa-pen-to-square"></i> Sửa
                    </button>

                    <?php if ((int)$c['product_count'] === 0): ?>
                    <form method="POST" action="index.php?page=category" class="inline-form"
                          onsubmit="return confirm('Xóa danh mục «<?= htmlspecialchars(addslashes($c['name'])) ?>»?')">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="category_id" value="<?= (int)$c['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                    </form>
                    <?php else: ?>
                        <span class="text-muted" style="font-size:11px;margin-left:4px;" title="Không thể xóa khi còn sản phẩm">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ═══ ADD / EDIT MODAL ════════════════════════════════════════════════════ -->
<div id="categoryModal" class="modal" role="dialog" aria-modal="true">
    <div class="modal-overlay" onclick="closeModal('categoryModal')"></div>
    <div class="modal-box modal-box-sm">
        <div class="modal-header">
            <h3 id="catModalTitle">Thêm danh mục mới</h3>
            <button type="button" class="modal-close" onclick="closeModal('categoryModal')"
                    aria-label="Đóng">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="categoryForm" method="POST" action="index.php?page=category">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="save_category">
            <input type="hidden" name="category_id" id="fc_id" value="">

            <div class="modal-body">

                <div class="form-group">
                    <label>Tên danh mục <span class="req">*</span></label>
                    <input type="text" name="name" id="fc_name"
                           placeholder="VD: Áo nam" maxlength="100" required>
                </div>

                <div class="form-group">
                    <label>Slug <span class="req">*</span></label>
                    <input type="text" name="slug" id="fc_slug"
                           placeholder="VD: ao-nam" maxlength="100" required>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline"
                        onclick="closeModal('categoryModal')">Hủy</button>
                <button type="submit" class="btn">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu danh mục
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    'use strict';

    // ── Slug generator ─────────────────────────────────────────────────────
    const viMap = {
        'à':'a','á':'a','â':'a','ã':'a','ă':'a','ắ':'a','ằ':'a','ẵ':'a','ẳ':'a','ặ':'a',
        'ấ':'a','ầ':'a','ẫ':'a','ẩ':'a','ậ':'a',
        'è':'e','é':'e','ê':'e','ế':'e','ề':'e','ễ':'e','ể':'e','ệ':'e',
        'ì':'i','í':'i','ị':'i','ỉ':'i','ĩ':'i',
        'ò':'o','ó':'o','ô':'o','ő':'o','ơ':'o','ớ':'o','ờ':'o','ỡ':'o','ở':'o','ợ':'o',
        'ố':'o','ồ':'o','ỗ':'o','ổ':'o','ộ':'o',
        'ù':'u','ú':'u','ũ':'u','ụ':'u','ủ':'u','ư':'u','ứ':'u','ừ':'u','ữ':'u','ử':'u','ự':'u',
        'ý':'y','ỳ':'y','ỹ':'y','ỷ':'y','ỵ':'y',
        'đ':'d','ñ':'n'
    };

    function toSlug(str) {
        return str.toLowerCase()
            .split('').map(c => viMap[c] ?? c).join('')
            .replace(/[^a-z0-9\s-]/g, '')
            .trim().replace(/\s+/g, '-').replace(/-+/g, '-');
    }

    document.getElementById('fc_name').addEventListener('input', function () {
        if (!document.getElementById('fc_id').value) {
            document.getElementById('fc_slug').value = toSlug(this.value);
        }
    });

    // ── Modal helpers ──────────────────────────────────────────────────────
    function openAddModal() {
        document.getElementById('catModalTitle').textContent = 'Thêm danh mục mới';
        document.getElementById('categoryForm').reset();
        document.getElementById('fc_id').value = '';
        document.getElementById('categoryModal').classList.add('active');
        document.getElementById('fc_name').focus();
    }

    function openEditModal(id, name, slug) {
        document.getElementById('catModalTitle').textContent = 'Sửa danh mục';
        document.getElementById('fc_id').value   = id;
        document.getElementById('fc_name').value = name;
        document.getElementById('fc_slug').value = slug;
        document.getElementById('categoryModal').classList.add('active');
        document.getElementById('fc_name').focus();
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape')
            document.querySelectorAll('.modal.active').forEach(m => m.classList.remove('active'));
    });

    window.openAddModal  = openAddModal;
    window.openEditModal = openEditModal;
    window.closeModal    = closeModal;
})();
</script>
