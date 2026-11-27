<?php
declare(strict_types=1);

function siteStatistics(): array
{
    $counts = query("SELECT COUNT(*) AS scans, COALESCE(SUM(status='matched'),0) AS matched,
        COALESCE(SUM(status='partial'),0) AS partial, COUNT(DISTINCT user_id) AS active_users FROM scans")->fetch();
    $counts['users'] = query('SELECT COUNT(*) FROM users')->fetchColumn();
    $counts['rules'] = query('SELECT COUNT(*) FROM signatures WHERE active=1')->fetchColumn();
    $daily = query("SELECT DATE(created_at) AS day, COUNT(*) AS scans, SUM(status='matched') AS matched
        FROM scans WHERE created_at >= CURRENT_DATE - INTERVAL 6 DAY GROUP BY DATE(created_at)")->fetchAll(PDO::FETCH_UNIQUE);
    $today = new DateTimeImmutable(query('SELECT CURRENT_DATE')->fetchColumn());
    $days = [];
    for ($i = 6; $i >= 0; $i--) {
        $day = $today->modify("-$i days")->format('Y-m-d');
        $days[$day] = $daily[$day] ?? ['scans'=>0, 'matched'=>0];
    }
    return [$counts, $days];
}
