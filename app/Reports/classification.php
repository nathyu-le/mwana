<?php
declare(strict_types=1);

function threatNames(mixed $entries): array
{
    $names = [];
    foreach (is_array($entries) ? $entries : [] as $entry) {
        $value = $entry['value'] ?? null;
        $count = $entry['count'] ?? null;
        if (is_string($value) && trim($value) !== '' && strlen($value) <= 160 && is_int($count) && $count > 0) {
            $names[$value] = max($count, $names[$value] ?? 0);
        }
    }
    arsort($names, SORT_NUMERIC);
    return array_keys(array_slice($names, 0, 4, true));
}

function vtClassification(array $attributes, array $engines): array
{
    $popular = $attributes['popular_threat_classification'] ?? [];
    $examples = [];
    foreach ($engines as $vendor => $entry) {
        if (!in_array($entry['category'], ['malicious', 'suspicious'], true)) continue;
        if (preg_match('/(?<![a-z])(keylog(?:ger|ging)?|botnet|ransomware|spyware|backdoor|infostealer)(?![a-z])/i', $entry['result'], $match)) {
            $examples[] = ['name'=>$vendor, 'category'=>$match[1], 'result'=>$entry['result']];
            if (count($examples) === 5) break;
        }
    }
    return [
        'categories'=>threatNames($popular['popular_threat_category'] ?? []),
        'families'=>threatNames($popular['popular_threat_name'] ?? []),
        'vendor_labels'=>$examples,
    ];
}
