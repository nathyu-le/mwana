<section class="card vt-connection">
    <div><h2>VirusTotal API</h2>
        <p class="connection-state"><i class="lamp-<?=e($indicator[0])?>" aria-hidden="true"></i><?=e(t($indicator[1]))?></p>
        <?php if ($indicator[2] !== null): ?><small><?=e(t('Kiểm tra lần cuối'))?> · <time><?=e(gmdate('Y-m-d H:i', $indicator[2]))?> UTC</time></small><?php endif ?>
    </div>
    <?php if (!empty(config()['vt_key'])): ?><form method="post"><?=token()?><button class="secondary" name="action" value="test_vt"><?=e(t('Kiểm tra kết nối'))?></button></form><?php endif ?>
</section>
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
        <label><?= e(t('Loading khi tải lại trang (giây)')) ?>
            <input name="reload_seconds" type="number" min="0" max="30" step="0.1" required
                aria-describedby="reload-help" value="<?=e($settings['reload_seconds'])?>">
            <small id="reload-help"><?= e(t('0 giây để tắt.')) ?></small>
        </label>
        <label><?= e(t('Dung lượng tối đa mỗi tệp (MiB)')) ?>
            <input name="max_file_mib" type="number" min="1" max="1024" step="1" required
                value="<?=e($settings['max_file_mib'])?>">
        </label>
        <button name="action" value="save_site"><?= e(t('Lưu cài đặt')) ?></button>
    </form>
</section>
