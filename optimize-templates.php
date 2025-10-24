<?php
/**
 * Optimize Templates for Production
 * Odstraní WebSocket kód z template souborů pro webhosting
 */

echo "🔧 OPTIMIZING TEMPLATES FOR WEBHOSTING\n";
echo "=====================================\n\n";

$templateDir = __DIR__ . '/templates';
$optimized = 0;

// Najít všechny PHP template soubory
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($templateDir),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($iterator as $file) {
    if ($file->getExtension() === 'php') {
        $filePath = $file->getRealPath();
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        // Odstranit WebSocket kód
        $content = preg_replace('/\/\*\s*WebSocket.*?\*\/.*?<script>.*?<\/script>/s', '', $content);
        $content = preg_replace('/\/\/\s*WebSocket.*?connectWebSocket\(\);/s', '', $content);
        $content = preg_replace('/this\.ws\s*=.*?;/s', '', $content);
        $content = preg_replace('/window\.ws\s*=.*?;/s', '', $content);
        
        // Odstranit odkazy na WebSocket v JS
        $content = str_replace('initWebSocket();', '// WebSocket disabled for webhosting', $content);
        $content = str_replace('connectWebSocket();', '// WebSocket disabled for webhosting', $content);
        
        // Odstranit Node.js/Webpack odkazy
        $content = preg_replace('/<!--.*?webpack.*?-->/s', '', $content);
        $content = preg_replace('/\/\*.*?webpack.*?\*\//s', '', $content);
        
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            $optimized++;
            echo "   ✅ Optimized: " . str_replace(__DIR__, '', $filePath) . "\n";
        }
    }
}

echo "\n🎉 Optimization complete! Optimized {$optimized} files.\n";