<section class="login-panel">
    <div class="login-visual" aria-hidden="true"><?php require dirname(__DIR__).'/partials/scan-art.php'; ?></div>
    <form class="loginform" method="post">
        <?= token() ?><h1>Tạo tài khoản</h1>
        <label>Email<input name="email" type="email" maxlength="190" autocomplete="email" required></label>
        <?php require dirname(__DIR__).'/partials/new-password.php'; ?>
        <button name="action" value="register">Đăng ký</button>
        <p><a href="?page=login">Trở về đăng nhập</a></p>
    </form>
</section>
