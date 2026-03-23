<?php
// pages/signup.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Already logged in → go home
if (isset($_SESSION['user_id'])) {
    header("Location: ../home.php");
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../includes/dbconnect.php';

    $full_name        = trim($_POST['full_name']        ?? '');
    $email            = trim($_POST['email']            ?? '');
    $phone            = trim($_POST['phone']            ?? '');
    $password         =      $_POST['password']         ?? '';
    $password_confirm =      $_POST['password_confirm'] ?? '';

    // Validate
    if (mb_strlen($full_name) < 3) {
        $error = 'Họ và tên phải có ít nhất 3 ký tự.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } elseif ($phone !== '' && !preg_match('/^[0-9]{10,20}$/', $phone)) {
        $error = 'Số điện thoại phải từ 10–20 chữ số.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } elseif ($password !== $password_confirm) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } else {
        // Check duplicate email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Email này đã được đăng ký.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $pdo->prepare(
                "INSERT INTO users (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)"
            );
            $ins->execute([$full_name, $email, $phone ?: null, $hash]);

            $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay.';
        }
    }
}

// ── View ──────────────────────────────────────────────────────
$page_title   = "Đăng ký";
$page_css     = "../assets/css/signup.css";
$base_path    = "../";
$page_scripts = ["../assets/js/signup.js"];

ob_start();
?>

<style>
.alert{display:flex;align-items:flex-start;gap:10px;padding:14px 16px;border-radius:8px;font-size:14px;line-height:1.5;margin-bottom:18px;}
.alert i{font-size:16px;flex-shrink:0;margin-top:2px;}
.alert-error{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;}
.alert-error i{color:#dc2626;}
.alert-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;}
.alert-success i{color:#16a34a;}
.alert-success a{color:#15803d;font-weight:600;text-decoration:underline;}
</style>

<div class="signup-container">
    <h2>ĐĂNG KÝ TÀI KHOẢN</h2>
    <p class="subtitle">Tạo tài khoản để mua sắm tại HKT Shop</p>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <?php echo htmlspecialchars($success); ?>
            <a href="signin.php">Đăng nhập ngay →</a>
        </div>
    <?php endif; ?>

    <form action="" method="POST" class="form-signup" id="signupForm">
        <div class="input-group">
            <i class="fa fa-user"></i>
            <input type="text" name="full_name" placeholder="Họ và tên"
                value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                required minlength="3" maxlength="100">
        </div>

        <div class="input-group">
            <i class="fa fa-envelope"></i>
            <input type="email" name="email" placeholder="Email"
                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                required maxlength="255">
        </div>

        <div class="input-group">
            <i class="fa-solid fa-phone"></i>
            <input type="tel" name="phone" placeholder="Số điện thoại (tùy chọn)"
                value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                pattern="[0-9]{10,20}" maxlength="20">
        </div>

        <div class="input-group">
            <i class="fa fa-lock"></i>
            <input type="password" name="password" id="password"
                placeholder="Mật khẩu (tối thiểu 6 ký tự)"
                required minlength="6" maxlength="255">
        </div>

        <div class="input-group">
            <i class="fa fa-lock"></i>
            <input type="password" name="password_confirm" id="password_confirm"
                placeholder="Xác nhận mật khẩu"
                required minlength="6" maxlength="255">
        </div>

        <div class="checkbox-group">
            <input type="checkbox" name="terms" id="terms" required>
            <label for="terms">
                Tôi đồng ý với <a href="#">Điều khoản dịch vụ</a>
                và <a href="#">Chính sách bảo mật</a>
            </label>
        </div>

        <button type="submit" class="btn-signup">ĐĂNG KÝ</button>
    </form>

    <div class="signin-link">
        Đã có tài khoản? <a href="signin.php">Đăng nhập tại đây</a>
    </div>
</div>

<?php
$page_content = ob_get_clean();
include '../includes/layout.php';
?>