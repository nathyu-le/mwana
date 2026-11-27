<?php
function evidenceTable(array $rows,array $columns): void { ?>
<div class="scroll"><table><thead><tr><?php foreach ($columns as $label): ?><th><?=e(t($label))?></th><?php endforeach ?></tr></thead>
<tbody><?php foreach ($rows as $row): ?><tr><?php foreach ($columns as $k=>$label): ?><td><?=e($row[$k]??'—')?></td><?php endforeach ?></tr><?php endforeach ?></tbody></table></div>
<?php }
?>
<div class="heading"><div><span class="eyebrow"><?= e(t('BÁO CÁO')) ?> #<?=(int)$scan['id']?></span><h1><?=e($scan['filename'])?></h1></div>
<a class="button secondary" href="?page=export&amp;id=<?=(int)$scan['id']?>"><?= e(t('Tải JSON')) ?> ↓</a></div>
<?php require dirname(__DIR__).'/partials/assessment.php'; ?>
<?php if (($vt['state'] ?? '') === 'available') require dirname(__DIR__).'/partials/classification.php'; ?>
<section class="card"><div class="hash"><small>SHA-256</small><code><?=e($scan['sha256'])?></code></div></section>
<section class="card"><small><?= e(t('ĐỐI CHIẾU BÊN NGOÀI')) ?></small><h2>VirusTotal</h2><p><?= e(t('Tra cứu gửi SHA-256 để lấy báo cáo có sẵn.')) ?></p>
<?php if ($vt): $states=vtStates(); ?>
<h3><?=e(t($states[$vt['state']]??'Chưa có dữ liệu'))?></h3><p><?=!empty($vt['cached'])?t('Từ bộ nhớ đệm · '):''?><?=isset($vt['queried_at'])?t('Truy vấn ').e($vt['queried_at']):''?>
<?=isset($vt['retry_at'])?t(' · Thử lại sau ').e($vt['retry_at']):''?></p>
<?php if ($vt['state']==='available'): ?><p><?= e(t('Thời điểm phân tích của VirusTotal')) ?> · <?=e($vt['analysis_at']??t('Không có dữ liệu'))?></p>
<?php $stats=[]; foreach ($vt['stats'] as $k=>$v) $stats[]=['category'=>$k,'count'=>$v??t('Không có dữ liệu')]; evidenceTable($stats,['category'=>'Phân loại','count'=>'Số engine']);
if ($vt['engines']): ?><details><summary><?= e(t('Kết quả từng engine')) ?></summary><?php $engineRows=[]; foreach ($vt['engines'] as $n=>$v) $engineRows[]=['name'=>$n]+$v;
$rank = ['malicious'=>0, 'suspicious'=>1];
usort($engineRows, static fn($a,$b)=>($rank[$a['category']] ?? 2) <=> ($rank[$b['category']] ?? 2));
evidenceTable($engineRows,['name'=>'Engine','category'=>'Phân loại','result'=>'Tên phát hiện']); ?></details><?php endif; endif; endif ?>
<form method="post"><?=token()?><input type="hidden" name="id" value="<?=(int)$scan['id']?>"><button name="action" value="lookup"><?= e(t('Tra cứu SHA-256')) ?></button></form></section>

<?php if (isset($report['evidence'], $report['pe'])) require dirname(__DIR__).'/partials/legacy-report.php'; ?>
