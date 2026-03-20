<?php
$base_path = isset($base_path) ? $base_path : "";
?>

<nav class="header">

    <!-- LEFT: Logo (desktop) / Hamburger (mobile) -->
    <div class="nav-left">
        <div class="menu-toggle"><i class="fa-solid fa-bars"></i></div>
        <a href="<?php echo $base_path; ?>home.php" class="nav-logo">
            <img src="<?php echo $base_path; ?>assets/images/logo.png" alt="logo">
        </a>
    </div>

    <!-- CENTER: Nav links -->
    <ul class="nav-links">
        <li><a href="<?php echo $base_path; ?>home.php">TRANG CHỦ</a></li>

        <li class="dropdown">
            <a href="<?php echo $base_path; ?>pages/product.php">SẢN PHẨM ▾</a>
            <ul class="dropdown-menu">
                <li><a href="<?php echo $base_path; ?>pages/product.php">TẤT CẢ SẢN PHẨM</a></li>
                <li><a href="#">SẢN PHẨM MỚI</a></li>
                <li><a href="#">SẢN PHẨM NỔI BẬT</a></li>
                <li><a href="#">SẢN PHẨM BÁN CHẠY</a></li>
            </ul>
        </li>

        <li class="dropdown">
            <a href="#">NAM ▾</a>
            <ul class="dropdown-menu">
                <li><a href="#">ÁO</a></li>
                <li><a href="#">QUẦN</a></li>
                <li><a href="#">GIÀY</a></li>
            </ul>
        </li>

        <li class="dropdown">
            <a href="#">NỮ ▾</a>
            <ul class="dropdown-menu">
                <li><a href="#">ÁO</a></li>
                <li><a href="#">QUẦN</a></li>
                <li><a href="#">GIÀY</a></li>
            </ul>
        </li>

        <li class="hide-on-tablet"><a href="#">VỀ CHÚNG TÔI</a></li>
        <li class="hide-on-tablet"><a href="#">ĐƠN HÀNG</a></li>

        <li class="dropdown tablet-only">
            <a href="#">KHÁC ▾</a>
            <ul class="dropdown-menu">
                <li><a href="#">VỀ CHÚNG TÔI</a></li>
                <li><a href="#">ĐƠN HÀNG</a></li>
            </ul>
        </li>
    </ul>

    <!-- RIGHT: User + Cart icons -->
    <div class="nav-icons">
        <div class="user-dropdown">
            <a href="#" class="user"><i class="fa-solid fa-user"></i></a>
            <div class="user-dropdown-menu">
                <a href="<?php echo $base_path; ?>pages/signIn.php" class="user-dropdown-item">Đăng nhập</a>
                <a href="<?php echo $base_path; ?>pages/signUp.php" class="user-dropdown-item">Đăng ký</a>
            </div>
        </div>
        <a href="#" class="cart">
            <i class="fa-solid fa-bag-shopping"></i>
            <span class="cart-count">0</span>
        </a>
    </div>

    <!-- Mobile: Logo centered (absolute) -->
    <div class="nav-logo-mobile">
        <a href="<?php echo $base_path; ?>home.php">
            <img src="<?php echo $base_path; ?>assets/images/logo.png" alt="logo">
        </a>
    </div>

</nav>

<div class="search-box-mobile">
    <input type="text" placeholder="Tìm kiếm ...">
    <button><i class="fa-solid fa-magnifying-glass"></i></button>
</div>

<script src="<?php echo $base_path; ?>assets/js/header.js"></script>