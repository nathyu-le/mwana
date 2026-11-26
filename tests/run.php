<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
require dirname(__DIR__).'/app/Analysis/Scanner.php';
require dirname(__DIR__).'/app/VirusTotal.php';
function check(bool $ok,string $name): void {
    if (!$ok) throw new RuntimeException($name);
    echo "PASS $name\n";
}
$bytes = "ab\0cd";
$fixture = str_repeat("\xff",4094).$bytes;
$rules = [
    ['id'=>1,'name'=>'bytes','source'=>'fixture','version'=>'1','kind'=>'bytes','pattern'=>bin2hex($bytes)],
    ['id'=>2,'name'=>'sha','source'=>'fixture','version'=>'1','kind'=>'sha256','pattern'=>hash('sha256',$fixture)],
];
$r = analyze($fixture,$rules);
check($r['status']==='matched' && count($r['findings'])===2 && $r['findings'][0]['offset']===4094,'offset across 4096, NUL and SHA-256');
check($r['hashes']['md5']===md5($fixture) && analyze('abc',$rules)['status']==='unmatched','MD5 and negative sample');
check(firstMatch('aaaabab','aaab')===1 && firstMatch('abc','abcd')===null,'overlapping prefixes and shorter file');
try { normalizePattern('bytes','ZZZZZZZZ'); throw new RuntimeException('invalid hex accepted'); }
catch (InputError) { check(true,'invalid hex rejected'); }
check(analyze("MZ\0",[])['status']==='partial','truncated PE');
foreach ([0x10b,0x20b] as $magic) {
    $pe = str_repeat("\0",512);
    $put = static function(int $at,string $bytes) use (&$pe) { $pe=substr_replace($pe,$bytes,$at,strlen($bytes)); };
    $put(0,'MZ'); $put(60,pack('V',64)); $put(64,"PE\0\0"); $put(68,pack('vv',0x8664,1));
    $put(84,pack('v',160)); $put(88,pack('v',$magic)); $put(104,pack('V',4096));
    $put(248,'.text'); $put(264,pack('VV',4,500));
    $parsed=inspectPe($pe);
    check($parsed['entry_point']===4096 && $parsed['sections'][0]['raw_offset']===500,"PE $magic fields");
    $put(268,pack('V',511)); check(inspectPe($pe)['state']==='malformed','bounds check '.$magic);
}
$ev = extractEvidence("aaa\x00\xff".implode("\0",str_split('demo.example'))."\0\xffhttps://example.org/x 192.0.2.1");
$byValue = array_column($ev['indicators'],null,'value');
check($byValue['demo.example']['offset']===5 && $byValue['demo.example']['encoding']==='UTF-16LE','UTF-16LE domain byte offset');
check($byValue['192.0.2.1']['type']==='IPv4' && $byValue['https://example.org/x']['type']==='URL','ASCII URL and IPv4');
$hash=$r['hashes']['sha256'];
foreach ([401=>'denied',403=>'denied',404=>'not_found',429=>'rate_limit',503=>'unavailable',0=>'unavailable'] as $code=>$expected)
    check(vtDecode($code,'',$hash)['state']===$expected,"VT $code");
$api = jsonData(['data'=>['id'=>$hash,'type'=>'file','attributes'=>['last_analysis_stats'=>['malicious'=>0]]]]);
$info = vtDecode(200,$api,$hash);
check($info['stats']['malicious']===0 && $info['stats']['harmless']===null,'missing and zero VT counters');
check(vtDecode(200,$api,str_repeat('a',64))['state']==='unavailable' && vtDecode(200,'?',$hash)['state']==='unavailable','reject wrong hash or invalid JSON');
