<div class="heading"><h1><?= e(t('Tài khoản')) ?></h1></div>
<section class="card"><h2><?= e(t('Đổi mật khẩu')) ?></h2>
    <form method="post" class="fields">
        <?= token() ?>
        <label class="full"><?= e(t('Mật khẩu hiện tại')) ?><input type="password" name="current_password" autocomplete="current-password" required></label>
        <?php require dirname(__DIR__).'/partials/new-password.php'; ?>
        <button name="action" value="change_password"><?= e(t('Đổi mật khẩu')) ?></button>
    </form>
</section>
<section class="card"><?php require dirname(__DIR__).'/partials/language.php'; ?></section>
