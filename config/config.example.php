<?php

/**
 * Example Configuration File
 * Copy this file to config.php and update with your settings
 */

return [
    "database" => [
        "host" => "localhost",
        "name" => "wasteland_dominion",
        "user" => "your_username",
        "pass" => "your_password",
        "charset" => "utf8mb4"
    ],
    
    "app" => [
        "name" => "Wasteland Dominion",
        "env" => "development", // development, production
        "debug" => true, // Set to false in production
        "url" => "http://localhost",
        "timezone" => "Europe/Prague"
    ],
    
    "security" => [
        "jwt_secret" => "change_this_to_random_32_character_string",
        "session_name" => "wasteland_session",
        "csrf_protection" => true
    ],
    
    "websocket" => [
        "host" => "0.0.0.0",
        "port" => 8080,
        "enabled" => true // Set to false for webhosting without WebSocket support
    ],
    
    "mail" => [
        "driver" => "smtp",
        "host" => "smtp.example.com",
        "port" => 587,
        "username" => "your_email@example.com",
        "password" => "your_email_password",
        "encryption" => "tls"
    ]
];