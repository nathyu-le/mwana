<section><h2>Thống kê toàn hệ thống</h2>
    <div class="stat-grid">
        <?php foreach (['users'=>'Tài khoản', 'active_users'=>'Người đã quét', 'scans'=>'Tổng lượt quét', 'matched'=>'Khớp chữ ký', 'partial'=>'Chưa hoàn tất', 'rules'=>'Chữ ký đang bật'] as $key=>$label): ?>
        <div class="card"><small><?= e($label) ?></small><strong><?= (int)$counts[$key] ?></strong></div>
        <?php endforeach ?>
    </div>
</section>
<section class="card"><h2>Hoạt động 7 ngày qua</h2>
    <table><thead><tr><th>Ngày</th><th>Lượt quét</th><th>Khớp chữ ký</th></tr></thead><tbody>
        <?php foreach ($days as $day=>$values): ?>
        <tr><td><?= e($day) ?></td><td><?= (int)$values['scans'] ?></td><td><?= (int)$values['matched'] ?></td></tr>
        <?php endforeach ?>
    </tbody></table>
</section>
