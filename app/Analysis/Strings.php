<?php
declare(strict_types=1);
function extractEvidence(string $data): array {
    $strings = $indicators = [];
    $limited = false;
    $iocRegex = '~https?://[^\s<>"\']{1,500}|(?<![a-z0-9_.-])(?:[0-9]{1,3}\.){3}[0-9]{1,3}(?![a-z0-9_.-])|(?<![a-z0-9_.-])(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}(?![a-z0-9_.-])~i';
    foreach (['ASCII'=>'~[\x20-\x7e]{4,1024}~','UTF-16LE'=>'~(?:[\x20-\x7e]\x00){4,1024}~'] as $encoding=>$regex) {
        $pos = 0;
        $wide = $encoding === 'UTF-16LE';
        while (preg_match($regex,$data,$matches,PREG_OFFSET_CAPTURE,$pos)) {
            [$raw,$start] = $matches[0];
            $pos = $start+strlen($raw);
            $s = $wide ? str_replace("\0",'',$raw) : $raw;
            if (count($strings) >= 128) { $limited = true; break; }
            $strings[] = ['offset'=>$start,'encoding'=>$encoding,'value'=>substr($s,0,256)];
            if (strlen($s)>256) $limited = true;
            preg_match_all($iocRegex,$s,$iocs,PREG_OFFSET_CAPTURE);
            foreach ($iocs[0] as [$v,$i]) {
                if (count($indicators)>=128) { $limited=true; break; }
                if (stripos($v,'http') === 0 && str_contains($v,'://')) $type = 'URL';
                elseif (preg_match('/^[0-9.]+$/D',$v)) {
                    if (!filter_var($v,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)) continue;
                    $type = 'IPv4';
                } else $type = 'Domain';
                $indicators[] = ['type'=>$type,'value'=>$v,'offset'=>$start+$i*($wide?2:1),'encoding'=>$encoding];
            }
        }
    }
    return ['strings'=>$strings,'indicators'=>$indicators,'limited'=>$limited];
}
