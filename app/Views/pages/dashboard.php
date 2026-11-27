<?php require dirname(__DIR__).'/partials/history-heading.php'; ?>
<div class="metrics">
<?php foreach (['total'=>'Tệp đã phân tích','matched'=>'Có cảnh báo','partial'=>'Chưa có báo cáo cho hash này'] as $key=>$label): ?>
<div><small><?=t($label)?></small><strong><?=(int)$totals[$key]?></strong></div><?php endforeach ?></div>
<?php require dirname(__DIR__).'/partials/history-list.php'; ?>
