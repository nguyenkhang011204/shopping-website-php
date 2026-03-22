<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();

$base_path    = isset($base_path) ? $base_path : "";
$is_logged_in = isset($_SESSION['user_id']);
$user_name    = $is_logged_in ? htmlspecialchars($_SESSION['user_name'])  : '';
$user_email   = $is_logged_in ? htmlspecialchars($_SESSION['user_email']) : '';
$avatar_letter = $is_logged_in
    ? mb_strtoupper(mb_substr($_SESSION['user_name'], 0, 1, 'UTF-8'), 'UTF-8')
    : '';
$first_name = $is_logged_in ? explode(' ', trim($_SESSION['user_name']))[0] : '';

// Hide the login button on auth pages
$current_page = basename($_SERVER['PHP_SELF']);
$hide_login_btn = in_array($current_page, ['signin.php', 'signup.php']);
?>

<nav class="header">

    <!-- LEFT: Logo (desktop) / Hamburger (mobile) -->
    <div class="nav-left">
        <div class="menu-toggle"><i class="fa-solid fa-bars"></i></div>
        <a href="<?php echo $base_path; ?>home.php" class="nav-logo no-line">
            <img src="<?php echo $base_path; ?>assets/images/logo.png" alt="HKT Shop">
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

        <li class="dropdown tablet-only">
            <a href="#">KHÁC ▾</a>
            <ul class="dropdown-menu">
                <li><a href="#">VỀ CHÚNG TÔI</a></li>
            </ul>
        </li>
    </ul>

    <!-- RIGHT: Auth area -->
    <div class="nav-icons">

        <?php if ($is_logged_in): ?>

            <!-- ── Logged in: avatar + dropdown ── -->
            <div class="user-dropdown">

                <button class="user-avatar-btn" aria-label="Tài khoản" type="button">
                    <span class="user-avatar"><?php echo $avatar_letter; ?></span>
                    <span class="user-avatar-name"><?php echo htmlspecialchars($first_name); ?></span>
                    <i class="fa-solid fa-chevron-down user-chevron"></i>
                </button>

                <ul class="user-dropdown-menu">

                    <!-- User info header -->
                    <li class="user-dropdown-header">
                        <span class="udh-name"><?php echo $user_name; ?></span>
                        <span class="udh-email"><?php echo $user_email; ?></span>
                    </li>

                    <!-- Menu items -->
                    <li class="user-dropdown-li">
                        <a href="<?php echo $base_path; ?>pages/profile.php" class="user-dropdown-item">
                            <i class="fa-regular fa-user"></i> Tài khoản của tôi
                        </a>
                    </li>
                    <li class="user-dropdown-li">
                        <a href="<?php echo $base_path; ?>pages/order.php" class="user-dropdown-item">
                            <i class="fa-regular fa-rectangle-list"></i> Đơn hàng của tôi
                        </a>
                    </li>

                    <li class="user-dropdown-divider"></li>

                    <li class="user-dropdown-li">
                        <a href="<?php echo $base_path; ?>pages/logout.php" class="user-dropdown-item user-dropdown-logout">
                            <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                        </a>
                    </li>

                </ul>
            </div>

        <?php else: ?>

            <!-- ── Guest: Đăng nhập button ── -->
            <?php if (!$hide_login_btn): ?>
                <a href="<?php echo $base_path; ?>pages/signin.php" class="btn-login no-line">
                    <i class="fa-regular fa-user"></i>
                    <span>Đăng nhập</span>
                </a>
            <?php endif; ?>

        <?php endif; ?>

    </div>

    <!-- Mobile: Logo centered (absolute) -->
    <div class="nav-logo-mobile no-line">
        <a href="<?php echo $base_path; ?>home.php">
            <img src="<?php echo $base_path; ?>assets/images/logo.png" alt="HKT Shop">
        </a>
    </div>

</nav>

<script src="<?php echo $base_path; ?>assets/js/header.js"></script>