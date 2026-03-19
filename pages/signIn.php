<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/signIn.css">
    <title>Document</title>
</head>
<body>
    <?php include_once('../includes/header.php')?>
    <?php include_once('../includes/navbar.php')?>
    
    <main class="content">
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
    </main>

    <?php include_once('../includes/footer.php')?>
</body>
</html>