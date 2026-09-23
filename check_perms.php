<?php
$dir = __DIR__ . '/uploads/logos';
$results = [
    'dir_exists' => is_dir($dir),
    'is_writable' => is_writable($dir),
    'perms' => substr(sprintf('%o', fileperms($dir)), -4),
    'owner' => posix_getpwuid(fileowner($dir))['name'],
    'user' => posix_getpwuid(posix_geteuid())['name']
];
file_put_contents(__DIR__ . '/debug_perms.json', json_encode($results, JSON_PRETTY_PRINT));
?>
