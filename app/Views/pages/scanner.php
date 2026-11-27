<h1><?= e(t('Quét tệp')) ?></h1>
<form class="dropzone" id="scanner"
    data-size="<?= e(t('Chọn một tệp từ 1 byte đến 2 MiB.')) ?>"
    data-scanning="<?= e(t('Đang quét tệp…')) ?>"
    data-drop="<?= e(t('Thả để quét tệp')) ?>"
    data-single="<?= e(t('Mỗi lượt quét nhận một tệp.')) ?>"
    data-picker="<?= e(t('Dùng nút Chọn tệp để tiếp tục.')) ?>" method="post" enctype="multipart/form-data"><?=token()?>
<input type="hidden" name="action" value="scan">
<?php require dirname(__DIR__).'/partials/scan-art.php'; ?>
<div class="upload-copy"><h2 id="scan-title"><?= e(t('Thả tệp vào đây')) ?></h2><p id="scan-hint" aria-live="polite"><?= e(t('Tối đa 2 MiB mỗi tệp')) ?></p>
<label class="picker"><span class="folder-icon" aria-hidden="true"></span><?= e(t('Chọn tệp')) ?><input type="file" name="sample" required aria-label="<?= e(t('Chọn tệp')) ?>"></label>
<button type="submit" class="manual-submit"><?= e(t('Quét tệp')) ?></button></div></form>
<?php require dirname(__DIR__).'/partials/history-list.php'; ?>
