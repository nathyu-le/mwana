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
$stats = ['malicious'=>12,'suspicious'=>2,'harmless'=>6,'undetected'=>20,'timeout'=>20];
$attr = ['last_analysis_stats'=>$stats,'threat_verdict'=>'VERDICT_MALICIOUS','threat_severity'=>[
    'threat_severity_level'=>'SEVERITY_HIGH','threat_severity_data'=>[
        'has_dropped_files_with_detections'=>true,'has_contacted_domains_with_detections'=>false,
    ],
]];
$report = vtDecode(200,jsonData(['data'=>['id'=>$hash,'type'=>'file','attributes'=>$attr]]),$hash);
$a = assessVirusTotal($report);
check($a['answered'] === 40 && $a['detection_score'] === 30 && $a['suspicious_percent'] === 5.0, 'separate detection and suspicion, exclude timeouts');
check($a['severity'] === 'Cao' && count($a['checks']) === 2 && $a['headline'] === VT_VERDICTS['VERDICT_MALICIOUS'], 'source verdict, severity and evidence-specific checks');
foreach ([[], ['undetected'=>4], ['malicious'=>0,'suspicious'=>0,'undetected'=>0,'harmless'=>0,'failure'=>19]] as $partial) {
    check(assessVirusTotal(['state'=>'available','stats'=>$partial])['detection_score'] === null,'insufficient coverage is unscored');
}
check(assessVirusTotal(['state'=>'not_found'])['severity'] === 'Chưa xác định','unknown file has no severity');
check(assessVirusTotal(['stats'=>['malicious'=>0,'suspicious'=>0,'harmless'=>0,'undetected'=>40]])['detection_score'] === 0,'genuine zero is preserved');
