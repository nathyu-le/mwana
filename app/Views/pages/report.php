<?php
function evidenceTable(array $rows,array $columns): void { ?>
<div class="scroll"><table><thead><tr><?php foreach ($columns as $label): ?><th><?=e(t($label))?></th><?php endforeach ?></tr></thead>
<tbody><?php foreach ($rows as $row): ?><tr><?php foreach ($columns as $k=>$label): ?><td><?=e($row[$k]??'—')?></td><?php endforeach ?></tr><?php endforeach ?></tbody></table></div>
<?php }
$ev=$report['evidence']; $pe=$report['pe']; ?>
<div class="heading"><div><span class="eyebrow"><?= e(t('BÁO CÁO')) ?> #<?=(int)$scan['id']?></span><h1><?=e($scan['filename'])?></h1></div>
<a class="button secondary" href="?page=export&amp;id=<?=(int)$scan['id']?>"><?= e(t('Tải JSON')) ?> ↓</a></div>
<section class="card"><small><?= e(t('PHÂN TÍCH NỘI BỘ')) ?></small><h2 class="status <?=e($report['status'])?>"><?=e($labels[$report['status']])?></h2>
<p><?=e($report['size'])?> byte · <?=e($report['mime'])?> · <?=e($report['rules'])?> <?= e(t('chữ ký')) ?> · <?=e($report['elapsed_ms'])?> ms</p>
<?php foreach ($report['hashes'] as $k=>$v): ?><div class="hash"><small><?=e(strtoupper($k))?></small><code><?=e($v)?></code></div><?php endforeach ?>
<?php if ($report['findings']): evidenceTable($report['findings'],['name'=>'Chữ ký','version'=>'Phiên bản','source'=>'Nguồn','kind'=>'Loại','pattern'=>'Mẫu khớp','offset'=>'Offset đầu tiên']);
else: ?><p><?= e(t('Chưa tìm thấy chữ ký khớp trong bộ quy tắc đang bật. Tệp vẫn có thể chứa dấu hiệu khác.')) ?></p><?php endif ?></section>
<section class="card"><h2>PE Explorer</h2>
<?php if ($pe['state']==='valid'): ?><p><?=e($pe['format'])?> · <?= e(t('Máy')) ?> <?=e($pe['machine'])?> · Characteristics <?=e($pe['characteristics'])?></p>
<p>Entry point <?=e($pe['entry_point'])?> · Image base <?=e($pe['image_base'])?> · Size of image <?=e($pe['size_of_image'])?></p>
<?php evidenceTable($pe['sections'],['name'=>'Section','rva'=>'RVA','virtual_size'=>'Virtual size','raw_offset'=>'Raw offset','raw_size'=>'Raw size']);
else: ?><p><?=$pe['state']==='other'?t('Không có DOS header MZ.'):t('Header hoặc phạm vi section không hợp lệ. Chưa hoàn tất phân tích PE.')?></p><?php endif ?></section>
<section class="card"><h2><?= e(t('Chỉ dấu nhúng')) ?></h2><?php if ($ev['indicators']): evidenceTable($ev['indicators'],['type'=>'Loại','value'=>'Giá trị','offset'=>'Byte offset','encoding'=>'Encoding']);
else: ?><p><?= e(t('Không tìm thấy URL, domain hoặc IPv4 trong các chuỗi đã đọc.')) ?></p><?php endif ?>
<details><summary><?= e(t('Chuỗi trích xuất')) ?> (<?=count($ev['strings'])?>)</summary><?php evidenceTable($ev['strings'],['offset'=>'Byte offset','encoding'=>'Encoding','value'=>'Chuỗi']); ?></details>
<?php if ($ev['limited']): ?><p><?= e(t('Đã đạt giới hạn trích xuất hoặc độ dài hiển thị. Kết quả chuỗi và chỉ dấu có thể chưa đầy đủ.')) ?></p><?php endif ?></section>
<section class="card"><small><?= e(t('ĐỐI CHIẾU BÊN NGOÀI')) ?></small><h2>VirusTotal</h2><p><?= e(t('Tra cứu gửi SHA-256 để lấy báo cáo có sẵn.')) ?></p>
<?php if ($vt): $states=['available'=>'Có báo cáo','not_found'=>'Chưa có báo cáo cho hash này','disabled'=>'Tích hợp đang tắt','missing_key'=>'Chưa cấu hình API key','denied'=>'Lỗi xác thực hoặc quyền truy cập','rate_limit'=>'Đang chờ hạn mức','unavailable'=>'Dịch vụ hoặc dữ liệu không khả dụng']; ?>
<h3><?=e(t($states[$vt['state']]??'Chưa có dữ liệu'))?></h3><p><?=!empty($vt['cached'])?t('Từ bộ nhớ đệm · '):''?><?=isset($vt['queried_at'])?t('Truy vấn ').e($vt['queried_at']):''?>
<?=isset($vt['retry_at'])?t(' · Thử lại sau ').e($vt['retry_at']):''?></p>
<?php if ($vt['state']==='available'): ?><p><?= e(t('Thời điểm phân tích của VirusTotal')) ?> · <?=e($vt['analysis_at']??t('Không có dữ liệu'))?></p>
<?php $stats=[]; foreach ($vt['stats'] as $k=>$v) $stats[]=['category'=>$k,'count'=>$v??t('Không có dữ liệu')]; evidenceTable($stats,['category'=>'Phân loại','count'=>'Số engine']);
if ($vt['engines']): ?><details><summary><?= e(t('Kết quả từng engine')) ?></summary><?php $engineRows=[]; foreach ($vt['engines'] as $n=>$v) $engineRows[]=['name'=>$n]+$v;
evidenceTable($engineRows,['name'=>'Engine','category'=>'Phân loại','result'=>'Tên phát hiện']); ?></details><?php endif; endif; endif ?>
<form method="post"><?=token()?><input type="hidden" name="id" value="<?=(int)$scan['id']?>"><button name="action" value="lookup"><?= e(t('Tra cứu SHA-256')) ?></button></form></section>
