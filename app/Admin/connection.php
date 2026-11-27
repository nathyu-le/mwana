<?php
declare(strict_types=1);

function vtConnectionIndicator(string $key, ?array $connection, int $now): array
{
    if ($key === '') return ['red','Tắt · chưa có API key',null];
    if (!$connection || !hash_equals($connection['key_fingerprint'], hash('sha256', $key))) {
        return ['amber','Đã cấu hình · chưa kiểm tra',null];
    }
    $time = (int)$connection['observed_at'];
    if ($time < $now - 3600) return ['amber','Cần kiểm tra lại',$time];
    [$color, $label] = match ($connection['state']) {
        'available', 'not_found' => ['green','Đang hoạt động'],
        'denied' => ['red','API từ chối truy cập'],
        'rate_limit' => ['amber','Đang chờ hạn mức'],
        default => ['red','Không kết nối được'],
    };
    return [$color, $label, $time];
}
