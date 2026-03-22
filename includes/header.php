<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$base_path     = isset($base_path) ? $base_path : "";
$is_logged_in  = isset($_SESSION['user_id']);
$user_name     = $is_logged_in ? htmlspecialchars($_SESSION['user_name'])  : '';
$user_email    = $is_logged_in ? htmlspecialchars($_SESSION['user_email']) : '';
$avatar_letter = $is_logged_in
    ? mb_strtoupper(mb_substr($_SESSION['user_name'], 0, 1, 'UTF-8'), 'UTF-8')
    : '';
$first_name    = $is_logged_in ? explode(' ', trim($_SESSION['user_name']))[0] : '';

$current_page   = basename($_SERVER['PHP_SELF']);
$hide_login_btn = in_array($current_page, ['signin.php', 'signup.php']);

// ── Active nav detection ──────────────────────────────────
$current_uri  = $_SERVER['REQUEST_URI'] ?? '';
$current_path = parse_url($current_uri, PHP_URL_PATH) ?? '';
$query_string = $_SERVER['QUERY_STRING'] ?? '';
parse_str($query_string, $query_params);

$current_sort     = $query_params['sort']     ?? '';
$current_category = $query_params['category'] ?? '';

$is_product_page = str_contains($current_path, 'product.php')
    && !str_contains($current_path, 'product_detail.php');

$nav_active_home    = str_contains($current_path, 'home.php') ? 'active' : '';
$nav_active_new     = ($is_product_page && $current_sort === 'newest')    ? 'active' : '';
$nav_active_sale    = ($is_product_page && $current_sort === 'price_asc') ? 'active' : '';
// "Sản phẩm" active only when not on a special sort link
$nav_active_product = ($is_product_page && $current_sort !== 'newest' && $current_sort !== 'price_asc') ? 'active' : '';
?>

<div class="nav-backdrop" id="navBackdrop"></div>

