<?php

/**
 * Product List Component
 *
 * Usage:
 * $products = $pdo->query("SELECT id, name, price, image FROM products ...")->fetchAll();
 * include('includes/product_list.php');
 */

$base_path = isset($base_path) ? $base_path : "";
$products  = isset($products)  ? $products  : [];
?>

<section class="product-list">
    <?php foreach ($products as $product): ?>
        <div class="product-item">
            <a href="<?php echo $base_path; ?>pages/product_detail.php?id=<?php echo (int)($product['id'] ?? 0); ?>">
                <div class="product-img-wrap">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                        onerror="this.src='<?php echo $base_path; ?>assets/images/placeholder.png'">
                </div>
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p class="price"><?php echo number_format((float)$product['price'], 0, ',', '.'); ?>đ</p>
            </a>
        </div>
    <?php endforeach; ?>

    <?php if (empty($products)): ?>
        <p style="grid-column:1/-1; text-align:center; color:#999; padding:40px 0;">
            Không có sản phẩm nào.
        </p>
    <?php endif; ?>
</section>