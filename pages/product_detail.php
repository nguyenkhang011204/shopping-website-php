<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/product_detail.css">
    <title>Document</title>
</head>
<body>
    <?php include_once('../includes/header.php')?>
    <?php include_once('../includes/navbar.php')?>
    
    <main class="content">
        
        <div class="product-container">
            
            <div class="product-detail">

                <div class="product-gallery">
                    <img src="../upload/Ao321.png" alt="" id="main-image">
                    <div class="thumb-list">
                        <img src="../upload/Ao321.png" alt="">
                        <img src="../upload/Ao321.png" alt="">
                        <img src="../upload/Ao321.png" alt="">
                    </div>
                </div>

                <div class="product-info">
                    <h1>Áo thể thao HKT - Sport T-Shirt</h1>
                    <p>SKU: ATHLETICS-S</p>
                    <h2 class="price">379.000đ</h2>
                    <div class="line"></div>
                    <label class="title-size" >Kích thước</label>
                    <div>
                        <select class="size">
                            <option>S</option>
                            <option>M</option>
                            <option>L</option>
                            <option>XL</option>
                        </select>
                    </div>
                    
                    <label class="title-count">Số lượng</label>
                    <div>
                        <input class="count" type="number" value="1" min="1">
                    </div>
                   

                    <div class="btn-group">
                        <button class="add-cart"><i class="fa-solid fa-cart-arrow-down"></i></button>
                        <button class="buy">Đặt hàng</button>
                    </div>
                </div>
            </div>

            <div>


            </div>
        </div>

    </main>


    <?php include_once('../includes/footer.php')?>
</body>
<script href="../assets/js/product_detail.js"></script>
</html>