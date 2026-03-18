<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/orderInformation.css">
    <title>Document</title>
</head>
<body class="page">
    <?php include_once('../includes/header.php')?>
    <?php include_once('../includes/navbar.php')?>

    <main class="content">

        <div class="orderInformation-container">

            <div class="title-signIn">
                <h2 class="title">THÔNG TIN GIAO HÀNG</h2>
                <p>Bạn đã có tài khoản rồi? <a href="">Đăng nhập</a></p>
            </div>

            <form method="post" action="orderInformation.php" class="orderInformation-form">
                <div class="row">
                    <input type="text" id="fullName" name="fullName" placeholder="Họ và tên" required>
                </div>

                <div class="row">
                    <input type="email" id="email" name="email" placeholder="Email" required>
                    <input type="tel" id="phoneNumber" name="phoneNumber"  placeholder="Số điện thoại" required>
                </div>

                <div class="row">
                    <input type="text" id="address" name="address" placeholder="Địa chỉ"required>
                </div>

                <div class="row">
                    <select id="city" name="city" required>
                        <option value="">Chọn tỉnh/thành phố</option>
                        <option value="An Giang">An Giang</option>
                        <option value="Ấn Độ">Ấn Độ</option>
                        <option value="Bà Rịa - Vũng Tàu">Bà Rịa - Vũng Tàu</option>
                        <option value="Bắc Giang">Bắc Giang</option>
                        <option value="Bắc Kạn">Bắc Kạn</option>
                        <option value="Bạc Liêu">Bạc Liêu</option>
                        <option value="Bắc Ninh">Bắc Ninh</option>
                        <option value="Bến Tre">Bến Tre</option>
                        <option value="Bình Dương">Bình Dương</option>
                        <option value="Bình Phước">Bình Phước</option>
                        <option value="Bình Thuận">Bình Thuận</option>
                        <option value="Cà Mau">Cà Mau</option>
                        <option value="Cần Thơ">Cần Thơ</option>
                        <option value="Cao Bằng">Cao Bằng</option>
                        <option value="Căn Cứ">Căn Cứ</option>
                        <option value="Đà Nẵng">Đà Nẵng</option>
                        <option value="Đắk Lắk">Đắk Lắk</option>
                        <option value="Đắk Nông">Đắk Nông</option>
                        <option value="Điện Biên">Điện Biên</option>
                        <option value="Đồng Nai">Đồng Nai</option>
                        <option value="Đồng Tháp">Đồng Tháp</option>
                        <option value="Gia Lai">Gia Lai</option>
                        <option value="Hà Giang">Hà Giang</option>
                        <option value="Hà Nam">Hà Nam</option>
                        <option value="Hà Nội">Hà Nội</option>
                        <option value="Hà Tây">Hà Tây</option>
                        <option value="Hải Dương">Hải Dương</option>
                        <option value="Hải Phòng">Hải Phòng</option>
                        <option value="Hậu Giang">Hậu Giang</option>
                        <option value="Hòa Bình">Hòa Bình</option>
                        <option value="Hồ Chí Minh">Hồ Chí Minh</option>
                        <option value="Huế">Huế</option>
                        <option value="Khánh Hòa">Khánh Hòa</option>
                        <option value="Kiên Giang">Kiên Giang</option>
                        <option value="Kon Tum">Kon Tum</option>
                        <option value="Lâm Đồng">Lâm Đồng</option>
                        <option value="Lạng Sơn">Lạng Sơn</option>
                        <option value="Lào Cai">Lào Cai</option>
                        <option value="Long An">Long An</option>
                        <option value="Nam Định">Nam Định</option>
                        <option value="Nghệ An">Nghệ An</option>
                        <option value="Ninh Bình">Ninh Bình</option>
                        <option value="Ninh Thuận">Ninh Thuận</option>
                        <option value="Phú Thọ">Phú Thọ</option>
                        <option value="Phú Yên">Phú Yên</option>
                        <option value="Quảng Bình">Quảng Bình</option>
                        <option value="Quảng Nam">Quảng Nam</option>
                        <option value="Quảng Ngãi">Quảng Ngãi</option>
                        <option value="Quảng Ninh">Quảng Ninh</option>
                        <option value="Quảng Trị">Quảng Trị</option>
                        <option value="Sóc Trăng">Sóc Trăng</option>
                        <option value="Song Be">Song Be</option>
                        <option value="Tây Ninh">Tây Ninh</option>
                        <option value="Thái Bình">Thái Bình</option>
                        <option value="Thái Nguyên">Thái Nguyên</option>
                        <option value="Thanh Hóa">Thanh Hóa</option>
                        <option value="Thừa Thiên - Huế">Thừa Thiên - Huế</option>
                        <option value="Tiền Giang">Tiền Giang</option>
                        <option value="Trà Vinh">Trà Vinh</option>
                        <option value="Tuyên Quang">Tuyên Quang</option>
                        <option value="Vĩnh Long">Vĩnh Long</option>
                        <option value="Vĩnh Phúc">Vĩnh Phúc</option>
                        <option value="Yên Bái">Yên Bái</option>
                    </select>
                    <select id="district" name="district" required>
                        <option value="Quận 7">Quận 7</option>
                    </select>
                </div>

                <div class="button-group">
                    <a href="cart.php" class="continue-shopping">
                        <i class="fa-solid fa-arrow-left"></i>Quay về giỏ hàng
                    </a>

                    <button type="submit" class="btn-submit">Tiếp tục đến trang thanh toán</button>
                </div>
            </form>
        </div>
    </main>

    <?php include_once('../includes/footer.php')?>
</body>
</html>