<?php

// Main entry point for Wasteland Dominion
// This file handles all requests except the static index page

try {
    $app = require_once __DIR__ . '/bootstrap.php';
    $app->run();
} catch (Exception $e) {
    // In production, this should log the error and show a generic error page
    if (defined('WD_DEBUG') && WD_DEBUG) {
        echo "<h1>Application Error</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        echo "<!DOCTYPE html><html><head><title>Error</title></head><body>";
        echo "<h1>🔥 Wasteland Error</h1>";
        echo "<p>Something went wrong in the wasteland. Please try again later.</p>";
        echo "<a href='/'>Return to Safety</a>";
        echo "</body></html>";
    }
}