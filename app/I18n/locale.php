<?php
declare(strict_types=1);

function t(string $message): string
{
    global $locale;
    if ($locale !== 'en') return $message;
    static $english;
    $english ??= require __DIR__.'/en.php';
    return $english[$message] ?? $message;
}

function selectLanguage(array $settings, ?array $user): array
{
    if ($user) {
        $choice = query('SELECT locale FROM user_language WHERE user_id=?', [$user['id']])->fetchColumn() ?: '';
    } else {
        $choice = $_SESSION['guest_locale'] ?? '';
    }
    $default = ($user['role'] ?? '') === 'admin' ? 'vi' : $settings['default_locale'];
    return [$choice ?: $default, $choice];
}

function saveLanguage(?array $user, mixed $choice): void
{
    if (!in_array($choice, ['', 'vi', 'en'], true)) throw new InputError('Ngôn ngữ không hợp lệ.');
    if (!$user) {
        $_SESSION['guest_locale'] = $choice;
    } elseif ($choice === '') {
        query('DELETE FROM user_language WHERE user_id=?', [$user['id']]);
    } else {
        query('INSERT INTO user_language(user_id,locale) VALUES(?,?) ON DUPLICATE KEY UPDATE locale=?',
            [$user['id'], $choice, $choice]);
    }
}
