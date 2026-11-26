<?php
declare(strict_types=1);
function inspectPe(string $data): array {
    if (!str_starts_with($data,'MZ')) return ['state'=>'other'];
    $size = strlen($data);
    $bad = ['state'=>'malformed'];
    if ($size < 64) return $bad;
    $u16 = static fn(int $at): int => unpack('v',$data,$at)[1];
    $u32 = static fn(int $at): int => unpack('V',$data,$at)[1];
    $pe = $u32(60);
    if ($pe < 64 || $pe > $size-24 || substr($data,$pe,4) !== "PE\0\0") return $bad;
    $count = $u16($pe+6);
    $optional = $pe+24;
    $length = $u16($pe+20);
    $table = $optional+$length;
    if ($count < 1 || $count > 96 || $length < 2 || $table+$count*40 > $size) return $bad;
    $magic = $u16($optional);
    if (!in_array($magic,[0x10b,0x20b],true) || $length < ($magic===0x10b ? 96 : 112)) return $bad;
    $base = $magic===0x10b ? sprintf('0x%X',$u32($optional+28))
        : sprintf('0x%X%08X',$u32($optional+28),$u32($optional+24));
    $sections = [];
    for ($i=0; $i<$count; $i++) {
        $at = $table+40*$i;
        $lengthRaw = $u32($at+16);
        $raw = $u32($at+20);
        if ($lengthRaw && ($raw > $size || $lengthRaw > $size-$raw)) return $bad;
        $sections[] = ['name'=>preg_replace('/[^\x20-\x7e]/','?',rtrim(substr($data,$at,8),"\0")),
            'rva'=>$u32($at+12),'virtual_size'=>$u32($at+8),'raw_size'=>$lengthRaw,'raw_offset'=>$raw];
    }
    return ['state'=>'valid','format'=>$magic===0x10b?'PE32':'PE32+','machine'=>sprintf('0x%04X',$u16($pe+4)),
        'characteristics'=>sprintf('0x%04X',$u16($pe+22)), 'entry_point'=>$u32($optional+16),
        'image_base'=>$base,'size_of_image'=>$u32($optional+56),'sections'=>$sections];
}
