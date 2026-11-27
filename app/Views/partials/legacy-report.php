<?php $ev=$report['evidence']; $pe=$report['pe']; ?>
<section class="card"><small><?= e(t('PHÂN TÍCH NỘI BỘ')) ?></small><h2 class="status <?=e($report['status'])?>"><?=e(t($labels[$report['status']]))?></h2>
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
