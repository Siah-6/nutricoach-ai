<?php
/**
 * Revert CSS and JS paths from absolute back to relative
 */

$pagesDir = __DIR__ . '/pages';
$files = glob($pagesDir . '/*.php');

$replacements = [
    'href="/assets/css/' => 'href="../assets/css/',
    'src="/assets/js/' => 'src="../assets/js/',
    'src="/assets/img/' => 'src="../assets/img/',
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

echo "✅ Reverted " . count($updatedFiles) . " files back to relative paths:\n\n";
foreach ($updatedFiles as $file) {
    echo "- $file\n";
}

echo "\n✅ Localhost should work now!\n";
?>
