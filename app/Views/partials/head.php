<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#101115">
    <title><?= e($pages[$page] ?? 'Đăng nhập') ?> · <?= e($settings['site_name']) ?></title>
    <link rel="icon" href="<?= asset('img/mark.svg') ?>" type="image/svg+xml">
    <?php foreach (['base', 'workspace', 'responsive', 'motion'] as $sheet): ?>
    <link rel="stylesheet" href="<?= asset("css/$sheet.css") ?>">
    <?php endforeach ?>
    <script src="<?= asset('js/startup.js') ?>"></script>
</head>
<body>
