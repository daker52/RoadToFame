<?php

/**
 * Build Script pro Webhosting
 * Připraví kompletní projekt pro nahrání na webhosting
 */

echo "🚀 WASTELAND DOMINION - BUILD PRO WEBHOSTING\n";
echo "==========================================\n\n";

// Konfigurace
$buildDir = __DIR__ . '/build';
$distDir = __DIR__ . '/dist';

// 1. Vyčistit předchozí buildy
echo "🧹 Cleaning previous builds...\n";
if (is_dir($buildDir)) {
    removeDirectory($buildDir);
}
if (is_dir($distDir)) {
    removeDirectory($distDir);
}

mkdir($buildDir, 0755, true);
mkdir($distDir, 0755, true);

// 2. Zkopírovat PHP soubory (bez dev dependencies)
echo "📁 Copying PHP files...\n";
$phpFiles = [
    'public',
    'src', 
    'templates',
    'config',
    'database/migrations',
    'database/seed.php'
];

foreach ($phpFiles as $dir) {
    if (is_dir($dir)) {
        copyDirectory($dir, $buildDir . '/' . $dir);
        echo "   ✅ Copied: {$dir}\n";
    }
}

// 3. Zkopírovat potřebné root soubory
echo "📄 Copying root files...\n";
$rootFiles = [
    '.htaccess',
    'index.php',
    'README.md'
];

foreach ($rootFiles as $file) {
    if (file_exists($file)) {
        copy($file, $buildDir . '/' . $file);
        echo "   ✅ Copied: {$file}\n";
    }
}

// 4. Vytvořit config pro produkci
echo "⚙️ Creating production config...\n";
createProductionConfig($buildDir);

// 5. Build CSS a JS (bez webpack)
echo "🎨 Building assets...\n";
buildAssets($buildDir);

// 6. Vytvořit vendor dependencies (ručně)
echo "📦 Creating minimal vendor...\n";
createMinimalVendor($buildDir);

// 7. Optimalizovat soubory
echo "🔧 Optimizing files...\n";
optimizeFiles($buildDir);

// 8. Vytvořit installer pro databázi
echo "💾 Creating database installer...\n";
createDatabaseInstaller($buildDir);

// 9. Zkomprimovat do ZIP
echo "📦 Creating distribution package...\n";
createZipPackage($buildDir, $distDir);

echo "\n🎉 BUILD DOKONČEN!\n";
echo "==================\n";
echo "📁 Build directory: {$buildDir}\n";
echo "📦 Distribution: {$distDir}/wasteland-dominion.zip\n\n";

echo "📋 INSTRUKCE PRO NAHRÁNÍ:\n";
echo "1. Rozbalte wasteland-dominion.zip na webhosting\n";
echo "2. Spusťte /install.php pro instalaci databáze\n";
echo "3. Upravte config/config.php dle vašeho hostingu\n";
echo "4. Nastavte dokumentový root na /public\n\n";

// Helper funkce
function removeDirectory($dir) {
    if (!is_dir($dir)) return;
    
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                removeDirectory($path);
            } else {
                unlink($path);
            }
        }
    }
    rmdir($dir);
}

function copyDirectory($src, $dst) {
    if (!is_dir($src)) return;
    
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }
    
    $files = scandir($src);
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;
            
            if (is_dir($srcPath)) {
                copyDirectory($srcPath, $dstPath);
            } else {
                copy($srcPath, $dstPath);
            }
        }
    }
}

function createProductionConfig($buildDir) {
    $config = '<?php

return [
    "database" => [
        "host" => "localhost", // Změňte dle vašeho hostingu
        "name" => "wasteland_dominion", // Změňte dle vašeho hostingu  
        "user" => "root", // Změňte dle vašeho hostingu
        "pass" => "", // Změňte dle vašeho hostingu
        "charset" => "utf8mb4"
    ],
    
    "app" => [
        "name" => "Wasteland Dominion",
        "env" => "production",
        "debug" => false,
        "url" => "https://your-domain.com", // Změňte na vaši doménu
        "timezone" => "Europe/Prague"
    ],
    
    "security" => [
        "jwt_secret" => "' . bin2hex(random_bytes(32)) . '",
        "session_name" => "wasteland_session",
        "csrf_protection" => true
    ],
    
    "websocket" => [
        "host" => "0.0.0.0",
        "port" => 8080,
        "enabled" => false // Vypnuto pro standardní webhosting
    ]
];
';
    
    file_put_contents($buildDir . '/config/config.php', $config);
}

function buildAssets($buildDir) {
    // Kombinovat CSS soubory
    $cssFiles = [
        'public/assets/css/style.css',
        'public/assets/css/game.css'
    ];
    
    $combinedCSS = "";
    foreach ($cssFiles as $file) {
        if (file_exists($file)) {
            $combinedCSS .= file_get_contents($file) . "\n";
        }
    }
    
    // Minifikovat CSS (základní)
    $combinedCSS = preg_replace('/\/\*.*?\*\//s', '', $combinedCSS); // Odstranit komentáře
    $combinedCSS = preg_replace('/\s+/', ' ', $combinedCSS); // Zmenšit whitespace
    
    file_put_contents($buildDir . '/public/assets/css/app.min.css', $combinedCSS);
    
    // Kombinovat JS soubory
    $jsFiles = [
        'public/assets/js/main.js',
        'public/assets/js/game.js',
        'public/assets/js/admin.js'
    ];
    
    $combinedJS = "";
    foreach ($jsFiles as $file) {
        if (file_exists($file)) {
            $combinedJS .= file_get_contents($file) . "\n";
        }
    }
    
    file_put_contents($buildDir . '/public/assets/js/app.min.js', $combinedJS);
}

