<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
require dirname(__DIR__).'/app/VirusTotal.php';
function check(bool $ok,string $name): void {
    if (!$ok) throw new RuntimeException($name);
    echo "PASS $name\n";
}
$hash=hash('sha256','demo');
foreach ([401=>'denied',403=>'denied',404=>'not_found',429=>'rate_limit',503=>'unavailable',0=>'unavailable'] as $code=>$expected)
    check(vtDecode($code,'',$hash)['state']===$expected,"VT $code");
$api = jsonData(['data'=>['id'=>$hash,'type'=>'file','attributes'=>['last_analysis_stats'=>['malicious'=>0]]]]);
$info = vtDecode(200,$api,$hash);
check($info['stats']['malicious']===0 && $info['stats']['harmless']===null,'missing and zero VT counters');
check(vtDecode(200,$api,str_repeat('a',64))['state']==='unavailable' && vtDecode(200,'?',$hash)['state']==='unavailable','reject wrong hash or invalid JSON');
$engines = ['Vendor'=>['category'=>'malicious','result'=>'Trojan.Win32.Keylogger.X']];
$classification = vtClassification(['popular_threat_classification'=>[
    'popular_threat_category'=>[['value'=>'trojan','count'=>5]],
    'popular_threat_name'=>[['value'=>'familyA','count'=>3],['value'=>'familyB','count'=>9]],
]], $engines);
check($classification['categories'] === ['trojan'] && $classification['families'] === ['familyB','familyA'], 'rank VirusTotal labels');
check($classification['vendor_labels'][0]['name'] === 'Vendor', 'attribute specific label to its vendor');
check(vtClassification([], []) === ['categories'=>[],'families'=>[],'vendor_labels'=>[]], 'unknown classification without evidence');
