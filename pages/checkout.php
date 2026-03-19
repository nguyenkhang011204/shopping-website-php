<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/checkout.css">
    <title>Document</title>
</head>
<body class="page">
    <?php include_once('../includes/header.php')?>
    <?php include_once('../includes/navbar.php')?>
    
    <main class="content">
        <div class="checkout-container">
            <div class="checkout__header">
                <h2>THANH TOÁN</h2>
            </div>

            <div class="checkout__cart">
                <table class="checkout__table">
                    <thead>
                        <tr>
                            <th>Tên sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Giá tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="product">
                                <img src="../upload/Ao321.png" alt="Áo thể thao HKT">
                                <span>Áo thể thao HKT - Sport T-Shirt</span>
                            </td>
                            <td>
                                <div class="quantity">1</div>
                            </td>
                            <td class="price">379,000đ</td>
                        </tr>
                        <tr>
                            <td class="product">
                                <img src="../upload/Ao321.png" alt="Áo thể thao HKT">
                                <span>Áo thể thao HKT - Sport T-Shirt</span>
                            </td>
                            <td>
                                <div class="quantity">1</div>
                            </td>
                            <td class="price">379,000đ</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="checkout__summary">
                <div class="summary-row">
                    <span>Tạm tính:</span>
                    <span class="amount">758,000đ</span>
                </div>
                <div class="summary-row">
                    <span>Phí vận chuyển:</span>
                    <span class="amount">30,000đ</span>
                </div>
                <div class="summary-row total">
                    <span>TỔNG THANH TOÁN</span>
                    <span class="amount">788,000đ</span>
                </div>
            </div>

            <div class="checkout__payment">
                <div class="payment-title">Chọn phương thức thanh toán</div>
                <label class="payment-option">
                    <input type="radio" name="payment" value="cod" checked>
                    <span class="radio-custom"></span>
                    Chuyển tiền khi nhận hàng (ship COD)
                </label>
                <label class="payment-option">
                    <input type="radio" name="payment" value="bank">
                    <span class="radio-custom"></span>
                    Chuyển tiền ngân hàng
                </label>
            </div>

            <div class="checkout__actions">
                <button class="btn-confirm hide-on-desktop" type="submit">XÁC NHẬN ĐẶT HÀNG</button>
                <a class="back-link" href="/pages/orderInformation.php">&larr; Thông tin đặt hàng</a>
                <button class="btn-confirm hide-on-mobile" type="submit">XÁC NHẬN ĐẶT HÀNG</button>
            </div>

            <div id="success-message" class="checkout-message">
                <strong><i class="fa-solid fa-circle-check"></i> Đặt hàng thành công!</strong> 
                Cảm ơn bạn đã mua sắm, chúng tôi sẽ liên hệ sớm nhất có thể.
            </div>
        </div>
    </main>

    <script>
    (function() {
        const messageEl = document.getElementById('success-message');
        const confirmButtons = document.querySelectorAll('.btn-confirm');
        if (!messageEl || confirmButtons.length === 0) return;

        confirmButtons.forEach(btn => {
            btn.addEventListener('click', event => {
                event.preventDefault();
                messageEl.style.display = 'block';
                confirmButtons.forEach(b => b.disabled = true);
                messageEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    })();
    </script>

    <?php include_once('../includes/footer.php')?>
</body>
</html>