<?php
declare(strict_types=1);
function saveUpload(int $user): int {
    $file = $_FILES['sample']??[];
    if (($file['error']??-1)!==UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        throw new InputError('Tải file thất bại. Kiểm tra giới hạn upload rồi chọn lại file.');
    }
    $tmp = $file['tmp_name'];
    try {
        $real = realpath($tmp);
        $web = realpath($_SERVER['DOCUMENT_ROOT']);
        if (!$real || !$web || str_starts_with($real,$web.DIRECTORY_SEPARATOR)) {
            throw new InputError('Hosting cần đặt upload_tmp_dir ngoài thư mục web.');
        }
        if (filesize($tmp)>FILE_LIMIT) throw new InputError('File vượt giới hạn 2 MiB.');
        $data = file_get_contents($tmp,false,null,0,FILE_LIMIT+1);
        if ($data===false) throw new InputError('Không thể đọc file tạm.');
        $report = analyze($data,query('SELECT * FROM signatures WHERE active=1 ORDER BY id')->fetchAll());
        $name = basename(str_replace('\\','/',$file['name']));
        $name = preg_replace('/[\x00-\x1F\x7F]/','',substr($name,0,200));
        $name = json_decode(json_encode($name,JSON_INVALID_UTF8_SUBSTITUTE));
        query('INSERT INTO scans(user_id,filename,sha256,status,report) VALUES(?,?,?,?,?)',
            [$user,$name?:'sample',$report['hashes']['sha256'],$report['status'],jsonData($report)]);
        return (int)db()->lastInsertId();
    } finally { if (is_file($tmp)) unlink($tmp); }
}
function ownScan(int $id,int $user): array {
    $scan = query('SELECT * FROM scans WHERE id=? AND user_id=?',[$id,$user])->fetch();
    if (!$scan) { http_response_code(404); throw new InputError('Không tìm thấy báo cáo.'); }
    return $scan;
}
function changeRule(string $action): void {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        query('SELECT id FROM settings WHERE id=1 FOR UPDATE')->fetch();
        $count = (int)query('SELECT COUNT(*) FROM signatures WHERE active=1')->fetchColumn();
        if ($action==='rule_toggle') {
            $id = (int)($_POST['id']??0);
            $active = $_POST['active'] === '1';
            if ($active && $count>=32) throw new InputError('Đã đạt giới hạn 32 chữ ký đang bật.');
            query('UPDATE signatures SET active=? WHERE id=?',[(int)$active,$id]);
        } else {
            if ($count>=32) throw new InputError('Tắt một chữ ký trước khi thêm.');
            $fields = [];
            foreach (['name'=>96,'source'=>128,'version'=>48] as $k=>$limit) {
                $fields[$k] = trim($_POST[$k]??'');
                if (!$fields[$k] || strlen($fields[$k])>$limit) throw new InputError('Kiểm tra tên, nguồn và phiên bản.');
            }
            $kind = $_POST['kind']??'';
            $pattern = normalizePattern($kind,$_POST['pattern']??'');
            query('INSERT INTO signatures(name,kind,pattern,source,version) VALUES(?,?,?,?,?)',
                [$fields['name'],$kind,$pattern,$fields['source'],$fields['version']]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        if ($e instanceof PDOException && $e->getCode()==='23000') throw new InputError('Tên và phiên bản này đã tồn tại.');
        throw $e;
    }
}
