<?php
require_once 'includes/dbconnect.php';

$page_title   = "Trang chủ";
$page_css     = "assets/css/main.css";
$base_path    = "";
$page_scripts = ["assets/js/main.js"];

// Fetch newest 8 active products
$new_products = $pdo->query(
    "SELECT id, name, price,
            (thumbnail_data IS NOT NULL) AS has_thumbnail,
            (image_data IS NOT NULL) AS has_image
     FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 8"
)->fetchAll();

// Fetch 8 featured products
$hot_products = $pdo->query(
    "SELECT id, name, price,
            (thumbnail_data IS NOT NULL) AS has_thumbnail,
            (image_data IS NOT NULL) AS has_image
     FROM products WHERE is_active = 1 AND is_featured = 1 ORDER BY created_at DESC LIMIT 8"
)->fetchAll();

ob_start();
?>

<div class="carousel">
    <div class="carousel-track">
        <div class="slide"><img src="assets/images/Slide1.jpg" alt="Slide 1"></div>
        <div class="slide"><img src="assets/images/Slide2.png" alt="Slide 2"></div>
    </div>
    <button class="carousel-btn prev"><i class="fa-solid fa-chevron-left"></i></button>
    <button class="carousel-btn next"><i class="fa-solid fa-chevron-right"></i></button>
</div>

<!-- NEW PRODUCTS -->
<div class="new-products_header">
    <p>NEW PRODUCTS</p>
    <h2>SẢN PHẨM MỚI</h2>
    <div class="line"></div>
</div>

<?php $products = $new_products;
include('includes/product_list.php'); ?>

<div class="view-more-wrapper">
    <a href="pages/product.php"><button class="view-more-btn">XEM THÊM</button></a>
</div>

<!-- BANNER -->
<div class="banner">
    <img src="assets/images/banner.png" alt="Banner">
</div>

<!-- HOT PRODUCTS -->
<div class="hot-products_header">
    <p>HOT PRODUCTS</p>
    <h2>SẢN PHẨM NỔI BẬT</h2>
    <div class="line"></div>
</div>

<?php $products = $hot_products;
include('includes/product_list.php'); ?>

<div class="view-more-wrapper">
    <a href="pages/product.php"><button class="view-more-btn">XEM THÊM</button></a>
</div>

<?php
$page_content = ob_get_clean();
include('includes/layout.php');
?>