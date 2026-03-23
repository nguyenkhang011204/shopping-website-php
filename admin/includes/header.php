<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

$admin_name    = isset($_SESSION['user_name'])  ? htmlspecialchars($_SESSION['user_name'])  : 'Admin';
$admin_email   = isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '';
$admin_role    = isset($_SESSION['user_role'])  ? htmlspecialchars($_SESSION['user_role'])  : 'admin';
$avatar_letter = mb_strtoupper(mb_substr($admin_name, 0, 1, 'UTF-8'), 'UTF-8');
$role_label    = $admin_role === 'admin' ? 'Admin' : 'Nhân viên';
$current_text  = htmlspecialchars($text_title ?? 'Dashboard');
?>

<!-- Backdrop overlay (mobile sidebar) -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<header class="admin-header">

    <!-- Left: hamburger + title -->
    <div class="header-left">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle menu">
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

        <div class="header-search" id="headerSearch">
            <button class="search-icon-btn" id="searchToggle" aria-label="Tìm kiếm">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
            <input type="text" id="searchInput" class="search-input" placeholder="Tìm kiếm..." aria-label="Tìm kiếm">
            <button class="search-close-btn" id="searchClose" aria-label="Đóng">
                <i class="fa-solid fa-xmark"></i>
            </button>
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

        <div class="header-sep header-sep-right"></div>

        <!-- Admin profile dropdown — same structure as storefront user-dropdown -->
        <div class="user-dropdown admin-user-dropdown">
            <button class="user-avatar-btn" type="button" aria-label="Tài khoản">
                <span class="user-avatar"><?= $avatar_letter ?></span>
                <span class="user-avatar-name"><?= $admin_name ?></span>
                <i class="fa-solid fa-chevron-down user-chevron"></i>
            </button>

            <ul class="user-dropdown-menu">

                <li class="user-dropdown-header">
                    <span class="udh-name"><?= $admin_name ?></span>
                    <span class="udh-email"><?= $admin_email ?></span>
                </li>

                <li class="user-dropdown-li">
                    <a href="../pages/profile.php" class="user-dropdown-item">
                        <i class="fa-regular fa-user"></i>
                        Tài khoản của tôi
                    </a>
                </li>

                <li class="user-dropdown-li">
                    <a href="../home.php" class="user-dropdown-item">
                        <i class="fa-solid fa-store"></i>
                        Về cửa hàng
                    </a>
                </li>

                <li class="user-dropdown-divider"></li>

                <li class="user-dropdown-li">
                    <a href="../pages/logout.php" class="user-dropdown-item user-dropdown-logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        Đăng xuất
                    </a>
                </li>

            </ul>
        </div>

    </div>

</header>

<script>
    (function() {
        /* ── Sidebar toggle ── */
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');

        function openSidebar() {
            sidebar.classList.add('open');
            sidebarBackdrop.classList.add('active');
            sidebarToggle.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            sidebarBackdrop.classList.remove('active');
            sidebarToggle.classList.remove('active');
            document.body.style.overflow = '';
        }

        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
        });

        sidebarBackdrop.addEventListener('click', closeSidebar);

        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) closeSidebar();
        });

        document.querySelectorAll('.sidebar .menu a').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) closeSidebar();
            });
        });

        /* ── Mobile search expand/collapse ── */
        const searchBox = document.getElementById('headerSearch');
        const searchToggle = document.getElementById('searchToggle');
        const searchInput = document.getElementById('searchInput');
        const searchClose = document.getElementById('searchClose');

        function openSearch() {
            searchBox.classList.add('expanded');
            searchInput.focus();
        }

        function closeSearch() {
            searchBox.classList.remove('expanded');
            searchInput.value = '';
        }

        searchToggle.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                searchBox.classList.contains('expanded') ? closeSearch() : openSearch();
            } else {
                searchInput.focus();
            }
        });

        searchClose.addEventListener('click', closeSearch);

        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 &&
                !searchBox.contains(e.target) &&
                searchBox.classList.contains('expanded')) {
                closeSearch();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && searchBox.classList.contains('expanded')) {
                closeSearch();
            }
        });

        /* ── Profile dropdown: click toggle (supplements CSS :hover) ── */
        const profileDropdown = document.querySelector('.admin-user-dropdown');
        if (profileDropdown) {
            const btn = profileDropdown.querySelector('.user-avatar-btn');
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                profileDropdown.classList.toggle('open');
            });
            document.addEventListener('click', function() {
                profileDropdown.classList.remove('open');
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') profileDropdown.classList.remove('open');
            });
        }
    })();
</script>