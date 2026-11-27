<div class="heading"><h1>Tài khoản</h1></div>
<section class="card"><h2>Đổi mật khẩu</h2>
    <form method="post" class="fields">
        <?= token() ?>
        <label class="full">Mật khẩu hiện tại<input type="password" name="current_password" autocomplete="current-password" required></label>
        <?php require dirname(__DIR__).'/partials/new-password.php'; ?>
        <button name="action" value="change_password">Đổi mật khẩu</button>
    </form>
</section>
