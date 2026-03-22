<?php
require_once '../includes/dbconnect.php';

// ── Validate ID ───────────────────────────────────────────────
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: product.php");
    exit;
}

// ── Fetch product ─────────────────────────────────────────────
$stmt = $pdo->prepare(
    "SELECT p.*, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.id = ? AND p.is_active = 1
     LIMIT 1"
);
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    header("Location: product.php");
    exit;
}

// ── Fetch gallery images ──────────────────────────────────────
$imgStmt = $pdo->prepare(
    "SELECT image FROM product_images WHERE product_id = ? ORDER BY sort_order"
);
$imgStmt->execute([$id]);
$images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
if (empty($images)) $images = [$product['image']]; // fallback to main image

// ── Fetch sizes ───────────────────────────────────────────────
$sizeStmt = $pdo->prepare(
    "SELECT size, stock FROM product_sizes
     WHERE product_id = ?
     ORDER BY FIELD(size,'XS','S','M','L','XL','28','30','32','34','35','36','37','38','39','40','41','42','Free Size')"
);
$sizeStmt->execute([$id]);
$sizes = $sizeStmt->fetchAll();

// ── Fetch other products (same category, random) ──────────────
$otherStmt = $pdo->prepare(
    "SELECT id, name, price, image FROM products
     WHERE is_active = 1 AND id != ?
       AND (category_id = ? OR 1=1)
     ORDER BY RAND()
     LIMIT 8"
);
$otherStmt->execute([$id, $product['category_id']]);
$other_products = $otherStmt->fetchAll();

// ── Page setup ────────────────────────────────────────────────
$page_title   = htmlspecialchars($product['name']);
$page_css     = "../assets/css/product_detail.css";
$base_path    = "../";
$page_scripts = ["../assets/js/product_detail.js"];

ob_start();
?>

<div class="product-container">

    <div class="product-detail">

        <!-- Gallery -->
        <div class="product-gallery">
            <img src="<?php echo htmlspecialchars($images[0]); ?>"
                alt="<?php echo htmlspecialchars($product['name']); ?>"
                id="main-image"
                onerror="this.src='../assets/images/placeholder.png'">
            <div class="thumb-list">
                <?php foreach ($images as $img): ?>
                    <img src="<?php echo htmlspecialchars($img); ?>"
                        alt=""
                        onclick="changeImg(this)"
                        onerror="this.src='../assets/images/placeholder.png'">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Info -->
        <div class="product-info">
            <?php if ($product['category_name']): ?>
                <p class="product-category" style="color:#f4a62a;font-size:13px;margin-bottom:5px;">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </p>
            <?php endif; ?>

            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p>SKU: <?php echo htmlspecialchars($product['sku'] ?? '—'); ?></p>
            <h2 class="price"><?php echo number_format((float)$product['price'], 0, ',', '.'); ?>đ</h2>

            <!-- Stock badge -->
            <?php if ($product['stock'] > 0): ?>
                <span style="color:green;font-size:13px;">
                    <i class="fa-solid fa-circle-check"></i> Còn hàng (<?= $product['stock'] ?> sản phẩm)
                </span>
            <?php else: ?>
                <span style="color:red;font-size:13px;">
                    <i class="fa-solid fa-circle-xmark"></i> Hết hàng
                </span>
            <?php endif; ?>

            <div class="line"></div>

            <!-- Size -->
            <label class="title-size">Kích thước</label>
            <div>
                <?php if (!empty($sizes)): ?>
                    <select class="size" id="sizeSelect">
                        <?php foreach ($sizes as $s): ?>
                            <option value="<?php echo htmlspecialchars($s['size']); ?>"
                                <?= $s['stock'] == 0 ? 'disabled' : '' ?>>
                                <?php echo htmlspecialchars($s['size']); ?>
                                <?= $s['stock'] == 0 ? ' (hết hàng)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <p style="color:#999;">Không có size</p>
                <?php endif; ?>
            </div>

            <!-- Quantity -->
            <label class="title-count">Số lượng</label>
            <div>
                <input class="count" id="qtyInput" type="number" value="1" min="1"
                    max="<?php echo (int)$product['stock']; ?>">
            </div>

            <!-- Buttons -->
            <div class="btn-group">
                <button class="add-cart"
                    data-id="<?php echo $product['id']; ?>"
                    <?= $product['stock'] == 0 ? 'disabled style="opacity:.5;cursor:not-allowed"' : '' ?>>
                    <i class="fa-solid fa-cart-arrow-down"></i> Thêm giỏ hàng
                </button>
                <button class="buy"
                    data-id="<?php echo $product['id']; ?>"
                    <?= $product['stock'] == 0 ? 'disabled style="opacity:.5;cursor:not-allowed"' : '' ?>>
                    Đặt hàng
                </button>
            </div>
        </div>
    </div>

    <!-- Description -->
    <div class="description">
        <h2>MÔ TẢ SẢN PHẨM</h2>
        <div class="line"></div>
        <?php if (!empty($product['description'])): ?>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        <?php else: ?>
            <p style="color:#999;">Chưa có mô tả cho sản phẩm này.</p>
        <?php endif; ?>
    </div>

    <!-- Other Products -->
    <?php if (!empty($other_products)): ?>
        <div class="other-products">
            <h2>SẢN PHẨM KHÁC</h2>
            <div class="line"></div>
            <?php $products = $other_products;
            include('../includes/product_list.php'); ?>
        </div>
    <?php endif; ?>

</div>

<?php
$page_content = ob_get_clean();
include('../includes/layout.php');
?>