<?php
declare(strict_types=1);

function readSiteSettings(): array
{
    return query('SELECT p.*, l.locale AS default_locale FROM site_preferences p JOIN site_language l ON l.id=p.id WHERE p.id=1')->fetch() ?: throw new RuntimeException('Site settings missing');
}

function saveSiteSettings(array $input): void
{
    $name = trim(is_string($input['site_name'] ?? null) ? $input['site_name'] : '');
    if ($name === '' || !preg_match('/^.{1,80}$/usD', $name)) throw new InputError('Tên website phải dài từ 1 đến 80 ký tự.');
    $language = $input['default_locale'] ?? '';
    if (!in_array($language, ['vi','en'], true)) throw new InputError('Ngôn ngữ không hợp lệ.');
    db()->beginTransaction();
    try {
        query('UPDATE site_preferences SET site_name=?, registration_open=? WHERE id=1',
        [$name, (int)(($input['registration_open'] ?? '') === '1')]);
        query('UPDATE site_language SET locale=? WHERE id=1', [$language]);
        db()->commit();
    } catch (Throwable $error) {
        db()->rollBack();
        throw $error;
    }
}
