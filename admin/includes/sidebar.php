<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo"><img src="../assets/images/logo.png" alt=""></div>
    <div class="menu">

        <a href="index.php?page=dashboard" class="<?= $page == 'dashboard' ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i>Trang quản trị
        </a>
        <a href="index.php?page=products" class="<?= $page == 'products' ? 'active' : '' ?>">
            <i class=" fa-solid fa-shirt"></i> Sản phẩm
        </a>
        <a href="index.php?page=orders" class="<?= $page == 'orders' ? 'active' : '' ?>">
            <i class="fa-solid fa-cart-shopping"></i> Đơn hàng
        </a>
        <a href="index.php?page=clients" class="<?= $page == 'clients' ? 'active' : '' ?>">
            <i class="fa-solid fa-user"></i> Khách hàng
        </a>
        <a href="index.php?page=category" class="<?= $page == 'category' ? 'active' : '' ?>">
            <i class="fa-solid fa-vest"></i> Danh mục
        </a>
        <a href="index.php?page=staffs" class="<?= $page == 'staffs' ? 'active' : '' ?>">
            <i class="fa-solid fa-users"></i> Nhân viên
        </a>
    </div>
</div>