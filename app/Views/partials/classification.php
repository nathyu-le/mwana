<?php
$tagGroups = [
    'Mức nguy hiểm'=>$assessment['severity'] === 'Chưa xác định' ? [] : [$assessment['severity']],
    'Loại mối đe dọa'=>$classification['categories'] ?? [],
    'Họ mã độc'=>$classification['families'] ?? [],
];
foreach ($tagGroups as $label=>$values): if (!$values) continue; ?>
<div class="threat-labels"><small><?=e(t($label))?></small>
    <?php foreach ($values as $value): ?><span><?=e($label === 'Mức nguy hiểm' ? t($value) : $value)?></span><?php endforeach ?>
</div>
<?php endforeach ?>
<?php if (!empty($classification['vendor_labels'])): ?>
<div class="threat-labels vendor-labels"><small><?=e(t('Nhãn của công cụ'))?></small>
    <?php foreach ($classification['vendor_labels'] as $entry): ?><span><?=e($entry['category'])?> <em>· <?=e($entry['name'])?></em></span><?php endforeach ?>
</div>
<?php endif ?>
