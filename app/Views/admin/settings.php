<section class="card"><h2><?= e(t('Cài đặt website')) ?></h2>
    <form class="fields" method="post">
        <?= token() ?>
        <label><?= e(t('Tên website')) ?><input name="site_name" maxlength="80" required value="<?= e($settings['site_name']) ?>"></label>
        <label><?= e(t('Đăng ký tài khoản')) ?><select name="registration_open">
            <option value="1" <?= $settings['registration_open'] ? 'selected' : '' ?>><?= e(t('Cho phép')) ?></option>
            <option value="0" <?= !$settings['registration_open'] ? 'selected' : '' ?>><?= e(t('Tạm đóng')) ?></option>
        </select></label>
        <label><?= e(t('Ngôn ngữ mặc định')) ?><select name="default_locale">
            <option value="vi" <?= $settings['default_locale'] === 'vi' ? 'selected' : '' ?>>Tiếng Việt</option>
            <option value="en" <?= $settings['default_locale'] === 'en' ? 'selected' : '' ?>>English</option>
        </select></label>
        <button name="action" value="save_site"><?= e(t('Lưu cài đặt')) ?></button>
    </form>
</section>
<section class="card"><h2>VirusTotal</h2><p>API key · <?=e(t(empty(config()['vt_key']) ? 'Chưa cấu hình' : 'Đã cấu hình'))?></p></section>
