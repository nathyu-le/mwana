<?php
function evidenceTable(array $rows, array $columns): void { ?>
<div class="scroll"><table><thead><tr><?php foreach ($columns as $label): ?><th><?=e(t($label))?></th><?php endforeach ?></tr></thead>
<tbody><?php foreach ($rows as $row): ?><tr><?php foreach ($columns as $k=>$label): ?><td><?=e($row[$k]??'—')?></td><?php endforeach ?></tr><?php endforeach ?></tbody></table></div>
<?php }
$assessment = assessVirusTotal($vt);
$state = $vt['state'] ?? '';
$available = $state === 'available';
$classification = $vt['classification'] ?? [];
$engineRows = [];
$rank = ['malicious'=>0, 'suspicious'=>1];
foreach ($vt['engines'] ?? [] as $name=>$engine) $engineRows[] = ['name'=>$name] + $engine;
usort($engineRows, static fn($a,$b)=>($rank[$a['category']] ?? 2) <=> ($rank[$b['category']] ?? 2));
?>
<article class="file-report">
    <p class="report-path"><a href="?page=history"><?=e(t('Lịch sử'))?></a> / <?=e(t('Báo cáo'))?> #<?=(int)$scan['id']?></p>
    <div class="report-title"><h1><span class="paper-glyph" aria-hidden="true"></span><?=e($scan['filename'])?></h1>
        <a href="?page=export&amp;id=<?=(int)$scan['id']?>"><?=e(t('Tải JSON'))?> ↓</a>
    </div>
    <?php require dirname(__DIR__).'/partials/assessment.php'; ?>
    <?php if ($available): ?>
    <details class="report-row"><summary><?=e(t('Kết quả từng công cụ'))?></summary>
        <div class="report-body">
        <?php if ($engineRows) evidenceTable($engineRows, ['name'=>'Engine','category'=>'Phân loại','result'=>'Tên phát hiện']); ?>
        <?php $stats = []; foreach ($vt['stats'] ?? [] as $category=>$count) $stats[] = ['category'=>$category,'count'=>$count]; ?>
        <?php if ($stats): ?><details><summary><?=e(t('Thống kê'))?></summary><?php evidenceTable($stats,['category'=>'Phân loại','count'=>'Số engine']); ?></details><?php endif ?>
        </div>
    </details>
    <?php endif ?>
    <details class="report-row"><summary><?=e(t('Thông tin tệp'))?></summary>
        <div class="report-body report-meta">
            <div><small>SHA-256</small><code><?=e($scan['sha256'])?></code></div>
            <div><small><?=e(t('Kích thước'))?></small><?=e($report['size'])?> B</div>
            <?php if (!empty($vt['analysis_at'])): ?><div><small><?=e(t('Thời điểm phân tích của VirusTotal'))?></small><?=e($vt['analysis_at'])?></div><?php endif ?>
            <?php if (!empty($vt['queried_at'])): ?><div><small><?=e(t('Tra cứu lần cuối'))?></small><?=e($vt['queried_at'])?><?=!empty($vt['cached'])?' · '.e(t('Bộ nhớ đệm')):''?></div><?php endif ?>
            <?php if ($available): ?><div><small><?=e(t('Điểm đồng thuận báo độc'))?></small><?=e(t('Điểm là tỷ lệ công cụ báo độc trên 100, tính trên các công cụ có kết quả. Lỗi, hết giờ và định dạng không hỗ trợ được loại khỏi mẫu số.'))?></div><?php endif ?>
        </div>
    </details>
    <?php if (isset($report['evidence'], $report['pe'])): ?><details class="report-row"><summary><?=e(t('PHÂN TÍCH NỘI BỘ'))?></summary><?php require dirname(__DIR__).'/partials/legacy-report.php'; ?></details><?php endif ?>
    <div class="report-actions">
        <form method="post"><?=token()?><input type="hidden" name="id" value="<?=(int)$scan['id']?>"><button name="action" value="lookup"><?=e(t('Tra cứu lại'))?> ↻</button></form>
        <?php if ($available): ?><a href="https://www.virustotal.com/gui/file/<?=e($scan['sha256'])?>" rel="noreferrer" target="_blank"><?=e(t('Xem trên VirusTotal'))?> ↗</a><?php endif ?>
    </div>
</article>
