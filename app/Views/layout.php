<!doctype html>
<html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($pages[$page]??'Đăng nhập')?> · MalwareGuard</title><link rel="stylesheet" href="app.css"></head>
<body><header><a class="brand" href="?"><b aria-hidden="true">M</b> MalwareGuard</a>
<?php if ($user): ?><nav aria-label="Điều hướng"><?php foreach (['dashboard','scanner','history','admin'] as $tab):
if ($tab==='admin' && $user['role']!=='admin') continue; ?>
<a <?= $page===$tab?'aria-current="page"':'' ?> href="?page=<?=$tab?>"><?=$pages[$tab]?></a>
<?php endforeach ?></nav><form method="post"><?=token()?><button class="quiet" name="action" value="logout">Đăng xuất</button></form><?php endif ?></header>
<main>
<?php if ($error): ?><div role="alert" class="alert"><?=e($error)?> <a href="?">Về trang chính</a></div><?php endif ?>
<?php if (!$user): ?>
<section class="login"><div class="intro"><span class="eyebrow">MALWAREGUARD / STATIC ANALYSIS</span><h1>Phân tích tệp<br><em>nghi nhiễm.</em></h1><p>Đọc cấu trúc PE, đối chiếu chữ ký và kiểm tra các chỉ dấu trong tệp.</p></div>
<form class="card loginform" method="post"><?=token()?><h2>Đăng nhập</h2>
<label>Email<input type="email" name="email" required autocomplete="username"></label>
<label>Mật khẩu<input type="password" name="password" required autocomplete="current-password"></label>
<button name="action" value="login">Vào hệ thống <span>→</span></button></form></section>
<?php elseif ($error): ?><a class="button" href="?page=scanner">Mở trình phân tích</a>
<?php elseif ($page==='scanner'): ?><div class="heading"><div><span class="eyebrow">FILE SCANNER</span><h1>Phân tích file</h1></div></div>
<form method="post" enctype="multipart/form-data" class="card scanform"><?=token()?>
<label class="upload">Chọn tệp cần phân tích<input type="file" name="sample" required><small>Tối đa 2 MiB · Phân tích tĩnh · File tạm được xóa sau khi đọc</small></label>
<button name="action" value="scan">Phân tích ngay <span>→</span></button></form>
<?php elseif ($page==='dashboard' || $page==='history'): ?>
<div class="heading"><div><span class="eyebrow">WORKSPACE / <?=e($user['role'])?></span><h1><?=$pages[$page]?></h1></div>
<a class="button" href="?page=scanner">+ Phân tích file</a></div>
<?php if ($page==='dashboard'): ?><div class="metrics"><?php foreach (['total'=>'File đã phân tích','matched'=>'Khớp chữ ký','partial'=>'Chưa hoàn tất'] as $key=>$label): ?>
<div class="card"><small><?=$label?></small><strong><?=e($totals[$key])?></strong></div><?php endforeach ?></div><?php endif ?>
<section class="card"><h2><?=$page==='dashboard'?'Báo cáo gần đây':'Báo cáo của bạn'?></h2>
<?php if (!$rows): ?><p>Chưa có báo cáo ở trang này.</p><?php else: ?><div class="scroll"><table><tr><th>Tệp</th><th>Kết quả nội bộ</th><th>Thời điểm (DB)</th></tr>
<?php foreach ($rows as $row): ?><tr><td><a href="?page=report&amp;id=<?=(int)$row['id']?>"><?=e($row['filename'])?></a></td>
<td><span class="tag <?=e($row['status'])?>"><?=e($labels[$row['status']])?></span></td><td><?=e($row['created_at'])?></td></tr><?php endforeach ?></table></div><?php endif ?>
<?php if ($page==='history'): ?><div class="pager"><?php if ($p>1): ?><a href="?page=history&amp;p=<?=$p-1?>">← Trang trước</a><?php endif ?>
<?php if ($p*$limit<$totals['total']): ?><a href="?page=history&amp;p=<?=$p+1?>">Trang sau →</a><?php endif ?></div><?php endif ?></section>
<?php elseif ($page==='report'): require __DIR__.'/report.php';
elseif ($page==='admin'): require __DIR__.'/admin.php'; endif ?>
</main></body></html>
