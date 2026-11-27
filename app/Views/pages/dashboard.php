<?php require dirname(__DIR__).'/partials/history-heading.php'; ?>
<div class="metrics">
<?php foreach (['total'=>'Tệp đã phân tích','matched'=>'Khớp chữ ký','partial'=>'Chưa hoàn tất'] as $key=>$label): ?>
<div><small><?=$label?></small><strong><?=(int)$totals[$key]?></strong></div><?php endforeach ?></div>
<?php require dirname(__DIR__).'/partials/history-list.php'; ?>