<nav class="header" id="mainNav">

    <!-- LEFT: hamburger + logo -->
    <div class="nav-left">
        <button class="menu-toggle" id="menuToggle" aria-label="Menu">
            <i class="fa-solid fa-bars"></i>
        </button>
        <a href="<?= $base_path ?>home.php" class="nav-logo">
            <img src="<?= $base_path ?>assets/images/logo.png" alt="HKT Shop">
        </a>
    </div>

    <!-- CENTER: nav links -->
    <ul class="nav-links" id="navLinks">

        <!-- Trang chủ -->
        <li>
            <a href="<?= $base_path ?>home.php" class="<?= $nav_active_home ?>">
                Trang chủ
            </a>
        </li>

        <!-- Hàng mới -->
        <li>
            <a href="<?= $base_path ?>pages/product.php?sort=newest" class="<?= $nav_active_new ?>">
                Hàng mới
                <span class="nav-badge">New</span>
            </a>
        </li>

        <!-- Sản phẩm — mega dropdown -->
        <li class="dropdown">
            <a href="<?= $base_path ?>pages/product.php" class="<?= $nav_active_product ?>">
                Sản phẩm <i class="fa-solid fa-chevron-down chevron"></i>
            </a>
            <ul class="dropdown-menu">
                <li class="drop-label">Nam</li>
                <li><a href="<?= $base_path ?>pages/product.php?category=ao-nam">Áo nam</a></li>
                <li><a href="<?= $base_path ?>pages/product.php?category=quan-nam">Quần nam</a></li>

                <li class="drop-divider"></li>
                <li class="drop-label">Nữ</li>
                <li><a href="<?= $base_path ?>pages/product.php?category=ao-nu">Áo nữ</a></li>
                <li><a href="<?= $base_path ?>pages/product.php?category=quan-nu">Quần nữ</a></li>
                <li><a href="<?= $base_path ?>pages/product.php?category=dam-vay">Đầm & Váy</a></li>

                <li class="drop-divider"></li>
                <li class="drop-label">Khác</li>
                <li><a href="<?= $base_path ?>pages/product.php?category=ao-khoac">Áo khoác</a></li>
                <li><a href="<?= $base_path ?>pages/product.php?category=giay-dep">Giày & Dép</a></li>
                <li><a href="<?= $base_path ?>pages/product.php?category=phu-kien">Phụ kiện</a></li>
                <li><a href="<?= $base_path ?>pages/product.php?category=do-the-thao">Đồ thể thao</a></li>
            </ul>
        </li>

        <!-- Sale -->
        <li>
            <a href="<?= $base_path ?>pages/product.php?sort=price_asc" class="nav-sale <?= $nav_active_sale ?>">
                Sale <i class="fa-solid fa-bolt"></i>
            </a>
        </li>

        <!-- Về chúng tôi -->
        <li>
            <a href="#">Về chúng tôi</a>
        </li>

    </ul>

    <!-- RIGHT: auth -->
    <div class="nav-icons">

        <?php if ($is_logged_in): ?>
            <div class="user-dropdown">
                <button class="user-avatar-btn" type="button">
                    <span class="user-avatar"><?= $avatar_letter ?></span>
                    <span class="user-avatar-name"><?= htmlspecialchars($first_name) ?></span>
                    <i class="fa-solid fa-chevron-down user-chevron"></i>
                </button>
                <ul class="user-dropdown-menu">
                    <li class="user-dropdown-header">
                        <span class="udh-name"><?= $user_name ?></span>
                        <span class="udh-email"><?= $user_email ?></span>
                    </li>
                    <li class="user-dropdown-li">
                        <a href="<?= $base_path ?>pages/profile.php" class="user-dropdown-item">
                            <i class="fa-regular fa-user"></i> Tài khoản của tôi
                        </a>
                    </li>
                    <li class="user-dropdown-li">
                        <a href="<?= $base_path ?>pages/order.php" class="user-dropdown-item">
                            <i class="fa-regular fa-rectangle-list"></i> Đơn hàng của tôi
                        </a>
                    </li>
                    <li class="user-dropdown-divider"></li>
                    <li class="user-dropdown-li">
                        <a href="<?= $base_path ?>pages/logout.php" class="user-dropdown-item user-dropdown-logout">
                            <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                        </a>
                    </li>
                </ul>
            </div>

        <?php elseif (!$hide_login_btn): ?>
            <a href="<?= $base_path ?>pages/signin.php" class="btn-login">
                <i class="fa-regular fa-user"></i>
                <span>Đăng nhập</span>
            </a>
        <?php endif; ?>

    </div>

    <!-- Mobile: centered logo -->
    <div class="nav-logo-mobile">
        <a href="<?= $base_path ?>home.php">
            <img src="<?= $base_path ?>assets/images/logo.png" alt="HKT Shop">
        </a>
    </div>

</nav>

<script>
    (function() {
        const nav = document.getElementById('mainNav');
        const toggle = document.getElementById('menuToggle');
        const links = document.getElementById('navLinks');
        const backdrop = document.getElementById('navBackdrop');

        // Frosted glass on scroll
        window.addEventListener('scroll', () => {
            nav.classList.toggle('scrolled', window.scrollY > 10);
        }, {
            passive: true
        });

        // Open / close drawer
        function openMenu() {
            links.classList.add('active');
            backdrop.classList.add('active');
            toggle.innerHTML = '<i class="fa-solid fa-xmark"></i>';
        }

        function closeMenu() {
            links.classList.remove('active');
            backdrop.classList.remove('active');
            toggle.innerHTML = '<i class="fa-solid fa-bars"></i>';
            document.querySelectorAll('.nav-links .dropdown.active')
                .forEach(el => el.classList.remove('active'));
        }

        toggle.addEventListener('click', () =>
            links.classList.contains('active') ? closeMenu() : openMenu()
        );
        backdrop.addEventListener('click', closeMenu);

        // Mobile accordion dropdowns
        document.querySelectorAll('.nav-links .dropdown > a').forEach(a => {
            a.addEventListener('click', e => {
                if (window.innerWidth > 768) return;
                e.preventDefault();
                const li = a.parentElement;
                const isOpen = li.classList.contains('active');
                document.querySelectorAll('.nav-links .dropdown.active')
                    .forEach(el => el.classList.remove('active'));
                if (!isOpen) li.classList.add('active');
            });
        });
    })();
</script>