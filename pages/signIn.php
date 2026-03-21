<?php
$page_title = "Đăng nhập";
$page_css = "../assets/css/signIn.css";
$base_path = "../";

// Start output buffering to capture page content
ob_start();
?>

<h2>ĐĂNG NHẬP</h2>
<form action="" class="form-signIn">
    <div class="input-group">
        <i class="fa fa-user"></i>
        <input type="text" placeholder="Tên đăng nhập">
    </div>

    <div class="input-group">
        <i class="fa fa-lock"></i>
        <input type="password" placeholder="Mật khẩu">
    </div>

    <div class="option">
        <a href="#">Quên mật khẩu</a>
        <a href="#">Đăng ký</a>
    </div>

    <div class="btn-SignIn" type="submit">ĐĂNG NHẬP</div>

</form>

<?php
// Capture output and include layout
$page_content = ob_get_clean();
include('../includes/layout.php');
?>