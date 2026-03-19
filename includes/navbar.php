<nav class="navbar">

    <!-- Mobile Top Bar -->
    <div class="nav-mobile">
        <div class="menu-toggle"><i class="fa-solid fa-bars"></i></div>
        <div class="logo"><img src="assets/images/logo.png" alt=""></div>
        
        <div class="cart">
            <i class="fa fa-user"></i>
            <i class="fa-solid fa-bag-shopping"></i>
        </div>
    </div>
    

    <!-- Main Menu -->
    <ul class="nav-links">

        <li><a href="home.php">TRANG CHỦ</a></li>

        <li class="dropdown">
            <a href="pages/product.php">SẢN PHẨM ▾</a>
            <ul class="dropdown-menu">
                <li><a href="pages/product.php">TẤT CẢ SẢN PHẨM</a></li>
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

        <!-- Tablet Only -->
        <li class="dropdown tablet-only">
            <a href="#">KHÁC ▾</a>
            <ul class="dropdown-menu">
                <li><a href="#">VỀ CHÚNG TÔI</a></li>
                <li><a href="#">ĐƠN HÀNG</a></li>
            </ul>
        </li>

    </ul>
</nav>

<div class="search-box-mobile">
    <input type="text" placeholder="Tìm kiếm ...">
    <button>
        <i class="fa-solid fa-magnifying-glass"></i>
    </button>
</div>

<script src="../assets/js/navbar.js"></script>