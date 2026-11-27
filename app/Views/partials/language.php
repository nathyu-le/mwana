<form class="language-form" method="post">
    <?= token() ?>
    <label><?= e(t('Ngôn ngữ')) ?><select name="locale">
        <option value="" <?= $choice === '' ? 'selected' : '' ?>><?= e(t('Mặc định')) ?></option>
        <option value="vi" <?= $choice === 'vi' ? 'selected' : '' ?>>Tiếng Việt</option>
        <option value="en" <?= $choice === 'en' ? 'selected' : '' ?>>English</option>
    </select></label>
    <button class="quiet" name="action" value="set_language"><?= e(t('Áp dụng')) ?></button>
</form>
