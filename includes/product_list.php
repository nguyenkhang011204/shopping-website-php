<?php
/**
 * Product List Component
 * 
 * Usage:
 * $products = [
 *     ['name' => 'Product Name', 'price' => '379.000đ', 'image' => 'upload/image.png'],
 *     ...
 * ];
 * include('includes/product_list.php');
 */

$base_path = isset($base_path) ? $base_path : "";
$products = isset($products) ? $products : [];
?>

<section class="product-list">
    <?php foreach ($products as $product): ?>
        <div class="product-item">
            <a href="<?php echo $base_path; ?>pages/product_detail.php">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p class="price"><?php echo htmlspecialchars($product['price']); ?></p>
            </a>
        </div>
    <?php endforeach; ?>
</section>
