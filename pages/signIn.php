<?php
$page_title = "Đăng nhập";
$page_css = "../assets/css/signIn.css";
$base_path = "../";
$page_scripts = ["../assets/js/signIn.js"];

// Start output buffering to capture page content
ob_start();
?>

<div class="signin-container">
    <h2>ĐĂNG NHẬP</h2>
    <p class="subtitle">Đăng nhập vào tài khoản HKT Shop của bạn</p>

    <form action="#" method="POST" class="form-signIn" id="signinForm">
        <!-- Email/Username -->
        <div class="input-group">
            <i class="fa fa-envelope"></i>
            <input 
                type="email" 
                name="email" 
                placeholder="Email" 
                required
                maxlength="255"
                id="emailInput"
            >
        </div>

        <!-- Password -->
        <div class="input-group">
            <i class="fa fa-lock"></i>
            <input 
                type="password" 
                name="password" 
                placeholder="Mật khẩu" 
                required
                minlength="6"
                maxlength="255"
                id="passwordInput"
            >
        </div>

        <!-- Remember Me -->
        <div class="checkbox-group">
            <input 
                type="checkbox" 
                name="remember_me" 
                id="rememberMe"
            >
            <label for="rememberMe">Ghi nhớ tôi</label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-signin">ĐĂNG NHẬP</button>
    </form>

    <!-- Additional Links -->
    <div class="form-links">
        <a href="#" class="forgot-password">Quên mật khẩu?</a>
        <span class="divider">|</span>
        <a href="signUp.php" class="signup-link">Đăng ký tài khoản</a>
    </div>
</div>

<?php
// Capture output and include layout
$page_content = ob_get_clean();
include('../includes/layout.php');
?>