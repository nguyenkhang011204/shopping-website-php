<?php
// pages/profile.php
session_start();

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit;
}

require_once '../includes/dbconnect.php';

$user_id  = (int) $_SESSION['user_id'];
$is_admin = ($_SESSION['user_role'] ?? '') === 'admin';
$tab = $_GET['tab'] ?? ($is_admin ? 'password' : 'info');

// Flash messages (survive POST-Redirect-GET)
$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error   = $_SESSION['flash_error']   ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── POST handlers ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirect_tab = $_POST['tab'] ?? 'info';

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['flash_error'] = 'Yêu cầu không hợp lệ. Vui lòng thử lại.';
        header("Location: profile.php?tab={$redirect_tab}");
        exit;
    }

    $action = $_POST['action'] ?? '';

    // ── Update profile info (customers only) ────────────────────────────
    if ($action === 'update_profile' && $is_admin) {
        $_SESSION['flash_error'] = 'Tài khoản admin không thể thay đổi thông tin cá nhân.';
        header("Location: profile.php?tab=password");
        exit;
    }

    if ($action === 'update_profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone     = trim($_POST['phone']     ?? '');

        if (mb_strlen($full_name) < 2) {
            $_SESSION['flash_error'] = 'Họ tên phải có ít nhất 2 ký tự.';
        } elseif ($phone !== '' && !preg_match('/^[0-9]{10,12}$/', $phone)) {
            $_SESSION['flash_error'] = 'Số điện thoại không hợp lệ (10–12 chữ số).';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$full_name, $phone ?: null, $user_id]);
            $_SESSION['user_name'] = $full_name;
            $_SESSION['flash_success'] = 'Cập nhật thông tin thành công.';
        }
        header("Location: profile.php?tab=info");
        exit;
    }

    // ── Change password ──────────────────────────────────────────────────
    if ($action === 'change_password') {
        $old_password     = $_POST['old_password']     ?? '';
        $new_password     = $_POST['new_password']     ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch();

        if (!password_verify($old_password, $row['password_hash'])) {
            $_SESSION['flash_error'] = 'Mật khẩu hiện tại không đúng.';
        } elseif (strlen($new_password) < 6) {
            $_SESSION['flash_error'] = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
        } elseif ($new_password !== $confirm_password) {
            $_SESSION['flash_error'] = 'Xác nhận mật khẩu mới không khớp.';
        } else {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $user_id]);
            $_SESSION['flash_success'] = 'Đổi mật khẩu thành công.';
        }
        header("Location: profile.php?tab=password");
        exit;
    }

    // ── Address actions (customers only) ────────────────────────────────
    if ($is_admin && in_array($action, ['save_address', 'delete_address', 'set_default_address'])) {
        $_SESSION['flash_error'] = 'Tài khoản admin không thể quản lý địa chỉ.';
        header("Location: profile.php?tab=password");
        exit;
    }

    // ── Save address (add or edit) ───────────────────────────────────────
    if ($action === 'save_address') {
        $addr_id        = (int) ($_POST['address_id']    ?? 0);
        $recipient_name = trim($_POST['recipient_name']  ?? '');
        $addr_phone     = trim($_POST['addr_phone']      ?? '');
        $street         = trim($_POST['street']          ?? '');
        $district       = trim($_POST['district']        ?? '');
        $city           = trim($_POST['city']            ?? '');
        $is_default     = isset($_POST['is_default']) ? 1 : 0;

        $err = '';
        if (mb_strlen($recipient_name) < 2)          $err = 'Vui lòng nhập tên người nhận.';
        elseif (!preg_match('/^[0-9]{10,12}$/', $addr_phone)) $err = 'Số điện thoại không hợp lệ (10–12 chữ số).';
        elseif (mb_strlen($street) < 3)               $err = 'Vui lòng nhập địa chỉ cụ thể.';
        elseif (empty($city))                         $err = 'Vui lòng chọn tỉnh/thành phố.';

        if ($err) {
            $_SESSION['flash_error'] = $err;
        } else {
            if ($is_default) {
                $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$user_id]);
            }
            if ($addr_id > 0) {
                $stmt = $pdo->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ?");
                $stmt->execute([$addr_id, $user_id]);
                if ($stmt->fetch()) {
                    $pdo->prepare(
                        "UPDATE addresses SET recipient_name=?, phone=?, street=?, district=?, city=?, is_default=? WHERE id=? AND user_id=?"
                    )->execute([$recipient_name, $addr_phone, $street, $district, $city, $is_default, $addr_id, $user_id]);
                    $_SESSION['flash_success'] = 'Cập nhật địa chỉ thành công.';
                }
            } else {
                $pdo->prepare(
                    "INSERT INTO addresses (user_id, recipient_name, phone, street, district, city, is_default) VALUES (?,?,?,?,?,?,?)"
                )->execute([$user_id, $recipient_name, $addr_phone, $street, $district, $city, $is_default]);
                $_SESSION['flash_success'] = 'Thêm địa chỉ mới thành công.';
            }
        }
        header("Location: profile.php?tab=addresses");
        exit;
    }

    // ── Delete address ───────────────────────────────────────────────────
    if ($action === 'delete_address') {
        $addr_id = (int) ($_POST['address_id'] ?? 0);
        $pdo->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?")->execute([$addr_id, $user_id]);
        $_SESSION['flash_success'] = 'Đã xóa địa chỉ.';
        header("Location: profile.php?tab=addresses");
        exit;
    }

    // ── Set default address ──────────────────────────────────────────────
    if ($action === 'set_default_address') {
        $addr_id = (int) ($_POST['address_id'] ?? 0);
        $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$user_id]);
        $pdo->prepare("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?")->execute([$addr_id, $user_id]);
        $_SESSION['flash_success'] = 'Đã đặt địa chỉ mặc định.';
        header("Location: profile.php?tab=addresses");
        exit;
    }
}

