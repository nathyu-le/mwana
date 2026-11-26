<?php
declare(strict_types=1);
require_once __DIR__.'/Pe.php';
require_once __DIR__.'/Strings.php';

const FILE_LIMIT = 2097152;

function normalizePattern(string $kind, string $value): string {
    $value = strtolower(trim($value));
    $valid = match ($kind) {
        'md5' => strlen($value) === 32,
        'sha256' => strlen($value) === 64,
        'bytes' => strlen($value) >= 8 && strlen($value) <= 512 && strlen($value)%2 === 0,
        default => false,
    };
    if (!$valid || !ctype_xdigit($value)) throw new InputError('Mẫu hex hoặc độ dài chữ ký không hợp lệ.');
    return $value;
}

// Exact KMP search. Skip the unmatched prefix in native code.
function firstMatch(string $data, string $pattern): ?int {
    $size = strlen($pattern);
    if ($size === 0) throw new InvalidArgumentException('Empty pattern');
    $prefix = array_fill(0,$size,0);
    for ($i=1,$j=0; $i<$size; $i++) {
        while ($j && $pattern[$i] !== $pattern[$j]) $j = $prefix[$j-1];
        if ($pattern[$i] === $pattern[$j]) $j++;
        $prefix[$i] = $j;
    }
    $length = strlen($data);
    for ($i=0,$j=0; $i<$length; $i++) {
        if ($j === 0) {
            $start = strpos($data,$pattern[0],$i);
            if ($start === false) return null;
            $i = $start;
        }
        while ($j && $data[$i] !== $pattern[$j]) $j = $prefix[$j-1];
        if ($data[$i] === $pattern[$j]) $j++;
        if ($j === $size) return $i-$size+1;
    }
    return null;
}

function analyze(string $data, array $rules): array {
    if (strlen($data) > FILE_LIMIT || $data === '') throw new InputError('Chọn file từ 1 byte đến 2 MiB.');
    $begin = hrtime(true);
    $hashes = ['md5'=>md5($data),'sha256'=>hash('sha256',$data)];
    $findings = [];
    foreach ($rules as $rule) {
        $kind = $rule['kind'];
        $pattern = normalizePattern($kind,$rule['pattern']);
        $offset = null;
        if ($kind === 'bytes') {
            $offset = firstMatch($data,hex2bin($pattern));
            if ($offset === null) continue;
        } elseif (!hash_equals($pattern,$hashes[$kind])) continue;
        $findings[] = array_intersect_key($rule,array_flip(['id','name','source','version','kind','pattern'])) + ['offset'=>$offset];
    }
    $pe = inspectPe($data);
    $strings = extractEvidence($data);
    $partial = $pe['state'] === 'malformed' || $strings['limited'];
    $status = $findings ? 'matched' : ($partial ? 'partial' : 'unmatched');
    return ['hashes'=>$hashes,'size'=>strlen($data),'mime'=>(new finfo(FILEINFO_MIME_TYPE))->buffer($data),
        'status'=>$status,'rules'=>count($rules),'findings'=>$findings,'pe'=>$pe,'evidence'=>$strings,
        'elapsed_ms'=>round((hrtime(true)-$begin)/1e6,2)];
}
