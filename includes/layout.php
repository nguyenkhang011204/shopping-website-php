<?php
/**
 * Layout Template
 * 
 * Usage in pages:
 * 1. Set variables:
 *    $page_title = "Page Title";
 *    $page_css = "assets/css/main.css";
 *    $base_path = "";  // or "../" for subdirectories
 *    $page_scripts = ["assets/js/script.js"];
 * 
 * 2. Capture page content using output buffering:
 *    ob_start();
 *    // ... page HTML ...
 *    $page_content = ob_get_clean();
 * 
 * 3. Include layout:
 *    include('includes/layout.php');
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Shopping Website'; ?></title>

    <!-- Page-specific CSS -->
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="<?php echo $page_css; ?>">
    <?php endif; ?>

    <!-- Component CSS -->
    <link rel="stylesheet" href="<?php echo isset($base_path) ? $base_path : ''; ?>assets/css/header.css">
    <link rel="stylesheet" href="<?php echo isset($base_path) ? $base_path : ''; ?>assets/css/footer.css">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>

    <!-- Header Component -->
    <?php include_once((isset($base_path) ? $base_path : '') . 'includes/navbar.php'); ?>

    <!-- Main Content -->
    <main class="content">
        <?php
        // Echo page content directly (already captured as HTML string)
        if (isset($page_content)) {
            echo $page_content;
        }
        ?>
    </main>

    <!-- Footer Component -->
    <?php include_once((isset($base_path) ? $base_path : '') . 'includes/footer.php'); ?>

    <!-- Page-specific Scripts -->
    <?php if (isset($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
<script src="<?= $base_path . 'assets/js/header.js' ?>"></script>

</html>