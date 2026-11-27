<section><h2><?= e(t('Thống kê toàn hệ thống')) ?></h2>
    <div class="stat-grid">
        <?php foreach (['users'=>'Tài khoản', 'active_users'=>'Người đã quét', 'scans'=>'Tổng lượt quét', 'matched'=>'Có cảnh báo', 'partial'=>'Chưa có báo cáo cho hash này'] as $key=>$label): ?>
        <div class="card"><small><?= e(t($label)) ?></small><strong><?= (int)$counts[$key] ?></strong></div>
        <?php endforeach ?>
    </div>
</section>
<section class="card"><h2><?= e(t('Hoạt động 7 ngày qua')) ?></h2>
    <table><thead><tr><th><?= e(t('Ngày')) ?></th><th><?= e(t('Lượt quét')) ?></th><th><?= e(t('Có cảnh báo')) ?></th></tr></thead><tbody>
        <?php foreach ($days as $day=>$values): ?>
        <tr><td><?= e($day) ?></td><td><?= (int)$values['scans'] ?></td><td><?= (int)$values['matched'] ?></td></tr>
        <?php endforeach ?>
    </tbody></table>
</section>
