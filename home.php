<?php
$page_title = "Trang chủ";
$page_css = "assets/css/main.css";
$base_path = "";
$page_scripts = ["assets/js/main.js"];

// Start output buffering to capture page content
ob_start();
?>

<div class="carousel">
    <div class="carousel-track">
        <div class="slide"><img src="assets/images/Slide1.jpg"></div>
        <div class="slide"><img src="assets/images/Slide2.png"></div>
    </div>

    <button class="carousel-btn prev">
        <i class="fa-solid fa-chevron-left"></i>
    </button>

    <button class="carousel-btn next">
        <i class="fa-solid fa-chevron-right"></i>
    </button>
</div>

<!-- NEW PRODUCTS -->
<div class="new-products_header">
    <p>NEW PRODUCTS</p>
    <h2>SẢN PHẨM MỚI</h2>
    <div class="line"></div>
</div>

<section class="product-list">
    <?php
    $products = [
        ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => 'upload/Ao321.png'],
        ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => 'upload/Ao321.png'],
        ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => 'upload/Ao321.png'],
        ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => 'upload/Ao321.png'],
        ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => 'upload/Ao321.png'],
        ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => 'upload/Ao321.png'],
        ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => 'upload/Ao321.png'],
        ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => 'upload/Ao321.png'],
        ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => 'upload/Ao321.png'],
        ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => 'upload/Ao321.png'],
        ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => 'upload/Ao321.png'],
        ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => 'upload/Ao321.png'],
    ];

    foreach ($products as $product) {
        echo '<div class="product-item">';
        echo '<a href="pages/product_detail.php">';
        echo '<img src="' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '">';
        echo '<h3>' . htmlspecialchars($product['name']) . '</h3>';
        echo '<p class="price">' . htmlspecialchars($product['price']) . '</p>';
        echo '</a>';
        echo '</div>';
    }
    ?>
</section>

<div class="view-more-wrapper">
    <a href="pages/product.php"><button class="view-more-btn">XEM THÊM</button></a>
</div>

<!-- BANNER -->
<div class="banner">
    <img src="assets/images/banner.png" alt="">
</div>

<!-- HOT PRODUCTS -->
<div class="hot-products_header">
    <p>HOT PRODUCTS</p>
    <h2>SẢN PHẨM NỔI BẬT</h2>
    <div class="line"></div>
</div>

<section class="product-list">
    <?php
    foreach ($products as $product) {
        echo '<div class="product-item">';
        echo '<a href="pages/product_detail.php">';
        echo '<img src="' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '">';
        echo '<h3>' . htmlspecialchars($product['name']) . '</h3>';
        echo '<p class="price">' . htmlspecialchars($product['price']) . '</p>';
        echo '</a>';
        echo '</div>';
    }
    ?>
</section>

<div class="view-more-wrapper">
    <a href="pages/product.php"><button class="view-more-btn">XEM THÊM</button></a>
</div>

<?php
// Capture output and include layout
$page_content = ob_get_clean();
include('includes/layout.php');
?>