<section class="login-panel">
<div class="login-visual" aria-hidden="true"><?php require dirname(__DIR__).'/partials/scan-art.php'; ?></div>
<form class="loginform" method="post"><?=token()?><h1><?= e(t('Đăng nhập')) ?></h1>
<label>Email<input type="email" name="email" required autocomplete="username"></label>
<label><?= e(t('Mật khẩu')) ?><input type="password" name="password" required autocomplete="current-password"></label>
<button name="action" value="login"><?= e(t('Vào hệ thống')) ?> <span aria-hidden="true">→</span></button><?php if ($settings['registration_open']): ?><p><a href="?page=register"><?= e(t('Tạo tài khoản')) ?></a></p><?php endif ?></form>
</section>