function createMinimalVendor($buildDir) {
    $vendorDir = $buildDir . '/vendor';
    mkdir($vendorDir, 0755, true);
    
    // Vytvořit autoloader
    $autoloader = '<?php

/**
 * Minimal Autoloader pro Wasteland Dominion
 */
class WastelandAutoloader {
    private static $classes = [
        "Database" => __DIR__ . "/../src/Database.php",
        "Auth" => __DIR__ . "/../src/Auth.php", 
        "Utils" => __DIR__ . "/../src/Utils.php",
        "App" => __DIR__ . "/../src/App.php"
    ];
    
    public static function register() {
        spl_autoload_register([__CLASS__, "load"]);
    }
    
    public static function load($className) {
        if (isset(self::$classes[$className])) {
            require_once self::$classes[$className];
            return true;
        }
        
        // Kontrola v Controllers
        $controllerFile = __DIR__ . "/../src/Controllers/{$className}.php";
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            return true;
        }
        
        // Kontrola v Models
        $modelFile = __DIR__ . "/../src/Models/{$className}.php";
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return true;
        }
        
        return false;
    }
}

WastelandAutoloader::register();
';
    
    file_put_contents($vendorDir . '/autoload.php', $autoloader);
}

function optimizeFiles($buildDir) {
    // Odstranit dev soubory
    $devFiles = [
        'package.json',
        'webpack.config.js',
        'node_modules',
        'composer.json',
        'composer.lock',
        '.git',
        'build-for-hosting.php'
    ];
    
    foreach ($devFiles as $file) {
        $path = $buildDir . '/' . $file;
        if (file_exists($path)) {
            if (is_dir($path)) {
                removeDirectory($path);
            } else {
                unlink($path);
            }
        }
    }
}

function createDatabaseInstaller($buildDir) {
    $installer = '<?php

/**
 * Wasteland Dominion Database Installer
 * Spusťte tento soubor po nahrání na webhosting
 */

echo "<!DOCTYPE html>";
echo "<html><head><title>Wasteland Dominion - Database Installer</title>";
echo "<style>body{font-family:Arial;margin:40px;background:#1a1a1a;color:#fff;}</style></head><body>";

echo "<h1>🎮 Wasteland Dominion - Database Installer</h1>";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $host = $_POST["host"] ?? "localhost";
    $dbname = $_POST["dbname"] ?? "";
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";
    
    if (empty($dbname) || empty($username)) {
        echo "<p style=\"color:red\">❌ Vyplňte všechna povinná pole!</p>";
    } else {
        try {
            // Test connection
            $pdo = new PDO("mysql:host={$host}", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$dbname}`");
            
            // Run migrations
            $migrationDir = __DIR__ . "/database/migrations";
            $migrations = glob($migrationDir . "/*.sql");
            sort($migrations);
            
            foreach ($migrations as $migration) {
                $sql = file_get_contents($migration);
                $pdo->exec($sql);
                echo "<p>✅ " . basename($migration) . "</p>";
            }
            
            // Run seeder
            $seederFile = __DIR__ . "/database/seed.php";
            if (file_exists($seederFile)) {
                require_once __DIR__ . "/vendor/autoload.php";
                require_once $seederFile;
                echo "<p>✅ Database seeded</p>";
            }
            
            // Update config
            $configFile = __DIR__ . "/config/config.php";
            $config = file_get_contents($configFile);
            $config = str_replace("\"host\" => \"localhost\"", "\"host\" => \"{$host}\"", $config);
            $config = str_replace("\"name\" => \"wasteland_dominion\"", "\"name\" => \"{$dbname}\"", $config);
            $config = str_replace("\"user\" => \"root\"", "\"user\" => \"{$username}\"", $config);
            $config = str_replace("\"pass\" => \"\"", "\"pass\" => \"{$password}\"", $config);
            file_put_contents($configFile, $config);
            
            echo "<h2>🎉 Instalace dokončena!</h2>";
            echo "<p><a href=\"/\" style=\"color:#39ff14\">➤ Přejít do hry</a></p>";
            echo "<p><strong>Poznámka:</strong> Smažte tento soubor (install.php) z bezpečnostních důvodů.</p>";
            
        } catch (Exception $e) {
            echo "<p style=\"color:red\">❌ Chyba: " . $e->getMessage() . "</p>";
        }
    }
} else {
    echo "<form method=\"post\" style=\"max-width:400px\">";
    echo "<p><label>Database Host:<br><input type=\"text\" name=\"host\" value=\"localhost\" required style=\"width:100%;padding:8px;margin:4px 0\"></label></p>";
    echo "<p><label>Database Name:<br><input type=\"text\" name=\"dbname\" required style=\"width:100%;padding:8px;margin:4px 0\"></label></p>";
    echo "<p><label>Username:<br><input type=\"text\" name=\"username\" required style=\"width:100%;padding:8px;margin:4px 0\"></label></p>";
    echo "<p><label>Password:<br><input type=\"password\" name=\"password\" style=\"width:100%;padding:8px;margin:4px 0\"></label></p>";
    echo "<p><button type=\"submit\" style=\"padding:12px 24px;background:#39ff14;color:#000;border:none;cursor:pointer\">🚀 Instalovat Databázi</button></p>";
    echo "</form>";
}

echo "</body></html>";
';
    
    file_put_contents($buildDir . '/install.php', $installer);
}

function createZipPackage($buildDir, $distDir) {
    $zipFile = $distDir . '/wasteland-dominion.zip';
    
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($buildDir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($buildDir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        
        $zip->close();
        echo "   ✅ Package created: wasteland-dominion.zip\n";
    } else {
        echo "   ❌ Failed to create ZIP package\n";
    }
}