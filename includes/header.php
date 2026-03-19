<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <link rel="stylesheet" href="/shopping-website-php/assets/css/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <header class="header">
        <div class="header-container">

            <!-- Logo -->
            <div class="logo">
                <img src="/shopping-website-php/assets/images/logo.png" alt="logo">
            </div>

            <!-- Search -->
            <div class="search-box">
                <input type="text" placeholder="Tìm kiếm ...">
                <button>
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>

            <!-- Icons -->
            <div class="header-icons">
                <div class="user-dropdown">
                <a href="#" class="user"><i class="fa-solid fa-user"></i></a>
                <div class="user-dropdown-menu">
                    <a href="/pages/signIn.php" class="user-dropdown-item">Đăng nhập</a>
                    <a href="/pages/signUp.php" class="user-dropdown-item">Đăng ký</a>
                </div>
            </div>

                <a href="#" class="cart">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="cart-count">0</span>
                </a>
            </div>

        </div>
    </header>
</body> 
</html>