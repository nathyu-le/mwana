<?php
declare(strict_types=1);

function readSiteSettings(): array
{
    return query('SELECT p.*, l.locale AS default_locale, COALESCE(a.reload_seconds, 2.0) AS reload_seconds
        FROM site_preferences p JOIN site_language l ON l.id=p.id
        LEFT JOIN site_appearance a ON a.id=p.id WHERE p.id=1')->fetch() ?: throw new RuntimeException('Site settings missing');
}

function saveSiteSettings(array $input): void
{
    $name = trim(is_string($input['site_name'] ?? null) ? $input['site_name'] : '');
    if ($name === '' || !preg_match('/^.{1,80}$/usD', $name)) throw new InputError('Tên website phải dài từ 1 đến 80 ký tự.');
    $language = $input['default_locale'] ?? '';
    if (!in_array($language, ['vi','en'], true)) throw new InputError('Ngôn ngữ không hợp lệ.');
    $seconds = $input['reload_seconds'] ?? null;
    if (array_key_exists('reload_seconds', $input) && (!is_string($seconds) ||
        !preg_match('/^[0-9]{1,2}(?:\.[0-9])?$/D', $seconds) || (float)$seconds > 30)) {
        throw new InputError('Nhập từ 0 đến 30 giây, tối đa một chữ số thập phân.');
    }
    db()->beginTransaction();
    try {
        query('UPDATE site_preferences SET site_name=?, registration_open=? WHERE id=1',
        [$name, (int)(($input['registration_open'] ?? '') === '1')]);
        query('UPDATE site_language SET locale=? WHERE id=1', [$language]);
        if ($seconds !== null) {
            query('INSERT INTO site_appearance (id,reload_seconds) VALUES (1,?)
                ON DUPLICATE KEY UPDATE reload_seconds=?', [$seconds, $seconds]);
        }
        db()->commit();
    } catch (Throwable $error) {
        db()->rollBack();
        throw $error;
    }
}
