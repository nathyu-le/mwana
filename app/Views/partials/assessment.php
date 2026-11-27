<?php $assessment = assessVirusTotal($vt); ?>
<section class="card">
    <small><?=e(t('Đánh giá từ báo cáo VirusTotal'))?></small>
    <h2><?=e(t($assessment['headline']))?></h2>
    <p><?=e(t('Mức nguy hiểm do VirusTotal cung cấp'))?> · <strong><?=e(t($assessment['severity']))?></strong></p>
    <?php if (($vt['state'] ?? '') === 'available'): ?>
    <div class="stat-grid">
        <div class="card"><small><?=e(t('Điểm đồng thuận báo độc'))?></small><strong><?=isset($assessment['detection_score']) ? $assessment['detection_score'].'/100' : '—'?></strong></div>
        <div class="card"><small><?=e(t('Công cụ báo độc hại'))?></small><strong><?=e($assessment['malicious'] ?? '—')?> / <?=e($assessment['answered'] ?? '—')?></strong></div>
        <div class="card"><small><?=e(t('Công cụ báo đáng ngờ'))?></small><strong><?=e($assessment['suspicious'] ?? '—')?> / <?=e($assessment['answered'] ?? '—')?></strong><?php if (isset($assessment['suspicious_percent'])): ?><?=e($assessment['suspicious_percent'])?>%<?php endif ?></div>
    </div>
    <p><?=e(t('Điểm là tỷ lệ công cụ báo độc trên 100, tính trên các công cụ có kết quả. Lỗi, hết giờ và định dạng không hỗ trợ được loại khỏi mẫu số.'))?></p>
    <?php endif ?>
    <h3><?=e(t('Nên kiểm tra gì?'))?></h3>
    <ul><?php foreach ($assessment['checks'] as $check): ?><li><?=e(t($check))?></li><?php endforeach ?></ul>
    <?php if (($vt['state'] ?? '') === 'available'): ?>
        <a href="https://www.virustotal.com/gui/file/<?=e($scan['sha256'])?>" rel="noreferrer" target="_blank"><?=e(t('Mở báo cáo gốc trên VirusTotal'))?> ↗</a>
    <?php endif ?>
</section>
