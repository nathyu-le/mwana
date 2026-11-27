<div class="heading"><h1><?= e(t('Quản lý chữ ký')) ?></h1></div>
<section class="card"><h2><?= e(t('Danh sách chữ ký')) ?></h2><div class="scroll"><table><tr><th><?= e(t('Tên')) ?></th><th><?= e(t('Loại')) ?></th><th><?= e(t('Nguồn')) ?></th><th><?= e(t('Phiên bản')) ?></th><th><?= e(t('Trạng thái')) ?></th><th><?= e(t('Thao tác')) ?></th></tr>
<?php foreach ($rules as $r): ?><tr><td><?=e($r['name'])?></td><td><?=e($r['kind'])?></td><td><?=e($r['source'])?></td><td><?=e($r['version'])?></td><td><?=$r['active']?t('Đang bật'):t('Đang tắt')?></td>
<td><form method="post"><?=token()?><input name="id" type="hidden" value="<?=(int)$r['id']?>"><input name="active" type="hidden" value="<?=$r['active']?'0':'1'?>"><button class="quiet" name="action" value="rule_toggle"><?=$r['active']?t('Tắt'):t('Bật')?></button></form></td></tr><?php endforeach ?></table></div></section>

<section class="card"><h2><?= e(t('Thêm chữ ký')) ?></h2><form method="post" class="fields"><?=token()?>
<label><?= e(t('Tên chữ ký')) ?><input name="name" required maxlength="96"></label><label><?= e(t('Loại')) ?><select name="kind"><option value="bytes"><?= e(t('Mẫu byte chính xác')) ?></option><option>sha256</option><option>md5</option></select></label>
<label><?= e(t('Nguồn')) ?><input required name="source" maxlength="128"></label><label><?= e(t('Phiên bản')) ?><input required name="version" maxlength="48"></label>
<label class="full"><?= e(t('Mẫu hex')) ?><input required name="pattern" pattern="[A-Fa-f0-9]+" maxlength="512"><small><?= e(t('Byte pattern từ 4 đến 256 byte. Nhập liền, mỗi byte gồm hai ký tự hex.')) ?></small></label>
<button name="action" value="rule_add"><?= e(t('Lưu chữ ký')) ?></button></form></section>
<section class="card"><h2>VirusTotal</h2><p>API key · <?=!empty(config()['vt_key'])?t('Đã cấu hình'):t('Chưa cấu hình')?> &nbsp; / &nbsp; <?= e(t('Tích hợp')) ?> · <?=$enabled?t('Đang bật'):t('Đang tắt')?></p>
<form method="post"><?=token()?><input type="hidden" name="enabled" value="<?=$enabled?'0':'1'?>"><button name="action" value="vt_toggle"><?=$enabled?t('Tắt tích hợp'):t('Bật tích hợp')?></button></form></section>
