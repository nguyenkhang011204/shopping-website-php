<?php
$page_title = "Đăng ký";
$page_css = "../assets/css/signUp.css";
$base_path = "../";
$page_scripts = ["../assets/js/signUp.js"];

// Start output buffering to capture page content
ob_start();
?>

<div class="signup-container">
    <h2>ĐĂNG KÝ TÀI KHOẢN</h2>
    <p class="subtitle">Tạo tài khoản để mua sắm tại HKT Shop</p>

    <form action="#" method="POST" class="form-signUp" id="signupForm">
        <!-- Full Name -->
        <div class="input-group">
            <i class="fa fa-user"></i>
            <input 
                type="text" 
                name="full_name" 
                placeholder="Họ và tên" 
                required
                minlength="3"
                maxlength="100"
            >
        </div>

        <!-- Email -->
        <div class="input-group">
            <i class="fa fa-envelope"></i>
            <input 
                type="email" 
                name="email" 
                placeholder="Email" 
                required
                maxlength="255"
            >
        </div>

        <!-- Phone -->
        <div class="input-group">
            <i class="fa-solid fa-phone"></i>
            <input 
                type="tel" 
                name="phone" 
                placeholder="Số điện thoại" 
                pattern="[0-9]{10,20}"
                maxlength="20"
            >
        </div>

        <!-- Password -->
        <div class="input-group">
            <i class="fa fa-lock"></i>
            <input 
                type="password" 
                name="password" 
                placeholder="Mật khẩu (tối thiểu 6 ký tự)" 
                required
                minlength="6"
                maxlength="255"
                id="password"
            >
        </div>

        <!-- Confirm Password -->
        <div class="input-group">
            <i class="fa fa-lock"></i>
            <input 
                type="password" 
                name="password_confirm" 
                placeholder="Xác nhận mật khẩu" 
                required
                minlength="6"
                maxlength="255"
                id="password_confirm"
            >
        </div>

        <!-- Terms & Conditions -->
        <div class="checkbox-group">
            <input 
                type="checkbox" 
                name="terms" 
                id="terms" 
                required
            >
            <label for="terms">
                Tôi đồng ý với <a href="#">Điều khoản dịch vụ</a> và <a href="#">Chính sách bảo mật</a>
            </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-signup">ĐĂNG KÝ</button>
    </form>

    <!-- Sign In Link -->
    <div class="signin-link">
        Đã có tài khoản? <a href="signIn.php">Đăng nhập tại đây</a>
    </div>
</div>

<?php
// Capture output and include layout
$page_content = ob_get_clean();
include('../includes/layout.php');
?>