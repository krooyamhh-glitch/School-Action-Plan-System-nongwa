<?php
function set_perms($path) {
    if (is_dir($path)) {
        chmod($path, 0777);
        echo "Set 0777 for directory: $path\n";
        $files = glob($path . '/*');
        foreach ($files as $file) {
            set_perms($file);
        }
    } else {
        chmod($path, 0666);
        echo "Set 0666 for file: $path\n";
    }
}

$root_uploads = __DIR__ . '/uploads';
if (is_dir($root_uploads)) {
    set_perms($root_uploads);
} else {
    mkdir($root_uploads, 0777, true);
    echo "Created uploads directory with 0777\n";
}
?>
