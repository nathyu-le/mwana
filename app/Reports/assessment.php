<?php
declare(strict_types=1);

const VT_VERDICTS = [
    'VERDICT_MALICIOUS'=>'VirusTotal đánh giá là độc hại',
    'VERDICT_SUSPICIOUS'=>'VirusTotal đánh giá là đáng ngờ',
    'VERDICT_UNDETECTED'=>'VirusTotal chưa phát hiện dấu hiệu độc hại',
];
const VT_SEVERITIES = [
    'SEVERITY_HIGH'=>'Cao', 'SEVERITY_MEDIUM'=>'Trung bình', 'SEVERITY_LOW'=>'Thấp',
    'SEVERITY_NONE'=>'Không ghi nhận mức nguy hiểm',
];
const VT_CHECKS = [
    'has_contacted_domains_with_detections'=>'Đối chiếu tên miền bị cảnh báo trong mục Relations của VirusTotal.',
    'has_contacted_ips_with_detections'=>'Đối chiếu địa chỉ IP bị cảnh báo trong mục Relations của VirusTotal.',
    'has_contacted_urls_with_detections'=>'Xem URL bị cảnh báo trong mục Relations của VirusTotal.',
    'has_dropped_files_with_detections'=>'Kiểm tra mã băm và kết quả của các tệp được tạo ra trong mục Dropped Files.',
    'has_execution_parents_with_detections'=>'Kiểm tra báo cáo của tệp đã khởi chạy mẫu trong mục Execution Parents.',
];

function vtSignals(array $attr): array
{
    $verdict = $attr['threat_verdict'] ?? null;
    $severity = $attr['threat_severity']['threat_severity_level'] ?? null;
    $checks = [];
    foreach (VT_CHECKS as $flag => $description) {
        if (($attr['threat_severity']['threat_severity_data'][$flag] ?? null) === true) $checks[] = $flag;
    }
    return [
        'verdict'=>is_string($verdict) && isset(VT_VERDICTS[$verdict]) ? $verdict : null,
        'severity'=>is_string($severity) && isset(VT_SEVERITIES[$severity]) ? $severity : null,
        'checks'=>$checks,
    ];
}

function assessVirusTotal(?array $vt): array
{
    $stats = $vt['stats'] ?? [];
    $counts = [];
    foreach (['malicious','suspicious','harmless','undetected'] as $category) {
        $value = $stats[$category] ?? null;
        $counts[$category] = is_int($value) && $value >= 0 ? $value : null;
    }
    $answered = in_array(null, $counts, true) ? null : array_sum($counts);
    $signals = $vt['signals'] ?? [];
    $verdict = $signals['verdict'] ?? null;
    $headline = VT_VERDICTS[$verdict] ?? match (true) {
        $counts['malicious'] > 0 => 'Có công cụ báo độc hại',
        $counts['suspicious'] > 0 => 'Có công cụ báo đáng ngờ',
        $answered > 0 => 'Chưa có công cụ nào cảnh báo',
        default => 'Chưa đủ dữ liệu để đánh giá',
    };
    $checks = [];
    if ($verdict === 'VERDICT_MALICIOUS' || $counts['malicious'] > 0 || $counts['suspicious'] > 0 || $verdict === 'VERDICT_SUSPICIOUS') {
        $checks[] = 'Tạm ngừng mở tệp. Xem tên phát hiện của từng công cụ và đối chiếu với nguồn tải.';
    } elseif (!$answered) {
        $checks[] = 'Kiểm tra nguồn tệp và mã SHA-256 do nhà phát hành công bố trước khi mở.';
    } else {
        $checks[] = 'Kiểm tra ngày phân tích và đối chiếu SHA-256 với bản phát hành chính thức.';
    }
    foreach ($signals['checks'] ?? [] as $flag) {
        if (isset(VT_CHECKS[$flag])) $checks[] = VT_CHECKS[$flag];
    }
    return [
        'headline'=>$headline, 'severity'=>VT_SEVERITIES[$signals['severity'] ?? ''] ?? 'Chưa xác định',
        'answered'=>$answered, 'malicious'=>$counts['malicious'], 'suspicious'=>$counts['suspicious'],
        'detection_score'=>$answered ? (int)round(100 * $counts['malicious'] / $answered) : null,
        'suspicious_percent'=>$answered ? round(100 * $counts['suspicious'] / $answered, 1) : null,
        'checks'=>$checks,
    ];
}
