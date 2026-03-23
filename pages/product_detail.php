<?php
require_once '../includes/dbconnect.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: product.php");
    exit;
}

$stmt = $pdo->prepare(
    "SELECT p.*, c.name AS category_name, c.slug AS category_slug
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.id = ? AND p.is_active = 1 LIMIT 1"
);
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    header("Location: product.php");
    exit;
}

$imgStmt = $pdo->prepare(
    "SELECT image FROM product_images WHERE product_id = ? ORDER BY sort_order"
);
$imgStmt->execute([$id]);
$images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
if (empty($images))
    $images = [$product['image']];

$sizeStmt = $pdo->prepare(
    "SELECT size, stock FROM product_sizes WHERE product_id = ?
     ORDER BY FIELD(size,'XS','S','M','L','XL','XXL',
     '28','30','32','34','35','36','37','38','39','40','41','42','43','Free Size')"
);
$sizeStmt->execute([$id]);
$sizes = $sizeStmt->fetchAll();

$otherStmt = $pdo->prepare(
    "SELECT id, name, price, image FROM products
     WHERE is_active = 1 AND id != ?
     ORDER BY (category_id = ?) DESC, RAND() LIMIT 8"
);
$otherStmt->execute([$id, $product['category_id']]);
$other_products = $otherStmt->fetchAll();

$page_title = htmlspecialchars($product['name']);
$page_css = "../assets/css/product_detail.css";
$base_path = "../";
$page_scripts = ["../assets/js/product_detail.js"];

ob_start();
?>

<div class="product-container">

    <!-- Breadcrumb -->
    <nav class="pd-breadcrumb">
        <a href="../home.php" style="color:inherit;text-decoration:none;">Trang chủ</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="product.php" style="color:inherit;text-decoration:none;">Sản phẩm</a>
        <?php if ($product['category_name']): ?>
            <i class="fa-solid fa-chevron-right"></i>
            <a href="product.php?category=<?= htmlspecialchars($product['category_slug'] ?? '') ?>"
                style="color:inherit;text-decoration:none;">
                <?= htmlspecialchars($product['category_name']) ?>
            </a>
        <?php endif; ?>
        <i class="fa-solid fa-chevron-right"></i>
        <span><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <!-- Main detail grid -->
    <div class="product-detail">

        <!-- ── LEFT: Gallery ── -->
        <div class="product-gallery">

            <!-- Vertical thumbnail strip -->
            <div class="thumb-list">
                <?php foreach ($images as $i => $img): ?>
                    <div class="thumb-item <?= $i === 0 ? 'active' : '' ?>"
                        onclick="changeImg(this, '<?= htmlspecialchars($img) ?>')">
                        <img src="<?= htmlspecialchars($img) ?>" alt=""
                            onerror="this.src='../assets/images/placeholder.png'">
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Main image -->
            <div class="main-img-wrap">
                <img src="<?= htmlspecialchars($images[0]) ?>" alt="<?= htmlspecialchars($product['name']) ?>"
                    id="main-image" onerror="this.src='../assets/images/placeholder.png'">
            </div>

        </div>

        <!-- ── RIGHT: Info ── -->
        <div class="product-info">

            <!-- Category -->
            <?php if ($product['category_name']): ?>
                <p class="product-category"><?= htmlspecialchars($product['category_name']) ?></p>
            <?php endif; ?>

            <!-- Name -->
            <h1><?= htmlspecialchars($product['name']) ?></h1>

            <!-- SKU -->
            <p class="sku">SKU: <?= htmlspecialchars($product['sku'] ?? '—') ?></p>

            <!-- Price -->
            <p class="price"><?= number_format((float) $product['price'], 0, ',', '.') ?>đ</p>

            <!-- Stock -->
            <?php if ($product['stock'] > 0): ?>
                <span class="stock-badge in-stock">
                    <i class="fa-solid fa-circle-check"></i>
                    Còn hàng &nbsp;·&nbsp; <?= $product['stock'] ?> sản phẩm
                </span>
            <?php else: ?>
                <span class="stock-badge out-stock">
                    <i class="fa-solid fa-circle-xmark"></i> Hết hàng
                </span>
            <?php endif; ?>

            <div class="pd-divider"></div>

            <!-- Sizes as chips -->
            <?php if (!empty($sizes)): ?>
                <label class="field-label">Kích thước</label>
                <div class="size-chips">
                    <?php foreach ($sizes as $i => $s): ?>
                        <button type="button"
                            class="size-chip <?= $s['stock'] == 0 ? 'disabled' : ($i === 0 ? 'selected' : '') ?>"
                            data-size="<?= htmlspecialchars($s['size']) ?>" <?= $s['stock'] == 0 ? 'disabled' : '' ?>>
                            <?= htmlspecialchars($s['size']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <!-- Hidden select for form submission -->
                <select id="sizeSelect">
                    <?php foreach ($sizes as $s): ?>
                        <option value="<?= htmlspecialchars($s['size']) ?>">
                            <?= htmlspecialchars($s['size']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <!-- Quantity stepper -->
            <label class="field-label">Số lượng</label>
            <div class="qty-row">
                <button type="button" class="qty-btn" id="qtyMinus">−</button>
                <input class="qty-input" id="qtyInput" type="number" value="1" min="1"
                    max="<?= (int) $product['stock'] ?>">
                <button type="button" class="qty-btn" id="qtyPlus">+</button>
            </div>

            <!-- Action buttons -->
            <div class="btn-group">
                <button class="add-cart" data-id="<?= $product['id'] ?>" <?= $product['stock'] == 0 ? 'disabled' : '' ?>>
                    <i class="fa-solid fa-bag-shopping"></i> Giỏ hàng
                </button>
                <button class="buy" data-id="<?= $product['id'] ?>" <?= $product['stock'] == 0 ? 'disabled' : '' ?>>
                    Đặt hàng ngay
                </button>
            </div>

            <!-- Trust badges -->
            <div class="trust-badges">
                <div class="trust-badge">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Bảo hành chính hãng</span>
                </div>
                <div class="trust-badge">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Đổi trả 30 ngày</span>
                </div>
                <div class="trust-badge">
                    <i class="fa-solid fa-truck-fast"></i>
                    <span>Giao hàng toàn quốc</span>
                </div>
            </div>

        </div><!-- /product-info -->
    </div><!-- /product-detail -->

    <!-- Description -->
    <?php if (!empty($product['description'])): ?>
        <div class="description">
            <h2 class="section-heading">Mô tả sản phẩm</h2>
            <hr class="section-line">
            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        </div>
    <?php endif; ?>

    <!-- Other products -->
    <?php if (!empty($other_products)): ?>
        <div class="other-products">
            <h2 class="section-heading">Sản phẩm khác</h2>
            <hr class="section-line">
            <?php $products = $other_products;
            include('../includes/product_list.php'); ?>
        </div>
    <?php endif; ?>

</div><!-- /product-container -->

<?php
$page_content = ob_get_clean();
include('../includes/layout.php');
?>