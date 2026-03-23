<?php

/**
 * Product List Component
 * Expects: $products array with keys: id, name, price, image
 */
$base_path = isset($base_path) ? $base_path : "";
$products  = isset($products)  ? $products  : [];
?>

<section class="product-list">
    <?php foreach ($products as $product): ?>
        <div class="product-item">
            <a href="<?php echo $base_path; ?>pages/product_detail.php?id=<?php echo (int)($product['id'] ?? 0); ?>">
                <div class="product-img-wrap">
                    <?php
                    $img_src = '';
                    if (!empty($product['has_thumbnail'])) $img_src = $base_path . 'img.php?p=' . (int)$product['id'] . '&t=thumb';
                    elseif (!empty($product['has_image']))  $img_src = $base_path . 'img.php?p=' . (int)$product['id'] . '&t=main';
                    ?>
                    <img src="<?php echo htmlspecialchars($img_src); ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                        onerror="this.src='<?php echo $base_path; ?>assets/images/placeholder.png'">
                </div>
                <div class="product-item-info">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="price"><?php echo number_format((float)$product['price'], 0, ',', '.'); ?> VNĐ</p>
                </div>
            </a>
        </div>
    <?php endforeach; ?>

    <?php if (empty($products)): ?>
        <p style="grid-column:1/-1;text-align:center;color:#999;padding:60px 0;">
            Không có sản phẩm nào.
        </p>
    <?php endif; ?>
</section>