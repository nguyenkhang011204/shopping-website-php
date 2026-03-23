-- ============================================================
-- HKT SHOP — SEED DATA
-- Images are stored as URLs here; the admin will auto-download
-- them to upload/products/ on first save, or you can run
-- database/download_seed_images.php to pre-download all.
-- ============================================================

USE hkt_shop;

-- ============================================================
-- CATEGORIES
-- ============================================================
INSERT INTO categories (name, slug) VALUES
("Áo Nam",       "ao-nam"),
("Quần Nam",     "quan-nam"),
("Áo Nữ",       "ao-nu"),
("Quần Nữ",     "quan-nu"),
("Áo Khoác",    "ao-khoac"),
("Đầm & Váy",   "dam-vay"),
("Phụ Kiện",    "phu-kien"),
("Giày & Dép",  "giay-dep"),
("Đồ Thể Thao", "do-the-thao"),
("Đồ Mặc Nhà",  "do-mac-nha");

-- ============================================================
-- PRODUCTS  (no image data — run database/download_seed_images.php
--            after this to download images into the database)
-- ============================================================
INSERT INTO products
  (category_id, name, slug, sku, description, price, stock, is_active, is_featured)
VALUES
(1, "Áo Sơ Mi Nam Dài Tay Stretch Cao Cấp",    "ao-so-mi-nam-dai-tay-stretch",      "AM-001", "Áo sơ mi nam dài tay chất liệu stretch co giãn 4 chiều thoải mái, thiết kế cổ bẻ thanh lịch phù hợp đi làm và dạo phố. Chất vải mềm mịn không nhăn, giữ form tốt suốt cả ngày.", 499000, 100, 1, 1),
(1, "Áo Thun Nam In Chữ Best Friend Ever",      "ao-thun-nam-in-chu-best-friend-ever","AM-002", "Áo thun nam in chữ hài hước Best Friend Ever, chất cotton 100% mềm mịn thoáng mát, màu sắc tươi không phai sau nhiều lần giặt. Món quà tặng bạn bè cực ý nghĩa.",        249000, 120, 1, 0),
(1, "Áo Thun Nam Bang On Bro",                  "ao-thun-nam-bang-on-bro",           "AM-003", "Áo thun nam thiết kế Bang On Bro phong cách street cá tính, chất cotton dày 200gsm không bai nhào, form regular thoải mái mặc cả ngày.",                                229000,  90, 1, 1),
(4, "Quần Jeans Ống Rộng Lưng Cao Nữ",         "quan-jeans-ong-rong-lung-cao-nu",   "QF-001", "Quần jeans ống rộng lưng cao phong cách Y2K, chất denim co giãn nhẹ bền màu, form baggy tôn dáng. Phù hợp mặc hàng ngày, đi học hoặc đi chơi cùng bạn bè.",            389000,  80, 1, 1),
(6, "Chân Váy Xòe Dài Phong Cách Ballet",       "chan-vay-xoe-dai-phong-cach-ballet","DV-001", "Chân váy xòe dài full circle kiểu dáng ballet vintage, vải voan mềm bay bổng nhẹ nhàng. Thiết kế lưng thun co giãn vừa mọi vóc dáng.",                               349000,  60, 1, 1),
(5, "Áo Khoác Denim Nữ Vintage Wash",           "ao-khoac-denim-nu-vintage-wash",    "AK-001", "Áo khoác denim nữ phong cách vintage wash nhẹ nhàng, chất liệu cotton denim bền đẹp. Thiết kế oversize thoải mái, phù hợp mix đồ hàng ngày.",                          459000,  75, 1, 0),
(2, "Quần Jogger Nam Thể Thao Cotton",           "quan-jogger-nam-the-thao-cotton",   "QM-001", "Quần jogger nam chất liệu cotton co giãn thoáng mát, thiết kế eo thun dây rút tiện lợi, phù hợp tập gym, chạy bộ và mặc nhà.",                                         299000, 110, 1, 0),
(6, "Đầm Maxi Hoa Nhí Dáng Xòe Nữ",            "dam-maxi-hoa-nhi-dang-xoe-nu",      "DV-002", "Đầm maxi hoa nhí dáng xòe nhẹ nhàng nữ tính, chất liệu voan mềm mại thoáng mát. Thiết kế cổ V tinh tế, phù hợp đi biển, dạo phố hoặc sự kiện ngoài trời.",            420000,  55, 1, 1);

-- ============================================================
-- PRODUCT SIZES
-- ============================================================
INSERT INTO product_sizes (product_id, size, stock) VALUES
-- 1: Áo Sơ Mi Nam
(1,"S",18),(1,"M",28),(1,"L",25),(1,"XL",18),(1,"XXL",11),
-- 2: Áo Thun Best Friend
(2,"S",25),(2,"M",38),(2,"L",32),(2,"XL",25),
-- 3: Áo Thun Bang On Bro
(3,"S",20),(3,"M",30),(3,"L",25),(3,"XL",15),
-- 4: Quần Jeans Nữ
(4,"XS",14),(4,"S",22),(4,"M",24),(4,"L",14),(4,"XL",6),
-- 5: Chân Váy Ballet
(5,"XS",10),(5,"S",16),(5,"M",20),(5,"L",10),(5,"XL",4),
-- 6: Áo Khoác Denim
(6,"S",15),(6,"M",25),(6,"L",20),(6,"XL",15),
-- 7: Quần Jogger Nam
(7,"S",20),(7,"M",35),(7,"L",30),(7,"XL",25),
-- 8: Đầm Maxi Hoa
(8,"XS",8),(8,"S",14),(8,"M",18),(8,"L",10),(8,"XL",5);
