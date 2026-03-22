-- ============================================================
-- HKT SHOP - SEED DATA (user-provided real products & images)
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
-- PRODUCTS
-- ============================================================
INSERT INTO products
  (category_id, name, slug, sku, description, price, stock, image, is_active, is_featured)
VALUES
 
-- Product 1: Ao So Mi Nam -> Ao Nam (cat 1)
(1,
 "Áo Sơ Mi Nam Dài Tay Stretch Cao Cấp",
 "ao-so-mi-nam-dai-tay-stretch",
 "AM-001",
 "Áo sơ mi nam dài tay chất liệu stretch co giãn 4 chiều thoải mái, thiết kế cổ bẻ thanh lịch phù hợp đi làm và dạo phố. Chất vải mềm mịn không nhăn, giữ form tốt suốt cả ngày.",
 499000, 100,
 "https://i5.walmartimages.com/seo/Alimens-Gentle-Mens-Long-Sleeve-Stretch-Dress-Shirts-Casual-Button-Down-Shirt_d5cf7f15-87a2-48ac-84b9-a27401d84970.25eba54662edc99079389825a966ee00.png?odnWidth=180&odnHeight=180&odnBg=ffffff",
 1, 1),
 
-- Product 2: Ao Thun In Chu -> Ao Nam (cat 1)
(1,
 "Áo Thun Nam In Chữ Best Friend Ever",
 "ao-thun-nam-in-chu-best-friend-ever",
 "AM-002",
 "Áo thun nam in chữ hài hước Best Friend Ever, chất cotton 100% mềm mịn thoáng mát, màu sắc tươi không phai sau nhiều lần giặt. Món quà tặng bạn bè cực ý nghĩa.",
 249000, 120,
 "https://m.media-amazon.com/images/I/B1pppR4gVKL._CLa%7C500%2C468%7C71LyF4uUprL.png%7C0%2C0%2C500%2C468%2B0.0%2C0.0%2C500.0%2C468.0_AC_SR400_.png",
 1, 0),
 
-- Product 3: Ao Thun Bang On Bro -> Ao Nam (cat 1)
(1,
 "Áo Thun Nam Bang On Bro",
 "ao-thun-nam-bang-on-bro",
 "AM-003",
 "Áo thun nam thiết kế Bang On Bro phong cách street cá tính, chất cotton dày 200gsm không bai nhào, form regular thoải mái mặc cả ngày.",
 229000, 90,
 "https://m.media-amazon.com/images/I/B1pppR4gVKL._CLa%7C500%2C468%7C71tdMD3TItL.png%7C0%2C0%2C500%2C468%2B0.0%2C0.0%2C500.0%2C468.0_AC_SR400_.png",
 1, 1),
 
-- Product 4: Quan Jeans Ong Rong -> Quan Nu (cat 4)
(4,
 "Quần Jeans Ống Rộng Lưng Cao Nữ",
 "quan-jeans-ong-rong-lung-cao-nu",
 "QF-001",
 "Quần jeans ống rộng lưng cao phong cách Y2K, chất denim co giãn nhẹ bền màu, form baggy tôn dáng. Phù hợp mặc hàng ngày, đi học hoặc đi chơi cùng bạn bè.",
 389000, 80,
 "https://m.media-amazon.com/images/I/41JR4lcBXuS._AC_SR70_.jpg",
 1, 1),
 
-- Product 5: Chan Vay Ballet -> Dam & Vay (cat 6)
(6,
 "Chân Váy Xòe Dài Phong Cách Ballet",
 "chan-vay-xoe-dai-phong-cach-ballet",
 "DV-001",
 "Chân váy xòe dài full circle kiểu dáng ballet vintage, vải voan mềm bay bổng nhẹ nhàng. Thiết kế lưng thun co giãn vừa mọi vóc dáng, phù hợp biểu diễn, chụp ảnh và dạo phố.",
 349000, 60,
 "https://m.media-amazon.com/images/I/41dL+vU8-SL._AC_SR70_.jpg",
 1, 1);
 
-- ============================================================
-- PRODUCT IMAGES
-- ============================================================
INSERT INTO product_images (product_id, image, sort_order) VALUES
-- Product 1: 4 images
(1, "https://i5.walmartimages.com/seo/Alimens-Gentle-Mens-Long-Sleeve-Stretch-Dress-Shirts-Casual-Button-Down-Shirt_d5cf7f15-87a2-48ac-84b9-a27401d84970.25eba54662edc99079389825a966ee00.png?odnWidth=180&odnHeight=180&odnBg=ffffff", 0),
(1, "https://i5.walmartimages.com/asr/930fcc8b-a370-429c-83d0-ddc836c04675.ca5fe2e823d2422a128de2c0b5f25784.png?odnWidth=180&odnHeight=180&odnBg=ffffff", 1),
(1, "https://i5.walmartimages.com/asr/38c66944-d740-4879-a372-2e9327975244.36a15c64c8c4d722d17eaeaef0969f77.jpeg?odnWidth=180&odnHeight=180&odnBg=ffffff", 2),
(1, "https://i5.walmartimages.com/asr/e8058bc3-d868-4d04-98c6-4ec33a91db0c.4843a95153f555d0482b794bdd8027cd.png?odnWidth=180&odnHeight=180&odnBg=ffffff", 3),
-- Product 2: 1 image
(2, "https://m.media-amazon.com/images/I/B1pppR4gVKL._CLa%7C500%2C468%7C71LyF4uUprL.png%7C0%2C0%2C500%2C468%2B0.0%2C0.0%2C500.0%2C468.0_AC_SR400_.png", 0),
-- Product 3: 1 image
(3, "https://m.media-amazon.com/images/I/B1pppR4gVKL._CLa%7C500%2C468%7C71tdMD3TItL.png%7C0%2C0%2C500%2C468%2B0.0%2C0.0%2C500.0%2C468.0_AC_SR400_.png", 0),
-- Product 4: 2 images
(4, "https://m.media-amazon.com/images/I/41JR4lcBXuS._AC_SR70_.jpg", 0),
(4, "https://m.media-amazon.com/images/I/51CWEG5aaqS._AC_SR70_.jpg", 1),
-- Product 5: 2 images
(5, "https://m.media-amazon.com/images/I/41dL+vU8-SL._AC_SR70_.jpg", 0),
(5, "https://m.media-amazon.com/images/I/41f8H6XGruL._AC_SR70_.jpg", 1);
 
-- ============================================================
-- PRODUCT SIZES
-- ============================================================
INSERT INTO product_sizes (product_id, size, stock) VALUES
-- Product 1: Ao So Mi Nam
(1,"S",18),(1,"M",28),(1,"L",25),(1,"XL",18),(1,"XXL",11),
-- Product 2: Ao Thun Best Friend
(2,"S",25),(2,"M",38),(2,"L",32),(2,"XL",25),
-- Product 3: Ao Thun Bang On Bro
(3,"S",20),(3,"M",30),(3,"L",25),(3,"XL",15),
-- Product 4: Quan Jeans Nu
(4,"XS",14),(4,"S",22),(4,"M",24),(4,"L",14),(4,"XL",6),
-- Product 5: Chan Vay Ballet
(5,"XS",10),(5,"S",16),(5,"M",20),(5,"L",10),(5,"XL",4);