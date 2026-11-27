<section class="login-panel">
<div class="login-visual" aria-hidden="true"><?php require dirname(__DIR__).'/partials/scan-art.php'; ?></div>
<form class="loginform" method="post"><?=token()?><h1>Đăng nhập</h1>
<label>Email<input type="email" name="email" required autocomplete="username"></label>
<label>Mật khẩu<input type="password" name="password" required autocomplete="current-password"></label>
<button name="action" value="login">Vào hệ thống <span aria-hidden="true">→</span></button></form>
</section>
