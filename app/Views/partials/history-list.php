<section class="history"><div class="section-head">
<h2><?=$page==='history'?t('Báo cáo của bạn'):t('Lần quét gần đây')?></h2>
<?php if ($page==='history'): ?><small><?=(int)$totals['total']?> <?= e(t('lượt quét')) ?></small>
<?php else: ?><a href="?page=history"><?= e(t('Xem lịch sử')) ?> <i class="diagonal-arrow" aria-hidden="true"></i></a><?php endif ?></div>
<?php if (!$rows): ?><p class="empty"><?= e(t('Chưa có lượt quét ở đây.')) ?></p><?php endif ?>
<?php foreach ($rows as $row):
$size = $row['bytes'];
$unit = $size>=1048576 ? number_format($size/1048576,1,',','').' MiB' : ($size>=1024 ? round($size/1024).' KiB' : $size.' B');
?>
<a class="scan-row" href="?page=report&amp;id=<?=(int)$row['id']?>">
<span class="file-icon" aria-hidden="true"><i></i></span>
<span class="record"><strong><?=e($row['filename'])?></strong><small><?= $size!==null ? e($unit).' · ' : ''?><time><?=e(substr($row['created_at'],0,16))?></time></small></span>
<span class="status <?=e($row['status'])?>"><?=e(t($labels[$row['status']]??$row['status']))?></span><span class="chevron" aria-hidden="true">›</span>
</a><?php endforeach ?>
<?php if ($page==='history'): ?><div class="pager"><?php if ($p>1): ?><a href="?page=history&amp;p=<?=$p-1?>">← <?= e(t('Trang trước')) ?></a><?php endif ?>
<?php if ($p*$limit<$totals['total']): ?><a href="?page=history&amp;p=<?=$p+1?>"><?= e(t('Trang sau')) ?> →</a><?php endif ?></div><?php endif ?>
</section>
