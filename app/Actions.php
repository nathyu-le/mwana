<?php
declare(strict_types=1);
function saveHashScan(int $user, array $input): int
{
    $hash = $input['sha256'] ?? null;
    $name = $input['filename'] ?? null;
    $size = filter_var($input['size'] ?? null, FILTER_VALIDATE_INT);
    if (!is_string($hash) || !preg_match('/^[a-f0-9]{64}$/D', $hash) ||
        !is_string($name) || !preg_match('/^[^[:cntrl:]]{1,220}$/uD', $name)) {
        throw new InputError('Tên tệp hoặc SHA-256 không hợp lệ.');
    }
    if (!$size || $size < 1 || $size > 2097152) throw new InputError('Chọn một tệp từ 1 byte đến 2 MiB.');
    $vt = vtLookup($hash);
    query('INSERT INTO scans(user_id,filename,sha256,status,report,vt_report) VALUES(?,?,?,?,?,?)',
        [$user, $name, $hash, vtStatus($vt), jsonData(['size'=>$size,'source'=>'VirusTotal']), jsonData($vt)]);
    return (int)db()->lastInsertId();
}
function ownScan(int $id,int $user): array {
    $scan = query('SELECT * FROM scans WHERE id=? AND user_id=?',[$id,$user])->fetch();
    if (!$scan) { http_response_code(404); throw new InputError('Không tìm thấy báo cáo.'); }
    return $scan;
}
