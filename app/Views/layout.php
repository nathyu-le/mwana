<?php
const UI_VERSION = '1.2.0';

function asset(string $file): string
{
    $prefix = defined('ASSET_DIRECTORY') ? ASSET_DIRECTORY : 'assets';
    return e($prefix . '/' . $file . '?v=' . UI_VERSION);
}

$view = !$user ? 'login' : ($error ? 'error' : $page);
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
?>
<main class="workspace">
    <?php if ($error): ?><div role="alert" class="alert"><?= e($error) ?></div><?php endif ?>
    <?php require __DIR__ . '/pages/' . $view . '.php'; ?>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
