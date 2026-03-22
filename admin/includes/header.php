<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

$admin_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin';
$admin_role = isset($_SESSION['user_role']) ? htmlspecialchars($_SESSION['user_role']) : 'Admin';
$avatar_letter = mb_strtoupper(mb_substr($admin_name, 0, 1, 'UTF-8'), 'UTF-8');
$role_label = $admin_role === 'admin' ? 'Admin' : 'Nhân viên';
?>

<header class="admin-header">

    <!-- Left: current page title (set in admin/index.php as $text_title) -->
    <h1 class="header-title">
        <?= htmlspecialchars($text_title ?? 'Dashboard') ?>
    </h1>

    <!-- Right: actions -->
    <div class="header-right">

        <!-- Back to storefront -->
        <a href="../home.php" class="header-shop-link" title="Xem cửa hàng">
            <i class="fa-solid fa-store"></i>
            <span>Xem cửa hàng</span>
        </a>

        <!-- Notifications -->
        <button class="header-icon-btn" title="Thông báo">
            <i class="fa-solid fa-bell"></i>
            <span class="badge"></span>
        </button>

        <!-- Messages -->
        <button class="header-icon-btn" title="Tin nhắn">
            <i class="fa-solid fa-message"></i>
        </button>

        <!-- Admin profile — same pattern as storefront user-avatar-btn -->
        <a href="index.php?page=profile" class="admin-profile">
            <div class="admin-avatar"><?= $avatar_letter ?></div>
            <div class="admin-profile-info">
                <span class="admin-profile-name"><?= $admin_name ?></span>
                <span class="admin-profile-role"><?= $role_label ?></span>
            </div>
        </a>

    </div>

</header>