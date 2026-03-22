<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Admin') ?> — HKT Shop Admin</title>

    <!-- Same fonts as storefront -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Playfair+Display:wght@600;700&display=swap">

    <!-- Same Font Awesome version as storefront (6.5, not 7) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Admin base (shared across all admin pages) -->
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/header.css">

    <!-- Page-specific CSS -->
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($page_css) ?>">
    <?php endif; ?>

    <!-- Chart.js (same CDN pattern as storefront uses for FA) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <!-- SIDEBAR -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- MAIN CONTENT AREA -->
    <div class="admin-wrapper">

        <!-- HEADER -->
        <?php include 'includes/header.php'; ?>

        <!-- PAGE CONTENT -->
        <main class="admin-main">
            
        </main>
     
               </div>
   
     <!-- Page-specific scripts (same pattern as $page_scripts in storefront) -->
       <?php if (isset($page_scripts)): ?>
                    <?php foreach ($page_scripts as $script): ?>
                    <script src="<?= htmlspecialchars($script) ?>"></script>
            <?php endforeach; ?>
    <?php endif; ?>

</body>

</html>