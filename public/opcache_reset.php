<?php
// Reset opcache for GeminiChatController
$file = realpath(__DIR__ . '/../app/Http/Controllers/GeminiChatController.php');
echo "File: $file\n";
echo "Exists: " . (file_exists($file) ? 'yes' : 'no') . "\n";

if (function_exists('opcache_is_script_cached')) {
    echo "Cached: " . (opcache_is_script_cached($file) ? 'yes' : 'no') . "\n";
}

if (function_exists('opcache_invalidate')) {
    $result = opcache_invalidate($file, true);
    echo "Invalidated: " . ($result ? 'yes' : 'no') . "\n";
}

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "Full opcache reset done\n";
} else {
    echo "opcache_reset not available\n";
}

echo "\nDone. Now re-test /gemini-chat/debug\n";
