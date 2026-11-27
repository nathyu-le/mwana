<?php
$initials = $user ? strtoupper(substr(preg_replace('/[^a-z0-9]/i','',explode('@',$user['email'])[0]),0,2)) : '';
?>
<div class="splash" role="status" aria-label="Đang tải MalwareGuard">
    <div class="splash-mark"><img src="<?= asset('img/mark.svg') ?>" width="128" height="84" alt=""></div>
</div>
<header class="topbar"><div class="bar-inner">
<a class="brand" href="?"><img src="<?= asset('img/mark.svg') ?>" alt="" width="43" height="29">MalwareGuard</a>
<?php if ($user): ?><nav aria-label="Điều hướng chính">
<?php foreach (['scanner','history','admin'] as $tab): if ($tab==='admin' && $user['role']!=='admin') continue; ?>
<a href="?page=<?=$tab?>" <?=($page===$tab || ($page==='report' && $tab==='history'))?'aria-current="page"':''?>><?=$pages[$tab]?></a>
<?php endforeach ?></nav>
<details class="account"><summary aria-label="Tài khoản"><?=e($initials?:'MG')?></summary>
<div class="menu"><strong><?=e($user['email'])?></strong><small><?= $user['role']==='admin'?'Quản trị viên':'Phân tích viên'?> · v<?=UI_VERSION?></small>
<a href="?page=dashboard">Tổng quan</a><form method="post"><?=token()?><button name="action" value="logout">Đăng xuất</button></form></div></details>
<?php endif ?></div></header>
