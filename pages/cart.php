<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/cart.css">
    <title>Document</title>
</head>
<body class="page">
    <?php include_once('../includes/header.php')?>
    <?php include_once('../includes/navbar.php')?>
    
    <main class="content">

        <div class="cart-container">

            <h2 class="cart-title">GIỎ HÀNG</h2>
            
                <!--Empty Cart -->
            <div class="cart-empty">
                <p>Chưa có sản phẩm được thêm vào giỏ hàng</p>

                <a href="" class="continue-shopping">
                    <i class="fa-solid fa-arrow-left"></i>Tiếp tục mua hàng
                </a>
            </div>


                 <!--Shopping Cart -->
            <div class="cart-shopping">
                <table class="cart-table">

                    <thead>
                        <tr>
                            
                            <th>Tên sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Giá tiền</th>
                            <th></th>
                        </tr>
                    </thead>
    
                    <tbody>
                        <tr class="cart-item">
                            <td class="product-info">
                                <img src="../upload/Ao321.png" alt="">
                                <span>Áo thể thao HKT - Sport T-Shirt</span>
                            </td>
    
                            <td>
                                <input type="number" value="1" class="quantity">
                            </td>
    
                            <td class="price">379,000đ</td>
                            <td class="remove">Xoá</td>
                        </tr>
    
                        <tr class="cart-item">
                            <td class="product-info">
                                <img src="../upload/Ao321.png" alt="">
                                <span>Áo thể thao HKT - Sport T-Shirt</span>
                            </td>
    
                            <td>
                                <input type="number" value="1" class="quantity">
                            </td>
    
                            <td class="price">379,000đ</td>
                            <td class="remove">Xoá</td>
                        </tr>
                    </tbody>

                </table>

                <div class="cart-total">
                    <span>Tổng cộng:</span>
                    <span class="total-price">758,000đ</span>
                </div>

                <div class="cart-actions">
                    <a href="#" class="continue-shopping">
                        <i class="fa-solid fa-arrow-left"></i>Tiếp tục mua hàng
                    </a>

                    <div class="button-group">
                        <button class="update-btn">CẬP NHẬT</button>
                        <button class="checkout-btn">ĐẶT HÀNG</button>
                    </div>
                </div>
            </div>


        </div>

        
    </main>
    
    <?php include_once('../includes/footer.php')?>
</body>
</html>