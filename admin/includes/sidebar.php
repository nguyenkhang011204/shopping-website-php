<?php
// $page is set by admin/index.php
$page = $page ?? 'dashboard';
?>

<aside class="sidebar">

    <!-- Logo — same as storefront nav-logo -->
    <div class="logo">
        <img src="../assets/images/logo.png" alt="HKT Shop">
    </div>

    <nav class="menu">

        <a href="index.php?page=dashboard" class="<?= $page === 'dashboard' ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i>
            Trang quản trị
        </a>

        <div class="menu-divider"></div>

        <div class="menu-label">Cửa hàng</div>

        <a href="index.php?page=products" class="<?= $page === 'products' ? 'active' : '' ?>">
            <i class="fa-solid fa-shirt"></i>
            Sản phẩm
        </a>

        <a href="index.php?page=category" class="<?= $page === 'category' ? 'active' : '' ?>">
            <i class="fa-solid fa-tags"></i>
            Danh mục
        </a>

        <a href="index.php?page=manageOrder" class="<?= $page === 'manageOrder' ? 'active' : '' ?>">
            <i class="fa-solid fa-cart-shopping"></i>
            Đơn hàng
        </a>

        <div class="menu-divider"></div>

        <div class="menu-label">Người dùng</div>

        <a href="index.php?page=clients" class="<?= $page === 'clients' ? 'active' : '' ?>">
            <i class="fa-solid fa-user"></i>
            Khách hàng
        </a>
    </nav>

</aside>