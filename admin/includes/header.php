<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

$admin_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin';
$admin_role = isset($_SESSION['user_role']) ? htmlspecialchars($_SESSION['user_role']) : 'admin';
$avatar_letter = mb_strtoupper(mb_substr($admin_name, 0, 1, 'UTF-8'), 'UTF-8');
$role_label = $admin_role === 'admin' ? 'Admin' : 'Nhân viên';
$current_text = htmlspecialchars($text_title ?? 'Dashboard');
?>

<!-- Backdrop overlay (mobile) -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<header class="admin-header">

    <!-- Left: hamburger + title -->
    <div class="header-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <span class="toggle-bar"></span>
            <span class="toggle-bar"></span>
            <span class="toggle-bar"></span>
        </button>
        <div class="header-title-wrap">
            <span class="header-breadcrumb">HKT Shop &rsaquo; Admin</span>
            <h1 class="header-title"><?= $current_text ?></h1>
        </div>
    </div>

    <!-- Right: search + actions + profile -->
    <div class="header-right">

        <!-- Search -->
        <div class="header-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Tìm kiếm...">
        </div>

        <div class="header-sep"></div>

        <!-- Back to storefront -->
        <a href="../home.php" class="header-shop-link" title="Xem cửa hàng">
            <i class="fa-solid fa-store"></i>
            <span class="shop-link-text">Cửa hàng</span>
        </a>

        <!-- Notifications -->
        <button class="header-icon-btn" title="Thông báo">
            <i class="fa-solid fa-bell"></i>
            <span class="notif-dot"></span>
        </button>

        <!-- Messages -->
        <button class="header-icon-btn" title="Tin nhắn">
            <i class="fa-solid fa-message"></i>
        </button>

        <div class="header-sep"></div>

        <!-- Admin profile pill -->
        <a href="index.php?page=profile" class="admin-profile">
            <div class="admin-avatar"><?= $avatar_letter ?></div>
            <div class="admin-profile-info">
                <span class="admin-profile-name"><?= $admin_name ?></span>
                <span class="admin-profile-role"><?= $role_label ?></span>
            </div>
        </a>

    </div>

</header>

<script>
    (function () {
        const toggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        function openSidebar() {
            sidebar.classList.add('open');
            backdrop.classList.add('active');
            toggle.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            backdrop.classList.remove('active');
            toggle.classList.remove('active');
            document.body.style.overflow = '';
        }

        function toggleSidebar() {
            sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
        }

        toggle.addEventListener('click', toggleSidebar);
        backdrop.addEventListener('click', closeSidebar);

        // Close on resize back to desktop
        window.addEventListener('resize', function () {
            if (window.innerWidth > 768) closeSidebar();
        });

        // Close when a sidebar link is tapped on mobile
        document.querySelectorAll('.sidebar .menu a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 768) closeSidebar();
            });
        });
    })();
</script>