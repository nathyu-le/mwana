<?php
$score = $assessment['detection_score'];
$signal = $vt['signals']['verdict'] ?? '';
$tone = match (true) {
    $signal === 'VERDICT_MALICIOUS' || $assessment['malicious'] > 0 => 'danger',
    $signal === 'VERDICT_SUSPICIOUS' || $assessment['suspicious'] > 0 => 'warning',
    default => 'neutral',
};
$headline = $available ? $assessment['headline'] : (vtStates()[$state] ?? 'Chưa đủ dữ liệu để đánh giá');
if ($headline === 'Chưa có công cụ nào cảnh báo') $headline = 'Chưa ghi nhận cảnh báo';
?>
<section class="report-hero <?=e($tone)?>" aria-labelledby="report-verdict">
    <div class="verdict-copy">
        <span class="verdict-source"><i aria-hidden="true"></i> VirusTotal</span>
        <h2 id="report-verdict"><?=e(t($headline))?></h2>
        <?php if ($assessment['answered'] > 0): ?>
        <p class="detection-ratio"><?=e(sprintf(t('%d trên %d công cụ báo độc'), $assessment['malicious'], $assessment['answered']))?></p>
        <?php endif ?>
        <?php if ($available): ?>
        <div class="count-line">
            <?php if (isset($assessment['malicious'])): ?><span><i class="dot-mal"></i><?=e($assessment['malicious'])?> <?=e(t('độc hại'))?></span><?php endif ?>
            <?php if (isset($assessment['suspicious'])): ?><span><i class="dot-sus"></i><?=e($assessment['suspicious'])?> <?=e(t('đáng ngờ'))?><?php if ($assessment['suspicious'] > 0 && $assessment['suspicious_percent'] !== null): ?> · <?=e($assessment['suspicious_percent'])?>%<?php endif ?></span><?php endif ?>
        </div>
        <?php require __DIR__.'/classification.php'; ?>
        <?php elseif (!empty($vt['retry_at'])): ?><p><?=e(t('Thử lại sau'))?> <?=e($vt['retry_at'])?></p><?php endif ?>
    </div>
    <?php if ($score !== null): ?>
    <figure class="report-gauge">
        <svg viewBox="0 0 180 180" aria-hidden="true">
            <circle class="g-track" cx="90" cy="90" r="78"/>
            <?php if ($score > 0): ?><circle class="g-value" cx="90" cy="90" r="78" pathLength="100" stroke-dasharray="<?=$score?> 100" transform="rotate(-90 90 90)"/><?php endif ?>
            <path class="g-tick" d="M90 8v9"/>
        </svg>
        <figcaption><span><b><?=$score?></b><small>/100</small></span><?=e(t('Điểm đồng thuận báo độc'))?></figcaption>
    </figure>
    <?php endif ?>
</section>
<?php if ($tone !== 'neutral' || count($assessment['checks']) > 1): ?>
<details class="report-row review-row"><summary><?=e(t('Nên kiểm tra gì?'))?></summary><ul><?php foreach ($assessment['checks'] as $check): ?><li><?=e(t($check))?></li><?php endforeach ?></ul></details>
<?php endif ?>
