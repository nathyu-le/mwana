<section class="card"><h2>Cài đặt website</h2>
    <form class="fields" method="post">
        <?= token() ?>
        <label>Tên website<input name="site_name" maxlength="80" required value="<?= e($settings['site_name']) ?>"></label>
        <label>Đăng ký tài khoản<select name="registration_open">
            <option value="1" <?= $settings['registration_open'] ? 'selected' : '' ?>>Cho phép</option>
            <option value="0" <?= !$settings['registration_open'] ? 'selected' : '' ?>>Tạm đóng</option>
        </select></label>
        <button name="action" value="save_site">Lưu cài đặt</button>
    </form>
</section>
