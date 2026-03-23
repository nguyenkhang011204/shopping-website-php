<?php
require_once '../includes/dbconnect.php';

$page_title = "Sản Phẩm";
$page_css = "../assets/css/product.css";
$base_path = "../";

// ── Inputs ───────────────────────────────────────────────────
$per_page = 12;
$current_page = max(1, (int) ($_GET['page'] ?? 1));
$sort_key = $_GET['sort'] ?? 'newest';
$cat_slug = trim($_GET['category'] ?? '');

$sort_map = [
    'newest' => 'p.created_at DESC',
    'price_asc' => 'p.price ASC',
    'price_desc' => 'p.price DESC',
];
$order_by = $sort_map[$sort_key] ?? 'p.created_at DESC';

// ── Categories for filter bar ─────────────────────────────────
$categories = $pdo->query("SELECT id, name, slug FROM categories ORDER BY name")->fetchAll();

// ── WHERE clause ──────────────────────────────────────────────
$where = "WHERE p.is_active = 1";
$params = [];

if ($cat_slug !== '') {
    $where .= " AND c.slug = :cat_slug";
    $params[':cat_slug'] = $cat_slug;
}

// ── Total count ───────────────────────────────────────────────
$count_sql = "SELECT COUNT(*) FROM products p
              LEFT JOIN categories c ON c.id = p.category_id
              $where";
$cnt_stmt = $pdo->prepare($count_sql);
$cnt_stmt->execute($params);
$total = (int) $cnt_stmt->fetchColumn();
$total_pages = max(1, (int) ceil($total / $per_page));
$current_page = min($current_page, $total_pages);
$offset = ($current_page - 1) * $per_page;

// ── Products ──────────────────────────────────────────────────
$sql = "SELECT p.id, p.name, p.price, p.image
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        $where
        ORDER BY $order_by
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v)
    $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

// ── Build query string helper ─────────────────────────────────
function buildQuery(array $overrides = []): string
{
    $base = ['sort' => $_GET['sort'] ?? 'newest', 'category' => $_GET['category'] ?? '', 'page' => $_GET['page'] ?? 1];
    $merged = array_merge($base, $overrides);
    $merged = array_filter($merged, fn($v) => $v !== '');
    return '?' . http_build_query($merged);
}

ob_start();
?>

<section class="product-header">
    <div class="product-header-top">
        <h2>
            <?php if ($cat_slug):
                $active_cat = array_values(array_filter($categories, fn($c) => $c['slug'] === $cat_slug));
                echo $active_cat ? htmlspecialchars($active_cat[0]['name']) : 'Sản Phẩm';
            else: ?>
                TẤT CẢ SẢN PHẨM
            <?php endif; ?>
            <small style="font-size:13px;font-weight:400;color:#999;">(<?= $total ?> sản phẩm)</small>
        </h2>

        <div class="sort">
            <label class="hide-on-phone">SẮP XẾP THEO:</label>
            <label><i class="fa-solid fa-filter"></i></label>
            <select onchange="window.location.href=buildQuery({sort:this.value,page:1})">
                <option value="newest" <?= $sort_key === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                <option value="price_asc" <?= $sort_key === 'price_asc' ? 'selected' : '' ?>>Giá thấp → cao</option>
                <option value="price_desc" <?= $sort_key === 'price_desc' ? 'selected' : '' ?>>Giá cao → thấp</option>
            </select>
        </div>
    </div>

    <!-- Category filter pills -->
    <div class="category-filter">
        <a href="<?= buildQuery(['category' => '', 'page' => 1]) ?>"
            class="cat-pill <?= $cat_slug === '' ? 'active' : '' ?>">Tất cả</a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?= buildQuery(['category' => $cat['slug'], 'page' => 1]) ?>"
                class="cat-pill <?= $cat_slug === $cat['slug'] ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="line"></div>
</section>

<script>
    function buildQuery(overrides) {
        const base = {
            sort: '<?= htmlspecialchars($sort_key) ?>',
            category: '<?= htmlspecialchars($cat_slug) ?>',
            page: <?= $current_page ?>
        };
        const merged = Object.assign({}, base, overrides);
        const params = new URLSearchParams();
        for (const [k, v] of Object.entries(merged)) {
            if (v !== '' && v !== null) params.set(k, v);
        }
        return '?' + params.toString();
    }
</script>

<?php include('../includes/product_list.php'); ?>

<?php if ($total_pages > 1): ?>
    <div class="pagination">
        <div class="previous-page">
            <?php if ($current_page > 1): ?>
                <a href="<?= buildQuery(['page' => $current_page - 1]) ?>">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span class="hide-on-phone">TRANG TRƯỚC</span>
                </a>
            <?php endif; ?>
        </div>

        <div class="page-numbers">
            <?php
            $start = max(1, $current_page - 2);
            $end = min($total_pages, $current_page + 2);
            if ($start > 1)
                echo '<a href="' . buildQuery(['page' => 1]) . '">1</a>';
            if ($start > 2)
                echo '<span>…</span>';
            for ($i = $start; $i <= $end; $i++):
                ?>
                <a class="<?= $i === $current_page ? 'active' : '' ?>" href="<?= buildQuery(['page' => $i]) ?>"><?= $i ?></a>
            <?php endfor;
            if ($end < $total_pages - 1)
                echo '<span>…</span>';
            if ($end < $total_pages)
                echo '<a href="' . buildQuery(['page' => $total_pages]) . '">' . $total_pages . '</a>';
            ?>
        </div>

        <div class="next-page">
            <?php if ($current_page < $total_pages): ?>
                <a href="<?= buildQuery(['page' => $current_page + 1]) ?>">
                    <span class="hide-on-phone">TRANG SAU</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php
$page_content = ob_get_clean();
include('../includes/layout.php');
?>