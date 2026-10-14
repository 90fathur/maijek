<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Maijek Admin' ?></title>
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css?v=') . time() ?>">
</head>
<body>
    
    <!-- Sidebar -->
    <?= $this->include('admin/layout/sidebar') ?>

    <div class="main-content">
        <!-- Topbar -->
        <?= $this->include('admin/layout/topbar') ?>

        <!-- Content -->
        <main class="content-area">
            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <!-- Custom JS -->
    <script src="<?= base_url('assets/js/admin.js?v=') . time() ?>"></script>
</body>
</html>
