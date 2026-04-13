<?php
// Fix upload_tmp_dir for Apache module PHP
putenv('TMP=C:/temp_uploads');
putenv('TEMP=C:/temp_uploads');
putenv('TMPDIR=C:/temp_uploads');
ini_set('upload_tmp_dir', 'C:/temp_uploads');
ini_set('sys_temp_dir', 'C:/temp_uploads');

// Diagnose
echo "Loaded INI: " . php_ini_loaded_file() . PHP_EOL;
echo "upload_tmp_dir: " . ini_get('upload_tmp_dir') . PHP_EOL;
echo "sys_get_temp_dir(): " . sys_get_temp_dir() . PHP_EOL;
echo PHP_EOL;

if (!empty($_FILES)) {
    echo "FILES RECEIVED:" . PHP_EOL;
    foreach ($_FILES as $k => $f) {
        echo "  $k: error={$f['error']}" . PHP_EOL;
        if ($f['error'] === UPLOAD_ERR_OK) {
            echo "  SUCCESS!" . PHP_EOL;
            move_uploaded_file($f['tmp_name'], 'C:/temp_uploads/' . basename($f['name']));
            echo "  Moved to C:/temp_uploads/" . basename($f['name']) . PHP_EOL;
        }
    }
} else {
    echo "Submit form to test." . PHP_EOL;
}
?>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="test_file">
    <button>Upload</button>
</form>
