<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__).'/app/bootstrap.php';
[$script,$email,$role] = array_pad($argv,3,null);
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email)>190 || !in_array($role,['admin','analyst'],true)) {
    exit("Usage: php bin/user.php analyst@example.com admin|analyst\n");
}
fwrite(STDERR,"Password (12+ characters): ");
$tty = stream_isatty(STDIN);
if ($tty) system('stty -echo');
try { $password = rtrim((string)fgets(STDIN), "\r\n"); }
finally { if ($tty) system('stty echo'); fwrite(STDERR,"\n"); }
if (strlen($password)<12 || strlen($password)>72) exit("Use 12 to 72 bytes.\n");
query('INSERT INTO users(email,password_hash,role) VALUES(?,?,?)',[$email,password_hash($password,PASSWORD_DEFAULT),$role]);
echo "Account created.\n";