// ── Fetch user data ──────────────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT full_name, email, phone, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// ── Fetch addresses ──────────────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id ASC");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll();

// ── Edit address pre-fill ────────────────────────────────────────────────────
$edit_address = null;
$show_addr_form = isset($_GET['add_address']);
if ($tab === 'addresses' && isset($_GET['edit_id'])) {
    $edit_id = (int) $_GET['edit_id'];
    foreach ($addresses as $addr) {
        if ((int) $addr['id'] === $edit_id) {
            $edit_address = $addr;
            $show_addr_form = true;
            break;
        }
    }
}

// ── Vietnamese provinces ─────────────────────────────────────────────────────
$vn_provinces = [
    'An Giang', 'Bà Rịa - Vũng Tàu', 'Bắc Giang', 'Bắc Kạn', 'Bạc Liêu',
    'Bắc Ninh', 'Bến Tre', 'Bình Định', 'Bình Dương', 'Bình Phước',
    'Bình Thuận', 'Cà Mau', 'Cần Thơ', 'Cao Bằng', 'Đà Nẵng',
    'Đắk Lắk', 'Đắk Nông', 'Điện Biên', 'Đồng Nai', 'Đồng Tháp',
    'Gia Lai', 'Hà Giang', 'Hà Nam', 'Hà Nội', 'Hà Tĩnh',
    'Hải Dương', 'Hải Phòng', 'Hậu Giang', 'Hòa Bình', 'Hưng Yên',
    'Khánh Hòa', 'Kiên Giang', 'Kon Tum', 'Lai Châu', 'Lâm Đồng',
    'Lạng Sơn', 'Lào Cai', 'Long An', 'Nam Định', 'Nghệ An',
    'Ninh Bình', 'Ninh Thuận', 'Phú Thọ', 'Phú Yên', 'Quảng Bình',
    'Quảng Nam', 'Quảng Ngãi', 'Quảng Ninh', 'Quảng Trị', 'Sóc Trăng',
    'Sơn La', 'Tây Ninh', 'Thái Bình', 'Thái Nguyên', 'Thanh Hóa',
    'Thừa Thiên Huế', 'Tiền Giang', 'Thành phố Hồ Chí Minh', 'Trà Vinh',
    'Tuyên Quang', 'Vĩnh Long', 'Vĩnh Phúc', 'Yên Bái',
];
sort($vn_provinces);

$avatar_letter = mb_strtoupper(mb_substr($user['full_name'], 0, 1, 'UTF-8'), 'UTF-8');
$joined_date   = date('d/m/Y', strtotime($user['created_at']));

// ── View ─────────────────────────────────────────────────────────────────────
$page_title  = 'Tài khoản của tôi';
$page_css    = '../assets/css/profile.css';
$base_path   = '../';
$page_scripts = ['../assets/js/profile.js'];

