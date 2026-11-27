<?php $classification = $vt['classification'] ?? []; ?>
<section class="card">
    <small>VirusTotal</small><h2><?=e(t('Nhận diện mã độc'))?></h2>
    <?php foreach (['categories'=>'Loại mối đe dọa', 'families'=>'Họ mã độc'] as $key=>$label): ?>
        <p><strong><?=e(t($label))?></strong> · <?=e(implode(', ', $classification[$key] ?? []) ?: t('Chưa xác định'))?></p>
    <?php endforeach ?>
    <?php if (!empty($classification['vendor_labels'])): ?>
        <h3><?=e(t('Nhãn do từng công cụ cung cấp'))?></h3>
        <?php evidenceTable($classification['vendor_labels'], ['name'=>'Engine','category'=>'Loại','result'=>'Tên phát hiện']); ?>
    <?php endif ?>
</section>
