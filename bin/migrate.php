<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit(1);
require dirname(__DIR__).'/app/bootstrap.php';
foreach (glob(dirname(__DIR__).'/database/migrations/*.sql') as $path) {
    db()->exec(file_get_contents($path));
    echo basename($path), PHP_EOL;
}
