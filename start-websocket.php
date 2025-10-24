#!/usr/bin/env php
<?php

/**
 * Wasteland Dominion WebSocket Server Start Script
 */

// Change to project directory
$projectDir = dirname(__DIR__);
chdir($projectDir);

echo "🎮 Wasteland Dominion WebSocket Server\n";
echo "=====================================\n\n";

// Check if composer dependencies are installed
if (!file_exists('vendor/autoload.php')) {
    echo "❌ Error: Composer dependencies not installed\n";
    echo "   Please run: composer install\n\n";
    exit(1);
}

// Check configuration
if (!file_exists('config/config.php')) {
    echo "❌ Error: Configuration file not found\n";
    echo "   Please copy config/config.example.php to config/config.php\n\n";
    exit(1);
}

$config = require 'config/config.php';

// Display configuration
echo "📋 Server Configuration:\n";
echo "   Host: " . ($config['websocket']['host'] ?? '0.0.0.0') . "\n";
echo "   Port: " . ($config['websocket']['port'] ?? 8080) . "\n";
echo "   Environment: " . ($config['app']['env'] ?? 'development') . "\n\n";

// Check if port is available
$host = $config['websocket']['host'] ?? '0.0.0.0';
$port = $config['websocket']['port'] ?? 8080;

if ($socket = @fsockopen($host, $port, $errno, $errstr, 1)) {
    fclose($socket);
    echo "❌ Error: Port {$port} is already in use\n";
    echo "   Please choose a different port in config/config.php\n\n";
    exit(1);
}

// Start server
echo "🚀 Starting WebSocket server...\n\n";

try {
    require 'websocket/server.php';
} catch (Exception $e) {
    echo "💥 Server error: " . $e->getMessage() . "\n\n";
    exit(1);
}