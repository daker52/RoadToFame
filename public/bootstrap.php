<?php

// Bootstrap file for Wasteland Dominion

// Set error reporting based on environment
if (file_exists(__DIR__ . '/../.env')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    // Fallback if composer autoload doesn't exist
    spl_autoload_register(function ($class) {
        $prefix = 'WastelandDominion\\';
        $base_dir = __DIR__ . '/../src/';
        
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require $file;
        }
    });
}

use WastelandDominion\App;
use WastelandDominion\Router;

// Initialize application
$app = App::getInstance();
$router = $app->getRouter();

// Define routes
$router->get('', function() {
    include __DIR__ . '/index.php';
});

$router->get('/', function() {
    include __DIR__ . '/index.php';
});

// Auth routes
$router->post('/auth/register', 'AuthController@register');
$router->post('/auth/login', 'AuthController@login');
$router->post('/auth/logout', 'AuthController@logout');
$router->get('/auth/profile', 'AuthController@profile');
$router->post('/auth/profile', 'AuthController@updateProfile');
$router->post('/auth/change-password', 'AuthController@changePassword');

// Admin routes
$router->get('/admin', 'AdminController@index');
$router->get('/admin/users', 'AdminController@users');
$router->post('/admin/user-action', 'AdminController@userAction');
$router->get('/admin/settings', 'AdminController@settings');
$router->post('/admin/settings', 'AdminController@settings');

// Game routes
$router->get('/map', 'MapController@index');
$router->get('/map/location/{id}', 'MapController@location');
$router->post('/map/travel', 'MapController@travel');
$router->post('/map/explore', 'MapController@explore');

$router->get('/character', 'CharacterController@profile');
$router->post('/character/upgrade', 'CharacterController@upgradeAttribute');
$router->post('/character/experience', 'CharacterController@gainExperience');

$router->get('/quests', 'QuestController@index');
$router->get('/quests/quest/{id}', 'QuestController@quest');
$router->post('/quests/accept', 'QuestController@accept');
$router->post('/quests/abandon', 'QuestController@abandon');
$router->post('/quests/complete', 'QuestController@complete');
$router->post('/quests/progress', 'QuestController@updateProgress');
$router->get('/quests/npc/{id}', 'QuestController@npcDialog');

// Game routes group
$router->group('/game', function($router) {
    $router->get('/dashboard', 'GameController@dashboard');
    $router->get('/character-setup', 'GameController@characterSetup');
    $router->post('/character-setup', 'GameController@saveCharacterSetup');
    $router->get('/city/{cityId}', 'GameController@city');
    $router->get('/location/{locationId}', 'GameController@location');
});

// API routes group
$router->group('/api', function($router) {
    // Player API
    $router->get('/player/stats', 'ApiController@playerStats');
    $router->get('/player/inventory', 'ApiController@playerInventory');
    $router->post('/player/location/change', 'ApiController@changeLocation');
    
    // World API
    $router->get('/world/cities', 'ApiController@getCities');
    $router->get('/world/locations/{cityId}', 'ApiController@getLocations');
    
    // Quest API
    $router->get('/quests/available/{cityId}', 'ApiController@getAvailableQuests');
    $router->post('/quests/start/{questId}', 'ApiController@startQuest');
    $router->get('/quests/active', 'ApiController@getActiveQuests');
    $router->post('/quests/complete/{questId}', 'ApiController@completeQuest');
    
    // Combat API
    $router->post('/combat/initiate', 'ApiController@initiateCombat');
    $router->get('/combat/status/{combatId}', 'ApiController@getCombatStatus');
    
    // Shop API
    $router->get('/shop/items/{locationId}', 'ApiController@getShopItems');
    $router->post('/shop/buy', 'ApiController@buyItem');
    $router->post('/shop/sell', 'ApiController@sellItem');
});

// Forum routes
$router->group('/forum', function($router) {
    $router->get('/', 'ForumController@index');
    $router->get('/category/{categoryId}', 'ForumController@category');
    $router->get('/thread/{threadId}', 'ForumController@thread');
    $router->post('/thread/create', 'ForumController@createThread');
    $router->post('/post/create', 'ForumController@createPost');
});

// Admin routes (protected)
$router->group('/admin', function($router) {
    $router->get('/', 'AdminController@index');
    $router->get('/users', 'AdminController@users');
    $router->get('/quests', 'AdminController@quests');
    $router->get('/items', 'AdminController@items');
    $router->get('/enemies', 'AdminController@enemies');
}, ['AdminMiddleware']);

// Static file routes
$router->get('/assets/{type}/{file}', function($type, $file) {
    $allowedTypes = ['css', 'js', 'images'];
    
    if (!in_array($type, $allowedTypes)) {
        http_response_code(404);
        return;
    }
    
    $filePath = __DIR__ . "/assets/{$type}/{$file}";
    
    if (!file_exists($filePath)) {
        http_response_code(404);
        return;
    }
    
    // Set appropriate content type
    $contentTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'images' => 'image/' . pathinfo($file, PATHINFO_EXTENSION)
    ];
    
    header('Content-Type: ' . ($contentTypes[$type] ?? 'application/octet-stream'));
    readfile($filePath);
});

return $app;