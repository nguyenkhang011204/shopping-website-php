<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../pages/signin.php?redirect=../admin/index.php');
    exit;
}

require_once '../includes/dbconnect.php';

$page = $_GET['page'] ?? 'dashboard';

$routes = [
    'dashboard' => [
        'title' => 'Dashboard',
        'file' => 'pages/dashboard.php',
        'css' => 'assets/css/dashboard.css',
        'text' => 'Trang quản trị'
    ],
    'profile' => [
        'title' => 'Profile-admin',
        'file' => 'pages/profile.php',
        'css' => 'assets/css/profile.css',
        'text' => 'Thông tin cá nhân'
    ],
    'products' => [
        'title' => 'Sản phẩm - admin',
        'file' => 'pages/products.php',
        'css' => 'assets/css/dashboard.css',
        'text' => 'Quản lý sản phẩm'
    ],
    'product-form' => [
        'title' => 'Sản phẩm - admin',
        'file' => 'pages/product_form.php',
        'css' => 'assets/css/dashboard.css',
        'text' => 'Sản phẩm'
    ],

    'manageOrder' => [
        'title' => 'Quản lý đơn hàng - admin',
        'file' => 'pages/manageOrder.php',
        'css' => 'assets/css/manageOrder.css',
        'text' => 'Quản lý đơn hàng'
    ],
    'order-detail' => [
        'title' => 'Chi tiết đơn hàng - admin',
        'file' => 'pages/order-detail.php',
        'css' => 'assets/css/orderDetail.css',
        'text' => 'Chi tiết đơn hàng'
    ],
    'clients' => [
        'title' => 'Khách hàng - admin',
        'file' => 'pages/clients.php',
        'css' => 'assets/css/dashboard.css',
        'text' => 'Quản lý khách hàng'
    ],
    'category' => [
        'title' => 'Danh mục - admin',
        'file' => 'pages/category.php',
        'css' => 'assets/css/dashboard.css',
        'text' => 'Quản lý danh mục'
    ],
    'staffs' => [
        'title' => 'Nhân viên',
        'file' => 'pages/staff.php',
        'css' => 'assets/css/dashboard.css',
        'text' => 'Quản lý nhân viên'
    ]
];

if (isset($routes[$page])) {
    $title = $routes[$page]['title'];
    $content = $routes[$page]['file'];
    $page_css = $routes[$page]['css'];
    $text_title = $routes[$page]['text'];
} else {
    // 404
    $title = "404";
    $content = "pages/404.php";
    $page_css = "assets/css/dashboard.css";
    $text_title = "Không tìm thấy trang";
}

include("includes/layout.php");
