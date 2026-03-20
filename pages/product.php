<?php
$page_title = "Sản Phẩm";
$page_css = "../assets/css/product.css";
$base_path = "../";

// Start output buffering to capture page content
ob_start();
?>

<section class="product-header">

    <div class="product-header-top">
        <h2>TẤT CẢ SẢN PHẨM</h2>

        <div class="sort">
            <label class="hide-on-phone">SẮP XẾP THEO:</label>
            <label><i class="fa-solid fa-filter"></i></label>
            <select>
                <option>Mặc định</option>
                <option>Giá thấp → cao</option>
                <option>Giá cao → thấp</option>
                <option>Mới nhất</option>
            </select>
        </div>
    </div>

    <div class="line"></div>

</section>

<?php
$products = [
    ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => '../upload/Ao321.png'],
    ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => '../upload/Ao321.png'],
    ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => '../upload/Ao321.png'],
    ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => '../upload/Ao321.png'],
    ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => '../upload/Ao321.png'],
    ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => '../upload/Ao321.png'],
    ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => '../upload/Ao321.png'],
    ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => '../upload/Ao321.png'],
    ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => '../upload/Ao321.png'],
    ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => '../upload/Ao321.png'],
    ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => '../upload/Ao321.png'],
    ['name' => 'Áo thể thao HKT - Sport T-Shirt', 'price' => '379.000đ', 'image' => '../upload/Ao321.png'],
];
include('../includes/product_list.php');
?>

<div class="pagination">
    <div class="previous-page">
        <a href="#"><i class="fa-solid fa-arrow-left"></i> <span class="hide-on-phone">TRANG TRƯỚC</span></a>
    </div>

    <div class="page-numbers">
        <a class="active" href="#">1</a>
        <a href="#">2</a>
        <a href="#">3</a>
        <a href="#">4</a>
        <a href="#">5</a>
    </div>

    <div class="next-page">
        <a href="#"><span class="hide-on-phone">TRANG SAU</span> <i class="fa-solid fa-arrow-right"></i></a>
    </div>
</div>

<?php
// Capture output and include layout
$page_content = ob_get_clean();
include('../includes/layout.php');
?>