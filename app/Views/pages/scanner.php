<h1>Quét tệp</h1>
<form class="dropzone" id="scanner" method="post" enctype="multipart/form-data"><?=token()?>
<input type="hidden" name="action" value="scan">
<?php require dirname(__DIR__).'/partials/scan-art.php'; ?>
<div class="upload-copy"><h2 id="scan-title">Thả tệp vào đây</h2><p id="scan-hint" aria-live="polite">Tối đa 2 MiB mỗi tệp</p>
<label class="picker"><span class="folder-icon" aria-hidden="true"></span>Chọn tệp<input type="file" name="sample" required aria-label="Chọn tệp"></label>
<button type="submit" class="manual-submit">Quét tệp</button></div></form>
<?php require dirname(__DIR__).'/partials/history-list.php'; ?>
