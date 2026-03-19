<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/signUp.css">
    <title>Document</title>
</head>
<body>
    <?php include_once('../includes/header.php')?>
    <?php include_once('../includes/navbar.php')?>
    
    <main class="content">
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
                <i class="fa-solid fa-envelope"></i>
                <input type="password" placeholder="Email">
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
                <a href="#" style="text-decoration: none; font-weight: bold">Đăng ký</a>
            </div>

            <div class="btn-SignUp" type="submit">ĐĂNG KÝ</div>

        </form>
    </main>

    <?php include_once('../includes/footer.php')?>
</body>
</html>