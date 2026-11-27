<div class="heading"><h1>Admin Panel</h1></div>
<div class="admin-tabs" aria-label="<?= e(t('Quản trị')) ?>">
    <?php foreach ($adminTabs as $key => $title): ?>
        <a href="?page=admin&amp;tab=<?= e($key) ?>" <?= $adminTab === $key ? 'aria-current="page"' : '' ?>><?= e(t($title)) ?></a>
    <?php endforeach ?>
</div>
<?php require dirname(__DIR__).'/admin/'.$adminTab.'.php'; ?>
