<?php
declare(strict_types=1);

function vtDecode(int $http, string $body, string $hash): array {
    $result = ['source'=>'VirusTotal','queried_at'=>gmdate('c')];
    if ($http !== 200) {
        $result['state'] = match($http) {
            404=>'not_found',401,403=>'denied',429=>'rate_limit',default=>'unavailable',
        };
        return $result;
    }
    $file = json_decode($body,true)['data'] ?? null;
    if (!is_array($file) || ($file['type']??null)!=='file' || ($file['id']??null)!==$hash
        || ($file['attributes']['sha256']??$hash)!==$hash || !is_array($file['attributes']??null)) {
        return $result+['state'=>'unavailable'];
    }
    $attr = $file['attributes'];
    $stats = [];
    foreach (['malicious','suspicious','undetected','harmless','timeout','failure','type-unsupported','confirmed-timeout'] as $key) {
        $n = $attr['last_analysis_stats'][$key] ?? null;
        $stats[$key] = is_int($n) && $n>=0 ? $n : null;
    }
    $engines = [];
    foreach (array_slice((array)($attr['last_analysis_results']??[]),0,150,true) as $name=>$engine) {
        if (is_array($engine)) $engines[substr((string)$name,0,120)] = [
            'category'=>substr((string)($engine['category']??''),0,48),'result'=>substr((string)($engine['result']??''),0,160)
        ];
    }
    $time = $attr['last_analysis_date']??null;
    return $result+['state'=>'available','stats'=>$stats,'engines'=>$engines,'analysis_at'=>is_int($time)?gmdate('c',$time):null];
}

function vtLookup(string $hash): array {
    if (!preg_match('/^[0-9a-f]{64}$/D',$hash)) throw new InvalidArgumentException('Invalid SHA-256');
    $key = config()['vt_key']??'';
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $settings = query('SELECT vt_enabled,vt_next FROM settings WHERE id=1 FOR UPDATE')->fetch();
        $now = time();
        $cached = query('SELECT body FROM vt_cache WHERE sha256=? AND expires_at>?',[$hash,$now])->fetchColumn();
        if (!$settings['vt_enabled']) $local = ['state'=>'disabled'];
        elseif (!$key) $local = ['state'=>'missing_key'];
        elseif ($cached!==false) $local = json_decode($cached,true,512,JSON_THROW_ON_ERROR)+['cached'=>true];
        elseif ($settings['vt_next']>$now) $local = ['state'=>'rate_limit','retry_at'=>gmdate('c',(int)$settings['vt_next'])];
        else query('UPDATE settings SET vt_next=? WHERE id=1',[$now+16]);
        $pdo->commit();
    } catch (Throwable $e) { $pdo->rollBack(); throw $e; }
    if (isset($local)) return $local+['source'=>'VirusTotal'];
    $body = '';
    $retry = 60;
    $c = curl_init('https://www.virustotal.com/api/v3/files/'.$hash);
    curl_setopt_array($c,[
        CURLOPT_HTTPHEADER=>['x-apikey: '.$key,'Accept: application/json'],
        CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>3,CURLOPT_TIMEOUT=>8,
        CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,
        CURLOPT_WRITEFUNCTION=>static function($ch,$chunk) use (&$body) {
            if (strlen($chunk)+strlen($body)>1048576) return 0;
            $body .= $chunk;
            return strlen($chunk);
        },
        CURLOPT_HEADERFUNCTION=>static function($ch,$line) use (&$retry) {
            if (stripos($line,'Retry-After:')===0) {
                $v = trim(substr($line,12));
                $retry = max(16,ctype_digit($v)?min(604800,(int)$v):min(604800,(strtotime($v)?:time()+60)-time()));
            }
            return strlen($line);
        },
    ]);
    $ok = curl_exec($c);
    $http = $ok===false ? 0 : (int)curl_getinfo($c,CURLINFO_RESPONSE_CODE);
    $report = vtDecode($http,$body,$hash);
    if ($http===429) {
        $until = time()+$retry;
        query('UPDATE settings SET vt_next=GREATEST(vt_next,?) WHERE id=1',[$until]);
        $report['retry_at'] = gmdate('c',$until);
    }
    if (in_array($report['state'],['available','not_found'],true)) {
        $ttl = $report['state']==='available'?3600:300;
        query('INSERT INTO vt_cache(sha256,body,expires_at) VALUES(?,?,?) ON DUPLICATE KEY UPDATE body=VALUES(body),expires_at=VALUES(expires_at)',
            [$hash,jsonData($report),time()+$ttl]);
    }
    return $report;
}
