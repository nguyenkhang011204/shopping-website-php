<?php

/**
 * Layout Template
 *
 * Variables to set before including:
 *   $page_title   — string
 *   $page_css     — path to page-specific CSS
 *   $base_path    — "" for root, "../" for subdirs
 *   $page_scripts — array of JS paths
 *   $page_content — captured HTML via ob_start/ob_get_clean
 */
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' — HKT Shop' : 'HKT Shop' ?></title>

    <!-- Page-specific CSS -->
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="<?= $page_css ?>">
    <?php endif; ?>

    <!-- Shared component CSS -->
    <link rel="stylesheet" href="<?= isset($base_path) ? $base_path : '' ?>assets/css/header.css">
    <link rel="stylesheet" href="<?= isset($base_path) ? $base_path : '' ?>assets/css/footer.css">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">
</head>

<body>

    <?php include_once((isset($base_path) ? $base_path : '') . 'includes/header.php'); ?>

    <main class="content">
        <?php if (isset($page_content)) echo $page_content; ?>
    </main>

    <?php include_once((isset($base_path) ? $base_path : '') . 'includes/footer.php'); ?>

    <?php if (isset($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

</body>

</html>