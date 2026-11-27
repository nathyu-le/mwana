<?php
declare(strict_types=1);

function readSiteSettings(): array
{
    return query('SELECT * FROM site_preferences WHERE id=1')->fetch() ?: throw new RuntimeException('Site settings missing');
}

function saveSiteSettings(array $input): void
{
    $name = trim(is_string($input['site_name'] ?? null) ? $input['site_name'] : '');
    if ($name === '' || !preg_match('/^.{1,80}$/usD', $name)) throw new InputError('Tên website phải dài từ 1 đến 80 ký tự.');
    query('UPDATE site_preferences SET site_name=?, registration_open=? WHERE id=1',
        [$name, (int)(($input['registration_open'] ?? '') === '1')]);
}
