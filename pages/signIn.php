<?php
// pages/signin.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Already logged in → go home
if (isset($_SESSION['user_id'])) {
    header("Location: ../home.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../includes/dbconnect.php';

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } else {
        $stmt = $pdo->prepare(
            "SELECT id, full_name, email, password_hash FROM users WHERE email = ? AND is_active = 1 LIMIT 1"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];

            // Redirect back to wherever the user came from, or home
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '../home.php';
            header("Location: " . $redirect);
            exit;
        } else {
            $error = 'Email hoặc mật khẩu không đúng.';
        }
    }
}

// ── View ──────────────────────────────────────────────────────
$page_title = "Đăng nhập";
$page_css = "../assets/css/signin.css";
$base_path = "../";
$page_scripts = ["../assets/js/signin.js"];

ob_start();
?>

<div class="signin-container">
    <h2>ĐĂNG NHẬP</h2>
    <p class="subtitle">Đăng nhập vào tài khoản HKT Shop của bạn</p>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" class="form-signin" id="signinForm">
        <div class="input-group">
            <i class="fa fa-envelope"></i>
            <input type="text" name="email" id="emailInput" placeholder="Email"
                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required maxlength="255">
        </div>

        <div class="input-group">
            <i class="fa fa-lock"></i>
            <input type="password" name="password" id="passwordInput" placeholder="Mật khẩu" required minlength="6"
                maxlength="255">
        </div>

        <div class="checkbox-group">
            <input type="checkbox" name="remember_me" id="rememberMe">
            <label for="rememberMe">Ghi nhớ tôi</label>
        </div>

        <button type="submit" class="btn-signin">ĐĂNG NHẬP</button>
    </form>

    <div class="form-links">
        <a href="#" class="forgot-password">Quên mật khẩu?</a>
        <span class="divider">|</span>
        <a href="signup.php">Đăng ký tài khoản</a>
    </div>
</div>

<?php
$page_content = ob_get_clean();
include '../includes/layout.php';
?>