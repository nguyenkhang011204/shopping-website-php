<?php
$page_title = "Chi tiết sản phẩm";
$page_css = "../assets/css/product_detail.css";
$base_path = "../";
$page_scripts = ["../assets/js/product_detail.js"];

// Start output buffering to capture page content
ob_start();
?>

<div class="product-container">

    <div class="product-detail">

        <div class="product-gallery">
            <img src="../upload/Ao321.png" alt="" id="main-image">
            <div class="thumb-list">
                <img src="../upload/Ao321.png" alt="" onclick="changeImg(this)">
                <img src="../upload/Behind_Ao321.png" alt="" onclick="changeImg(this)">
                <img src="../upload/Ao321.png" alt="" onclick="changeImg(this)">
            </div>
        </div>

        <div class="product-info">
            <h1>Áo thể thao HKT - Sport T-Shirt</h1>
            <p>SKU: ATHLETICS-S</p>
            <h2 class="price">379.000đ</h2>
            <div class="line"></div>
            <label class="title-size">Kích thước</label>
            <div>
                <select class="size">
                    <option>S</option>
                    <option>M</option>
                    <option>L</option>
                    <option>XL</option>
                </select>
            </div>

            <label class="title-count">Số lượng</label>
            <div>
                <input class="count" type="number" value="1" min="1">
            </div>


            <div class="btn-group">
                <button class="add-cart"><i class="fa-solid fa-cart-arrow-down"></i> Thêm giỏ hàng</button>
                <button class="buy">Đặt hàng</button>
            </div>
        </div>
    </div>

    <div class="description">
        <h2>MÔ TẢ SẢN PHẨM</h2>
        <div class="line"></div>
        <p>
            Áo thể thao HKT - The sport T-Shirt
            Nếu bạn yêu thích phong cách thể thao đậm chất đường phố và thời trang hiện đại,
            chiếc The Sport T-Shirt chính là lựa chọn hoàn hảo. Với thiết kế mang hơi hướng cổ
            điển kết hợp sự sáng tạo đương đại, sản phẩm không chỉ nổi bật trong các hoạt động
            thể thao mà còn hoàn hảo để phối đồ trong mọi hoàn cảnh.
        </p>

        <p>
        <h2>Điểm Nổi Bật Của Sản Phẩm</h2>
        <b>Chất Liệu Cao Cấp – Bền Bỉ và Thoáng Khí.</b> <br>
        <br>
        <b>Vải cotton dày dặn và thoáng khí:</b> Mang lại cảm giác dễ chịu suốt cả ngày dài, đặc biệt trong các
        hoạt động di chuyển nhiều. <br><br>
        <b>Patch thêu tỉ mỉ:</b> Logo và số áo được gia công sắc nét, đảm bảo độ bền bỉ và không phai
        màu.<br><br>
        <b>Đường may chắc chắn:</b> Gia công kỹ lưỡng ở từng chi tiết, đảm bảo chất lượng bền bỉ theo thời
        gian.<br><br>
        <b>Form Oversize Thời Thượng</b> – Phù Hợp Mọi Vóc Dáng<br><br>
        <b>Kiểu dáng rộng rãi, dễ phối đồ:</b> Phối cùng quần jeans, short hoặc jogger đều tạo nên diện mạo năng
        động và phong cách.<br><br>
        <b>Phong cách linh hoạt:</b> Dù là đi chơi, đi học hay tham gia các sự kiện thể thao, chiếc áo đều phù
        hợp.<br><br>
        </p>

        <h2>Bảng Size và Tư Vấn Chọn Size</h2>
        <div style="text-align:center;">
            <img src="../assets/images/size.png" alt="" style="width:70%; height:auto;">
        </div>
        <p><b>Form rộng:</b> Thoải mái lựa chọn size tùy vào phong cách (vừa vặn hoặc oversize).
            Tư vấn tận tình: Đội ngũ HKT luôn sẵn sàng hỗ trợ để bạn chọn size chuẩn xác nhất.</p>

    </div>

    <div class="other-products">
        <h2>SẢN PHẨM KHÁC</h2>
        <div class="line"></div>

        <section class="product-list">

            <div class="product-item">
                <img src="../upload/Ao321.png" alt="">
                <h3>Áo thể thao HKT - Sport T-Shirt</h3>
                <p class="price">379.000đ</p>
            </div>

            <div class="product-item">
                <img src="../upload/Ao321.png" alt="">
                <h3>Áo thể thao HKT - Sport T-Shirt</h3>
                <p class="price">379.000đ</p>
            </div>

            <div class="product-item">
                <img src="../upload/Ao321.png" alt="">
                <h3>Áo thể thao HKT - Sport T-Shirt</h3>
                <p class="price">379.000đ</p>
            </div>

            <div class="product-item">
                <img src="../upload/Ao321.png" alt="">
                <h3>Áo thể thao HKT - Sport T-Shirt</h3>
                <p class="price">379.000đ</p>
            </div>

            <div class="product-item">
                <img src="../upload/Ao321.png" alt="">
                <h3>Áo thể thao HKT - Sport T-Shirt</h3>
                <p class="price">379.000đ</p>
            </div>

            <div class="product-item">
                <img src="../upload/Ao321.png" alt="">
                <h3>Áo thể thao HKT - Sport T-Shirt</h3>
                <p class="price">379.000đ</p>
            </div>

            <div class="product-item">
                <img src="../upload/Ao321.png" alt="">
                <h3>Áo thể thao HKT - Sport T-Shirt</h3>
                <p class="price">379.000đ</p>
            </div>

            <div class="product-item">
                <img src="../upload/Ao321.png" alt="">
                <h3>Áo thể thao HKT - Sport T-Shirt</h3>
                <p class="price">379.000đ</p>
            </div>

            <div class="product-item">
                <img src="../upload/Ao321.png" alt="">
                <h3>Áo thể thao HKT - Sport T-Shirt</h3>
                <p class="price">379.000đ</p>
            </div>

            <div class="product-item">
                <img src="../upload/Ao321.png" alt="">
                <h3>Áo thể thao HKT - Sport T-Shirt</h3>
                <p class="price">379.000đ</p>
            </div>

            <div class="product-item">
                <img src="../upload/Ao321.png" alt="">
                <h3>Áo thể thao HKT - Sport T-Shirt</h3>
                <p class="price">379.000đ</p>
            </div>

            <div class="product-item">
                <img src="../upload/Ao321.png" alt="">
                <h3>Áo thể thao HKT - Sport T-Shirt</h3>
                <p class="price">379.000đ</p>
            </div>

        </section>
    </div>

</div>

<?php
// Capture output and include layout
$page_content = ob_get_clean();
include('../includes/layout.php');
?>