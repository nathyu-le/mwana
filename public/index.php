<?php
declare(strict_types=1);

$project = dirname(__DIR__);
if (!is_file($project.'/app/Http/workspace.php')) {
    // cPanel keeps the real document root alongside the private project.
    $project = dirname(__DIR__, 2).'/malwareguard_app';
}
require $project.'/app/Http/workspace.php';
