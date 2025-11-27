<?php
/**
 * Fix CSS and JS paths from relative to absolute
 * Run this once to update all PHP files
 */

$pagesDir = __DIR__ . '/pages';
$files = glob($pagesDir . '/*.php');

$replacements = [
    'href="../assets/css/' => 'href="/assets/css/',
    'src="../assets/js/' => 'src="/assets/js/',
    'src="../assets/img/' => 'src="/assets/img/',
];

$updatedFiles = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $updatedFiles[] = basename($file);
    }
}

echo "✅ Fixed CSS/JS paths in " . count($updatedFiles) . " files:\n\n";
foreach ($updatedFiles as $file) {
    echo "- $file\n";
}

echo "\n✅ All paths updated to absolute paths!\n";
echo "Upload these files to Hostinger and the CSS should work.\n";
?>
