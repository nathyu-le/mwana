<?php
declare(strict_types=1);
ini_set('display_errors','0');
ini_set('session.use_strict_mode','1');
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off']);
session_start();
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'self'; object-src 'none'; base-uri 'none'; frame-ancestors 'none'; form-action 'self'");
require dirname(__DIR__).'/bootstrap.php';
require dirname(__DIR__).'/Analysis/Scanner.php';
require dirname(__DIR__).'/Actions.php';
require dirname(__DIR__).'/Accounts/passwords.php';
require dirname(__DIR__).'/Admin/settings.php';
require dirname(__DIR__).'/I18n/locale.php';
require dirname(__DIR__).'/Admin/statistics.php';
require dirname(__DIR__).'/VirusTotal.php';

$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
function token(): string { return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">'; }
$labels = ['matched'=>'Khớp chữ ký','unmatched'=>'Chưa khớp','partial'=>'Chưa hoàn tất'];
$pages = ['dashboard'=>'Tổng quan','scanner'=>'Trình quét','history'=>'Lịch sử','admin'=>'Admin Panel','report'=>'Báo cáo','account'=>'Tài khoản','register'=>'Tạo tài khoản','login'=>'Đăng nhập'];
$user = $_SESSION['user']??null;
$requestedPage = $_GET['page'] ?? 'scanner';
$page = $user ? (is_string($requestedPage) ? $requestedPage : '') : ($requestedPage === 'register' ? 'register' : 'login');
$adminTabs = ['overview'=>'Thống kê', 'settings'=>'Cài đặt', 'signatures'=>'Chữ ký'];
$selectedTab = $_GET['tab'] ?? 'overview';
$adminTab = is_string($selectedTab) && isset($adminTabs[$selectedTab]) ? $selectedTab : 'overview';
$settings = ['site_name'=>'MalwareGuard', 'registration_open'=>false];
$locale = 'vi';
$choice = '';
$error = null;
$notice = $_SESSION['notice'] ?? null;
unset($_SESSION['notice']);
try {
    $settings = readSiteSettings();
    [$locale, $choice] = selectLanguage($settings, $user);
    if (!$user && $page === 'register' && !$settings['registration_open']) {
        $page = 'login';
        throw new InputError('Website đang tạm đóng đăng ký.');
    }
    if ($_SERVER['REQUEST_METHOD']==='POST') {
        if (!hash_equals($_SESSION['csrf'],(string)($_POST['csrf']??''))) throw new InputError('Phiên gửi biểu mẫu đã hết hạn. Tải lại trang.');
        $action = $_POST['action']??'';
        if ($action === 'set_language') {
            saveLanguage($user, $_POST['locale'] ?? null);
            go('page='.($user ? 'account' : ($page === 'register' ? 'register' : 'login')));
        }
        if (!$user) {
            if ($action === 'register') {
                if (!$settings['registration_open']) throw new InputError('Website đang tạm đóng đăng ký.');
                registerAccount($_POST);
                $_SESSION['notice'] = 'Tài khoản đã tạo. Bạn có thể đăng nhập.';
                go('page=login');
            }
            if ($action!=='login') throw new InputError('Đăng nhập để tiếp tục.');
            $row = query('SELECT id,email,role,password_hash FROM users WHERE email=?',[trim($_POST['email']??'')])->fetch();
            if (!$row || !password_verify($_POST['password']??'',$row['password_hash'])) throw new InputError('Email hoặc mật khẩu chưa đúng.');
            unset($row['password_hash']);
            session_regenerate_id(true);
            $_SESSION = ['user'=>$row,'csrf'=>bin2hex(random_bytes(32))];
            go('page=scanner');
        }
        if ($action==='logout') { $_SESSION=[]; session_destroy(); go(''); }
        if ($action === 'change_password') {
            changePassword((int)$user['id'], $_POST);
            $_SESSION['notice'] = 'Đã đổi mật khẩu.';
            go('page=account');
        }
        if ($action==='scan') go('page=report&id='.saveUpload((int)$user['id']));
        if ($action==='lookup') {
            $scan = ownScan((int)($_POST['id']??0),(int)$user['id']);
            $vt = vtLookup($scan['sha256']);
            query('UPDATE scans SET vt_report=? WHERE id=? AND user_id=?',[jsonData($vt),$scan['id'],$user['id']]);
            go('page=report&id='.$scan['id']);
        }
        if ($user['role']!=='admin') { http_response_code(403); throw new InputError('Chức năng dành cho quản trị viên.'); }
        if ($action === 'save_site') {
            saveSiteSettings($_POST);
            $_SESSION['notice'] = 'Đã lưu cài đặt.';
            go('page=admin&tab=settings');
        }
        if (in_array($action,['rule_add','rule_toggle'],true)) changeRule($action);
        elseif ($action==='vt_toggle') query('UPDATE settings SET vt_enabled=? WHERE id=1',[(int)(($_POST['enabled']??'')==='1')]);
        else throw new InputError('Thao tác không hợp lệ.');
        go('page=admin&tab=signatures');
    }
    if ($user) {
        if ($page==='admin' && $user['role']!=='admin') { http_response_code(403); throw new InputError('Chức năng dành cho quản trị viên.'); }
        if ($page==='report' || $page==='export') {
            $scan = ownScan((int)($_GET['id']??0),(int)$user['id']);
            $report = json_decode($scan['report'],true,512,JSON_THROW_ON_ERROR);
            $vt = json_decode($scan['vt_report']??'null',true);
            if ($page==='export') {
                header('Content-Type: application/json; charset=utf-8');
                header('Content-Disposition: attachment; filename="malwareguard-'.$scan['id'].'.json"');
                echo jsonData(['file'=>$scan['filename'],'created_at'=>$scan['created_at'],'internal'=>$report,'virustotal'=>$vt]);
                exit;
            }
        } elseif (in_array($page,['scanner','dashboard','history'],true)) {
            $p = $page==='history' ? max(1,min(10000000,(int)($_GET['p']??1))) : 1;
            $limit = match ($page) { 'scanner'=>3, 'dashboard'=>5, default=>20 };
            $offset = ($p-1)*$limit;
            $rows = query("SELECT id,filename,status,created_at,JSON_UNQUOTE(JSON_EXTRACT(report,'$.size')) AS bytes
                FROM scans WHERE user_id=? ORDER BY id DESC LIMIT $limit OFFSET $offset",[$user['id']])->fetchAll();
            if ($page!=='scanner') {
                $totals = query("SELECT COUNT(*) total, COALESCE(SUM(status='matched'),0) matched,
                    COALESCE(SUM(status='partial'),0) partial FROM scans WHERE user_id=?",[$user['id']])->fetch();
            }
        } elseif ($page==='admin' && $adminTab==='overview') {
            [$counts, $days] = siteStatistics();
        } elseif ($page==='admin' && $adminTab==='signatures') {
            $rules = query('SELECT * FROM signatures ORDER BY id DESC')->fetchAll();
            $enabled = query('SELECT vt_enabled FROM settings WHERE id=1')->fetchColumn();
        } elseif (!in_array($page,['account','admin'],true)) { http_response_code(404); throw new InputError('Trang không tồn tại.'); }
    }
} catch (InputError $e) {
    $error = $e->getMessage();
    if (http_response_code()<400) http_response_code(422);
} catch (Throwable $e) {
    http_response_code(503);
    $error = 'Không thể hoàn tất yêu cầu. Kiểm tra cấu hình PHP, cơ sở dữ liệu hoặc dịch vụ bên ngoài.';
}
require dirname(__DIR__).'/Views/layout.php';
