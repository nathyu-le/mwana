<h1><?= e(t('Quét tệp')) ?></h1>
<form class="dropzone" id="scanner"
    data-hash-worker="<?=asset('js/hash-worker.js')?>"
    data-max-bytes="<?=(int)$settings['max_file_mib'] * 1048576?>"
    data-size="<?= e(sprintf(t('Chọn một tệp từ 1 byte đến %d MiB.'), $settings['max_file_mib'])) ?>"
    data-scanning="<?= e(t('Đang quét tệp…')) ?>"
    data-drop="<?= e(t('Thả để quét tệp')) ?>"
    data-single="<?= e(t('Mỗi lượt quét nhận một tệp.')) ?>"
    data-failed="<?= e(t('Không đọc được tệp. Hãy dùng HTTPS và chọn lại tệp.')) ?>" method="post"><?=token()?>
<input type="hidden" name="action" value="scan">
<input type="hidden" name="sha256"><input type="hidden" name="filename"><input type="hidden" name="size">
<?php require dirname(__DIR__).'/partials/scan-art.php'; ?>
<div class="upload-copy"><h2 id="scan-title"><?= e(t('Thả tệp vào đây')) ?></h2><p id="scan-hint" aria-live="polite"><?= e(sprintf(t('Tối đa %d MiB · Chỉ gửi SHA-256 đến VirusTotal'), $settings['max_file_mib'])) ?></p>
<label class="picker"><span class="folder-icon" aria-hidden="true"></span><?= e(t('Chọn tệp')) ?><input type="file" disabled aria-label="<?= e(t('Chọn tệp')) ?>"></label>
<noscript><?= e(t('Bật JavaScript để tính SHA-256.')) ?></noscript></div></form>
<?php require dirname(__DIR__).'/partials/history-list.php'; ?>
