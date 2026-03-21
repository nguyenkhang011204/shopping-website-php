<?php
$page_title = "Đăng ký";
$page_css = "../assets/css/signUp.css";
$base_path = "../";

// Start output buffering to capture page content
ob_start();
?>
<h2>ĐĂNG KÝ</h2>
<form action="" class="form-signUp">
    <div class="input-group">
        <i class="fa fa-user"></i>
        <input type="text" placeholder="Họ">
    </div>

    <div class="input-group">
        <i class="fa fa-user"></i>
        <input type="text" placeholder="Tên">
    </div>

    <div class="input-group">
        <i class="fa-solid fa-phone"></i>
        <input type="number" placeholder="Số điện thoại">
    </div>

    <div class="input-group">
        <i class="fa fa-lock"></i>
        <input type="password" placeholder="Mật khẩu">
    </div>

    <div class="input-group">
        <i class="fa fa-lock"></i>
        <input type="password" placeholder="Xác nhận mật khẩu">
    </div>

    <div class="have-user">
        <a href="#">Đã có tài khoản?</a>
    </div>

    <div class="btn-SignUp" type="submit">ĐĂNG KÝ</div>

</form>

<?php
// Capture output and include layout
$page_content = ob_get_clean();
include('../includes/layout.php');
?>