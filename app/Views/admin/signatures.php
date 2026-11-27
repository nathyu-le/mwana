<div class="heading"><h1>Quản lý chữ ký</h1></div>
<section class="card"><h2>Danh sách chữ ký</h2><div class="scroll"><table><tr><th>Tên</th><th>Loại</th><th>Nguồn</th><th>Phiên bản</th><th>Trạng thái</th><th>Thao tác</th></tr>
<?php foreach ($rules as $r): ?><tr><td><?=e($r['name'])?></td><td><?=e($r['kind'])?></td><td><?=e($r['source'])?></td><td><?=e($r['version'])?></td><td><?=$r['active']?'Đang bật':'Đang tắt'?></td>
<td><form method="post"><?=token()?><input name="id" type="hidden" value="<?=(int)$r['id']?>"><input name="active" type="hidden" value="<?=$r['active']?'0':'1'?>"><button class="quiet" name="action" value="rule_toggle"><?=$r['active']?'Tắt':'Bật'?></button></form></td></tr><?php endforeach ?></table></div></section>

<section class="card"><h2>Thêm chữ ký</h2><form method="post" class="fields"><?=token()?>
<label>Tên chữ ký<input name="name" required maxlength="96"></label><label>Loại<select name="kind"><option value="bytes">Mẫu byte chính xác</option><option>sha256</option><option>md5</option></select></label>
<label>Nguồn<input required name="source" maxlength="128"></label><label>Phiên bản<input required name="version" maxlength="48"></label>
<label class="full">Mẫu hex<input required name="pattern" pattern="[A-Fa-f0-9]+" maxlength="512"><small>Byte pattern từ 4 đến 256 byte. Nhập liền, mỗi byte gồm hai ký tự hex.</small></label>
<button name="action" value="rule_add">Lưu chữ ký</button></form></section>
<section class="card"><h2>VirusTotal</h2><p>API key · <?=!empty(config()['vt_key'])?'Đã cấu hình':'Chưa cấu hình'?> &nbsp; / &nbsp; Tích hợp · <?=$enabled?'Đang bật':'Đang tắt'?></p>
<form method="post"><?=token()?><input type="hidden" name="enabled" value="<?=$enabled?'0':'1'?>"><button name="action" value="vt_toggle"><?=$enabled?'Tắt tích hợp':'Bật tích hợp'?></button></form></section>