ob_start();
?>

<div class="profile-wrapper">

    <!-- ── Sidebar ─────────────────────────────────── -->
    <aside class="profile-sidebar">

        <div class="profile-avatar-block">
            <div class="profile-avatar-circle"><?= $avatar_letter ?></div>
            <p class="profile-avatar-name"><?= htmlspecialchars($user['full_name']) ?></p>
            <p class="profile-avatar-email"><?= htmlspecialchars($user['email']) ?></p>
            <p class="profile-joined">Thành viên từ <?= $joined_date ?></p>
        </div>

        <nav class="profile-nav">
            <?php if (!$is_admin): ?>
            <a href="profile.php?tab=info"
               class="profile-nav-item <?= $tab === 'info' ? 'active' : '' ?>">
                <i class="fa-regular fa-user"></i>
                Thông tin cá nhân
            </a>
            <?php endif; ?>
            <a href="profile.php?tab=password"
               class="profile-nav-item <?= $tab === 'password' ? 'active' : '' ?>">
                <i class="fa-solid fa-lock"></i>
                Đổi mật khẩu
            </a>
            <?php if (!$is_admin): ?>
            <a href="profile.php?tab=addresses"
               class="profile-nav-item <?= $tab === 'addresses' ? 'active' : '' ?>">
                <i class="fa-solid fa-location-dot"></i>
                Địa chỉ của tôi
                <?php if (count($addresses) > 0): ?>
                    <span class="addr-count"><?= count($addresses) ?></span>
                <?php endif; ?>
            </a>
            <?php endif; ?>
        </nav>

    </aside>

    <!-- ── Main content ────────────────────────────── -->
    <div class="profile-content">

        <!-- Flash messages -->
        <?php if ($flash_success): ?>
            <div class="profile-alert profile-alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <?= htmlspecialchars($flash_success) ?>
            </div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="profile-alert profile-alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= htmlspecialchars($flash_error) ?>
            </div>
        <?php endif; ?>

        <!-- ══ TAB: Thông tin cá nhân ══════════════════════════════════ -->
        <?php if ($tab === 'info' && !$is_admin): ?>
        <div class="profile-card">
            <div class="profile-card-header">
                <h2><i class="fa-regular fa-user"></i> Thông tin cá nhân</h2>
                <p>Quản lý thông tin hồ sơ để bảo mật tài khoản</p>
            </div>

            <form action="profile.php" method="POST" class="profile-form">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action" value="update_profile">
                <input type="hidden" name="tab" value="info">

                <div class="form-row">
                    <label class="form-label" for="full_name">
                        Họ và tên <span class="required">*</span>
                    </label>
                    <div class="input-group">
                        <i class="fa-solid fa-signature"></i>
                        <input type="text" id="full_name" name="full_name"
                               value="<?= htmlspecialchars($user['full_name']) ?>"
                               placeholder="Nguyễn Văn A"
                               maxlength="100" required>
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label" for="email">
                        Email
                    </label>
                    <div class="input-group input-group-disabled">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>"
                               disabled title="Email không thể thay đổi">
                    </div>
                    <span class="form-hint">Email không thể thay đổi.</span>
                </div>

                <div class="form-row">
                    <label class="form-label" for="phone">
                        Số điện thoại
                    </label>
                    <div class="input-group">
                        <i class="fa-solid fa-phone"></i>
                        <input type="tel" id="phone" name="phone"
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                               placeholder="0901234567"
                               pattern="[0-9]{10,12}" maxlength="12">
                    </div>
                    <span class="form-hint">Số điện thoại 10–12 chữ số.</span>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-profile-primary">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>

        <!-- ══ TAB: Đổi mật khẩu ═══════════════════════════════════════ -->
        <?php elseif ($tab === 'password'): ?>
        <div class="profile-card">
            <div class="profile-card-header">
                <h2><i class="fa-solid fa-lock"></i> Đổi mật khẩu</h2>
                <p>Để bảo mật tài khoản, vui lòng không chia sẻ mật khẩu cho người khác</p>
            </div>

            <form action="profile.php" method="POST" class="profile-form" id="passwordForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action" value="change_password">
                <input type="hidden" name="tab" value="password">

                <div class="form-row">
                    <label class="form-label" for="old_password">
                        Mật khẩu hiện tại <span class="required">*</span>
                    </label>
                    <div class="input-group input-group-password">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="old_password" name="old_password"
                               placeholder="Nhập mật khẩu hiện tại"
                               minlength="6" maxlength="255" required>
                        <button type="button" class="toggle-pwd" aria-label="Hiện/ẩn mật khẩu">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label" for="new_password">
                        Mật khẩu mới <span class="required">*</span>
                    </label>
                    <div class="input-group input-group-password">
                        <i class="fa-solid fa-key"></i>
                        <input type="password" id="new_password" name="new_password"
                               placeholder="Ít nhất 6 ký tự"
                               minlength="6" maxlength="255" required>
                        <button type="button" class="toggle-pwd" aria-label="Hiện/ẩn mật khẩu">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label" for="confirm_password">
                        Xác nhận mật khẩu mới <span class="required">*</span>
                    </label>
                    <div class="input-group input-group-password">
                        <i class="fa-solid fa-key"></i>
                        <input type="password" id="confirm_password" name="confirm_password"
                               placeholder="Nhập lại mật khẩu mới"
                               minlength="6" maxlength="255" required>
                        <button type="button" class="toggle-pwd" aria-label="Hiện/ẩn mật khẩu">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <span class="form-hint" id="pwdMatchHint"></span>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-profile-primary">
                        <i class="fa-solid fa-shield-halved"></i>
                        Đổi mật khẩu
                    </button>
                </div>
            </form>
        </div>

        <!-- ══ TAB: Địa chỉ ════════════════════════════════════════════ -->
        <?php elseif ($tab === 'addresses' && !$is_admin): ?>
        <div class="profile-card">
            <div class="profile-card-header profile-card-header-row">
                <div>
                    <h2><i class="fa-solid fa-location-dot"></i> Địa chỉ của tôi</h2>
                    <p>Quản lý danh sách địa chỉ nhận hàng</p>
                </div>
                <?php if (!$show_addr_form): ?>
                <a href="profile.php?tab=addresses&add_address=1" class="btn-add-address">
                    <i class="fa-solid fa-plus"></i> Thêm địa chỉ
                </a>
                <?php endif; ?>
            </div>

            <!-- Address list -->
            <?php if (count($addresses) === 0 && !$show_addr_form): ?>
                <div class="addr-empty">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <p>Bạn chưa có địa chỉ nào. Hãy thêm địa chỉ đầu tiên!</p>
                    <a href="profile.php?tab=addresses&add_address=1" class="btn-profile-primary">
                        <i class="fa-solid fa-plus"></i> Thêm địa chỉ
                    </a>
                </div>
            <?php else: ?>
                <div class="addr-list">
                    <?php foreach ($addresses as $addr): ?>
                    <div class="addr-card <?= $addr['is_default'] ? 'addr-card-default' : '' ?>">
                        <div class="addr-card-body">
                            <div class="addr-name-row">
                                <span class="addr-recipient"><?= htmlspecialchars($addr['recipient_name']) ?></span>
                                <span class="addr-sep">|</span>
                                <span class="addr-phone"><?= htmlspecialchars($addr['phone']) ?></span>
                                <?php if ($addr['is_default']): ?>
                                    <span class="addr-default-badge">Mặc định</span>
                                <?php endif; ?>
                            </div>
                            <p class="addr-street"><?= htmlspecialchars($addr['street']) ?></p>
                            <p class="addr-region">
                                <?php
                                $parts = array_filter([$addr['district'], $addr['city']]);
                                echo htmlspecialchars(implode(', ', $parts));
                                ?>
                            </p>
                        </div>
                        <div class="addr-card-actions">
                            <a href="profile.php?tab=addresses&edit_id=<?= $addr['id'] ?>"
                               class="btn-addr-action btn-addr-edit">
                                <i class="fa-regular fa-pen-to-square"></i> Sửa
                            </a>
                            <?php if (!$addr['is_default']): ?>
                                <form action="profile.php" method="POST" class="inline-form">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="set_default_address">
                                    <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                                    <button type="submit" class="btn-addr-action btn-addr-default">
                                        <i class="fa-regular fa-star"></i> Mặc định
                                    </button>
                                </form>
                                <form action="profile.php" method="POST" class="inline-form"
                                      onsubmit="return confirm('Bạn có chắc muốn xóa địa chỉ này?')">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="delete_address">
                                    <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                                    <button type="submit" class="btn-addr-action btn-addr-delete">
                                        <i class="fa-regular fa-trash-can"></i> Xóa
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Add / Edit address form -->
            <?php if ($show_addr_form): ?>
            <div class="addr-form-section">
                <h3 class="addr-form-title">
                    <?= $edit_address ? 'Cập nhật địa chỉ' : 'Thêm địa chỉ mới' ?>
                </h3>

                <form action="profile.php" method="POST" class="profile-form addr-form">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="save_address">
                    <input type="hidden" name="tab" value="addresses">
                    <?php if ($edit_address): ?>
                        <input type="hidden" name="address_id" value="<?= $edit_address['id'] ?>">
                    <?php endif; ?>

                    <!-- Two-column grid -->
                    <div class="addr-form-grid">

                        <div class="form-row">
                            <label class="form-label" for="recipient_name">
                                Họ tên người nhận <span class="required">*</span>
                            </label>
                            <div class="input-group">
                                <i class="fa-regular fa-user"></i>
                                <input type="text" id="recipient_name" name="recipient_name"
                                       value="<?= htmlspecialchars($edit_address['recipient_name'] ?? $user['full_name']) ?>"
                                       placeholder="Nguyễn Văn A"
                                       maxlength="100" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <label class="form-label" for="addr_phone">
                                Số điện thoại <span class="required">*</span>
                            </label>
                            <div class="input-group">
                                <i class="fa-solid fa-phone"></i>
                                <input type="tel" id="addr_phone" name="addr_phone"
                                       value="<?= htmlspecialchars($edit_address['phone'] ?? $user['phone'] ?? '') ?>"
                                       placeholder="0901234567"
                                       pattern="[0-9]{10,12}" maxlength="12" required>
                            </div>
                        </div>

                        <div class="form-row form-row-full">
                            <label class="form-label" for="street">
                                Địa chỉ cụ thể <span class="required">*</span>
                            </label>
                            <div class="input-group">
                                <i class="fa-solid fa-house"></i>
                                <input type="text" id="street" name="street"
                                       value="<?= htmlspecialchars($edit_address['street'] ?? '') ?>"
                                       placeholder="Số nhà, tên đường, phường/xã"
                                       maxlength="255" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <label class="form-label" for="district">
                                Quận / Huyện
                            </label>
                            <div class="input-group">
                                <i class="fa-solid fa-map-pin"></i>
                                <input type="text" id="district" name="district"
                                       value="<?= htmlspecialchars($edit_address['district'] ?? '') ?>"
                                       placeholder="VD: Quận 1, Huyện Bình Chánh"
                                       maxlength="100">
                            </div>
                        </div>

                        <div class="form-row">
                            <label class="form-label" for="city">
                                Tỉnh / Thành phố <span class="required">*</span>
                            </label>
                            <div class="input-group input-group-select">
                                <i class="fa-solid fa-map"></i>
                                <select id="city" name="city" required>
                                    <option value="" disabled <?= empty($edit_address['city']) ? 'selected' : '' ?>>
                                        -- Chọn tỉnh/thành phố --
                                    </option>
                                    <?php foreach ($vn_provinces as $province): ?>
                                        <option value="<?= htmlspecialchars($province) ?>"
                                            <?= ($edit_address['city'] ?? '') === $province ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($province) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                    </div><!-- /addr-form-grid -->

                    <div class="form-row">
                        <label class="checkbox-group">
                            <input type="checkbox" name="is_default" value="1"
                                   <?= ($edit_address['is_default'] ?? 0) ? 'checked' : '' ?>
                                   <?= (count($addresses) === 0) ? 'checked disabled' : '' ?>>
                            <span>Đặt làm địa chỉ mặc định</span>
                        </label>
                    </div>

                    <div class="form-actions addr-form-actions">
                        <a href="profile.php?tab=addresses" class="btn-profile-secondary">
                            Hủy
                        </a>
                        <button type="submit" class="btn-profile-primary">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <?= $edit_address ? 'Lưu thay đổi' : 'Thêm địa chỉ' ?>
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

        </div>
        <?php endif; ?>

    </div><!-- /profile-content -->

</div><!-- /profile-wrapper -->

<?php
$page_content = ob_get_clean();
include '../includes/layout.php';
?>
