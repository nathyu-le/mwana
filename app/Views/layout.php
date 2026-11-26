<?php
const UI_VERSION = '1.1.0';
$initials = $user ? strtoupper(substr(preg_replace('/[^a-z0-9]/i','',explode('@',$user['email'])[0]),0,2)) : '';
?>
<!doctype html>
<html lang="vi"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#101115">
<title><?=e($pages[$page]??'Đăng nhập')?> · MalwareGuard</title>
<link rel="icon" href="mark.svg" type="image/svg+xml">
<link rel="stylesheet" href="app.css?v=<?=UI_VERSION?>">
<script src="startup.js?v=<?=UI_VERSION?>"></script>
<script src="scanner.js?v=<?=UI_VERSION?>" defer></script>
</head><body>
<div class="splash" role="status" aria-label="Đang tải MalwareGuard"><div class="splash-mark"><img src="mark.svg" width="128" height="84" alt=""></div></div>
<header class="topbar"><div class="bar-inner">
<a class="brand" href="?"><img src="mark.svg" alt="" width="43" height="29">MalwareGuard</a>
<?php if ($user): ?><nav aria-label="Điều hướng chính">
<?php foreach (['scanner','history','admin'] as $tab): if ($tab==='admin' && $user['role']!=='admin') continue; ?>
<a href="?page=<?=$tab?>" <?=($page===$tab || ($page==='report' && $tab==='history'))?'aria-current="page"':''?>><?=$pages[$tab]?></a>
<?php endforeach ?></nav>
<details class="account"><summary aria-label="Tài khoản"><?=e($initials?:'MG')?></summary>
<div class="menu"><strong><?=e($user['email'])?></strong><small><?= $user['role']==='admin'?'Quản trị viên':'Phân tích viên'?> · v<?=UI_VERSION?></small>
<a href="?page=dashboard">Tổng quan</a><form method="post"><?=token()?><button name="action" value="logout">Đăng xuất</button></form></div></details>
<?php endif ?></div></header>
<main class="workspace">
<?php if ($error): ?><div role="alert" class="alert"><?=e($error)?></div><?php endif ?>
<?php if (!$user): ?>
<section class="login-panel">
<div class="login-visual" aria-hidden="true"><?php require __DIR__.'/scan-art.php'; ?></div>
<form class="loginform" method="post"><?=token()?><h1>Đăng nhập</h1>
<label>Email<input type="email" name="email" required autocomplete="username"></label>
<label>Mật khẩu<input type="password" name="password" required autocomplete="current-password"></label>
<button name="action" value="login">Vào hệ thống <span aria-hidden="true">→</span></button></form>
</section>
<?php elseif ($error): ?><a class="button secondary" href="?page=scanner">Quay lại trình quét</a>
<?php elseif ($page==='scanner'): ?>
<h1>Quét tệp</h1>
<form class="dropzone" id="scanner" method="post" enctype="multipart/form-data"><?=token()?>
<input type="hidden" name="action" value="scan">
<?php require __DIR__.'/scan-art.php'; ?>
<div class="upload-copy"><h2 id="scan-title">Thả tệp vào đây</h2><p id="scan-hint" aria-live="polite">Tối đa 2 MiB mỗi tệp</p>
<label class="picker"><span class="folder-icon" aria-hidden="true"></span>Chọn tệp<input type="file" name="sample" required aria-label="Chọn tệp"></label>
<button type="submit" class="manual-submit">Quét tệp</button></div></form>
<?php require __DIR__.'/history.php'; ?>
<?php elseif ($page==='history' || $page==='dashboard'): ?>
<div class="heading"><h1><?=$pages[$page]?></h1><a class="button secondary" href="?page=scanner">+ Quét tệp</a></div>
<?php if ($page==='dashboard'): ?><div class="metrics">
<?php foreach (['total'=>'Tệp đã phân tích','matched'=>'Khớp chữ ký','partial'=>'Chưa hoàn tất'] as $key=>$label): ?>
<div><small><?=$label?></small><strong><?=(int)$totals[$key]?></strong></div><?php endforeach ?></div><?php endif ?>
<?php require __DIR__.'/history.php'; ?>
<?php elseif ($page==='report'): require __DIR__.'/report.php';
elseif ($page==='admin'): require __DIR__.'/admin.php'; endif ?>
</main></body></html>
