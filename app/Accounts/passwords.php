<?php
declare(strict_types=1);

function newPassword(array $input): string
{
    $password = $input['password'] ?? '';
    if (!is_string($password) || strlen($password) < 12 || strlen($password) > 72 || str_contains($password, "\0")) {
        throw new InputError('Mật khẩu phải dài từ 12 đến 72 byte.');
    }
    if ($password !== ($input['confirmation'] ?? null)) {
        throw new InputError('Hai mật khẩu chưa khớp.');
    }
    return $password;
}

function registerAccount(array $input): void
{
    $email = trim(is_string($input['email'] ?? null) ? $input['email'] : '');
    if (strlen($email) > 190 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InputError('Email không hợp lệ.');
    }
    $hash = password_hash(newPassword($input), PASSWORD_DEFAULT);
    try {
        query("INSERT INTO users(email,password_hash,role) VALUES(?,?,'analyst')", [$email, $hash]);
    } catch (PDOException $exception) {
        if ((int)($exception->errorInfo[1] ?? 0) === 1062) {
            throw new InputError('Email này đã được đăng ký.');
        }
        throw $exception;
    }
}
