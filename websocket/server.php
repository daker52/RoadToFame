<?php

/**
 * Wasteland Dominion WebSocket Server
 * Handles real-time communication for multiplayer features
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class WastelandWebSocketServer implements MessageComponentInterface
{
    protected $clients;
    protected $rooms;
    protected $userConnections;
    protected $db;
    
    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->rooms = [];
        $this->userConnections = [];
        
        // Initialize database connection
        $config = require __DIR__ . '/../config/config.php';
        $this->db = new PDO(
            "mysql:host={$config['database']['host']};dbname={$config['database']['name']}",
            $config['database']['user'],
            $config['database']['pass']
        );
        
        echo "🎮 Wasteland Dominion WebSocket Server starting...\n";
    }
    
    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        
        echo "New connection! ({$conn->resourceId})\n";
        
        // Send welcome message
        $this->sendToConnection($conn, [
            'type' => 'welcome',
            'message' => 'Connected to Wasteland Dominion',
            'connection_id' => $conn->resourceId
        ]);
    }
    
    public function onMessage(ConnectionInterface $from, $msg)
    {
        try {
            $data = json_decode($msg, true);
            
            if (!$data || !isset($data['type'])) {
                $this->sendError($from, 'Invalid message format');
                return;
            }
            
            $this->handleMessage($from, $data);
            
        } catch (Exception $e) {
            echo "Error processing message: " . $e->getMessage() . "\n";
            $this->sendError($from, 'Message processing failed');
        }
    }
    
    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        
        // Remove from user connections
        foreach ($this->userConnections as $userId => $connection) {
            if ($connection === $conn) {
                unset($this->userConnections[$userId]);
                
                // Notify others that user disconnected
                $this->broadcastUserStatus($userId, 'offline');
                break;
            }
        }
        
        // Remove from rooms
        foreach ($this->rooms as $roomId => $members) {
            if (($key = array_search($conn, $members)) !== false) {
                unset($this->rooms[$roomId][$key]);
                
                // Notify room members
                $this->broadcastToRoom($roomId, [
                    'type' => 'user_left',
                    'connection_id' => $conn->resourceId
                ]);
            }
        }
        
        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
    
    protected function handleMessage(ConnectionInterface $from, array $data)
    {
        switch ($data['type']) {
            case 'authenticate':
                $this->handleAuthentication($from, $data);
                break;
                
            case 'join_room':
                $this->handleJoinRoom($from, $data);
                break;
                
            case 'leave_room':
                $this->handleLeaveRoom($from, $data);
                break;
                
            case 'chat_message':
                $this->handleChatMessage($from, $data);
                break;
                
            case 'guild_message':
                $this->handleGuildMessage($from, $data);
                break;
                
            case 'combat_action':
                $this->handleCombatAction($from, $data);
                break;
                
            case 'location_update':
                $this->handleLocationUpdate($from, $data);
                break;
                
            case 'trade_request':
                $this->handleTradeRequest($from, $data);
                break;
                
            case 'heartbeat':
                $this->handleHeartbeat($from);
                break;
                
            default:
                $this->sendError($from, 'Unknown message type: ' . $data['type']);
        }
    }
    
    protected function handleAuthentication(ConnectionInterface $conn, array $data)
    {
        $token = $data['token'] ?? '';
        
        if (empty($token)) {
            $this->sendError($conn, 'Authentication token required');
            return;
        }
        
        // Validate JWT token or session
        $userId = $this->validateToken($token);
        
        if (!$userId) {
            $this->sendError($conn, 'Invalid authentication token');
            return;
        }
        
        // Store user connection
        $this->userConnections[$userId] = $conn;
        $conn->userId = $userId;
        
        // Load user data
        $userData = $this->getUserData($userId);
        
        $this->sendToConnection($conn, [
            'type' => 'authenticated',
            'user' => $userData,
            'message' => 'Authentication successful'
        ]);
        
        // Notify others that user came online
        $this->broadcastUserStatus($userId, 'online');
        
        echo "User {$userId} authenticated\n";
    }
    
    protected function handleJoinRoom(ConnectionInterface $conn, array $data)
    {
        $roomId = $data['room'] ?? '';
        
        if (empty($roomId)) {
            $this->sendError($conn, 'Room ID required');
            return;
        }
        
        // Initialize room if doesn't exist
        if (!isset($this->rooms[$roomId])) {
            $this->rooms[$roomId] = [];
        }
        
        // Add connection to room
        $this->rooms[$roomId][] = $conn;
        
        // Notify room members
        $this->broadcastToRoom($roomId, [
            'type' => 'user_joined',
            'user_id' => $conn->userId ?? null,
            'connection_id' => $conn->resourceId
        ]);
        
        $this->sendToConnection($conn, [
            'type' => 'room_joined',
            'room' => $roomId,
            'members' => count($this->rooms[$roomId])
        ]);
        
        echo "Connection {$conn->resourceId} joined room {$roomId}\n";
    }
    
    protected function handleLeaveRoom(ConnectionInterface $conn, array $data)
    {
        $roomId = $data['room'] ?? '';
        
        if (isset($this->rooms[$roomId])) {
            if (($key = array_search($conn, $this->rooms[$roomId])) !== false) {
                unset($this->rooms[$roomId][$key]);
                
                // Notify remaining members
                $this->broadcastToRoom($roomId, [
                    'type' => 'user_left',
                    'user_id' => $conn->userId ?? null,
                    'connection_id' => $conn->resourceId
                ]);
            }
        }
        
        $this->sendToConnection($conn, [
            'type' => 'room_left',
            'room' => $roomId
        ]);
    }
    
    protected function handleChatMessage(ConnectionInterface $from, array $data)
    {
        $roomId = $data['room'] ?? 'global';
        $message = $data['message'] ?? '';
        
        if (empty($message)) {
            $this->sendError($from, 'Message cannot be empty');
            return;
        }
        
        // Get user data
        $userId = $from->userId ?? null;
        $userData = $userId ? $this->getUserData($userId) : null;
        
        $chatMessage = [
            'type' => 'chat_message',
            'room' => $roomId,
            'user' => $userData,
            'message' => htmlspecialchars($message),
            'timestamp' => time()
        ];
        
        // Save to database
        $this->saveChatMessage($userId, $roomId, $message);
        
        // Broadcast to room
        $this->broadcastToRoom($roomId, $chatMessage);
    }
    
    protected function handleGuildMessage(ConnectionInterface $from, array $data)
    {
        $message = $data['message'] ?? '';
        $userId = $from->userId ?? null;
        
        if (!$userId || empty($message)) {
            $this->sendError($from, 'Invalid guild message');
            return;
        }
        
        // Get user's guild
        $guildId = $this->getUserGuild($userId);
        
        if (!$guildId) {
            $this->sendError($from, 'You are not in a guild');
            return;
        }
        
        // Get guild members
        $guildMembers = $this->getGuildMembers($guildId);
        $userData = $this->getUserData($userId);
        
        $guildMessage = [
            'type' => 'guild_message',
            'guild_id' => $guildId,
            'user' => $userData,
            'message' => htmlspecialchars($message),
            'timestamp' => time()
        ];
        
        // Send to all online guild members
        foreach ($guildMembers as $memberId) {
            if (isset($this->userConnections[$memberId])) {
                $this->sendToConnection($this->userConnections[$memberId], $guildMessage);
            }
        }
    }
    
    protected function handleCombatAction(ConnectionInterface $from, array $data)
    {
        $combatId = $data['combat_id'] ?? '';
        $action = $data['action'] ?? '';
        $userId = $from->userId ?? null;
        
        if (!$userId || empty($combatId) || empty($action)) {
            $this->sendError($from, 'Invalid combat action');
            return;
        }
        
        // Process combat action (this would integrate with combat system)
        $result = $this->processCombatAction($userId, $combatId, $action, $data);
        
        if ($result) {
            // Broadcast combat update to participants
            $this->broadcastCombatUpdate($combatId, $result);
        }
    }
    
    protected function handleLocationUpdate(ConnectionInterface $from, array $data)
    {
        $locationId = $data['location_id'] ?? null;
        $userId = $from->userId ?? null;
        
        if (!$userId || !$locationId) {
            return;
        }
        
        // Update user location in database
        $this->updateUserLocation($userId, $locationId);
        
        // Notify other players in the same location
        $this->broadcastLocationUpdate($locationId, $userId);
    }
    
    protected function handleTradeRequest(ConnectionInterface $from, array $data)
    {
        $targetUserId = $data['target_user_id'] ?? null;
        $fromUserId = $from->userId ?? null;
        
        if (!$fromUserId || !$targetUserId) {
            $this->sendError($from, 'Invalid trade request');
            return;
        }
        
        // Check if target user is online
        if (!isset($this->userConnections[$targetUserId])) {
            $this->sendError($from, 'Target user is not online');
            return;
        }
        
        $userData = $this->getUserData($fromUserId);
        
        // Send trade request to target user
        $this->sendToConnection($this->userConnections[$targetUserId], [
            'type' => 'trade_request',
            'from_user' => $userData,
            'trade_id' => uniqid('trade_')
        ]);
    }
    
    protected function handleHeartbeat(ConnectionInterface $from)
    {
        $this->sendToConnection($from, [
            'type' => 'heartbeat_response',
            'timestamp' => time()
        ]);
    }
    
    protected function sendToConnection(ConnectionInterface $conn, array $data)
    {
        $conn->send(json_encode($data));
    }
    
    protected function sendError(ConnectionInterface $conn, string $message)
    {
        $this->sendToConnection($conn, [
            'type' => 'error',
            'message' => $message
        ]);
    }
    
    protected function broadcastToRoom(string $roomId, array $data)
    {
        if (!isset($this->rooms[$roomId])) {
            return;
        }
        
        foreach ($this->rooms[$roomId] as $conn) {
            $this->sendToConnection($conn, $data);
        }
    }
    
    protected function broadcastUserStatus(int $userId, string $status)
    {
        $message = [
            'type' => 'user_status',
            'user_id' => $userId,
            'status' => $status,
            'timestamp' => time()
        ];
        
        foreach ($this->clients as $client) {
            if ($client->userId !== $userId) { // Don't send to the user themselves
                $this->sendToConnection($client, $message);
            }
        }
    }
    
    protected function broadcastCombatUpdate(string $combatId, array $result)
    {
        // Implementation would broadcast to combat participants
        echo "Broadcasting combat update for {$combatId}\n";
    }
    
    protected function broadcastLocationUpdate(int $locationId, int $userId)
    {
        $message = [
            'type' => 'location_update',
            'location_id' => $locationId,
            'user_id' => $userId,
            'timestamp' => time()
        ];
        
        // Send to users in the same location
        foreach ($this->userConnections as $connUserId => $conn) {
            if ($connUserId !== $userId) {
                // Check if user is in same location (simplified)
                $this->sendToConnection($conn, $message);
            }
        }
    }
    
    protected function validateToken(string $token): ?int
    {
        // Simplified token validation - in production use proper JWT
        try {
            $stmt = $this->db->prepare(
                "SELECT id FROM users WHERE MD5(CONCAT(id, username)) = ? AND is_active = 1"
            );
            $stmt->execute([$token]);
            
            return $stmt->fetchColumn() ?: null;
            
        } catch (Exception $e) {
            echo "Token validation error: " . $e->getMessage() . "\n";
            return null;
        }
    }
    
    protected function getUserData(int $userId): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT u.id, u.username, p.character_name, p.level 
                FROM users u 
                LEFT JOIN player_profiles p ON u.id = p.user_id 
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            
        } catch (Exception $e) {
            echo "Error getting user data: " . $e->getMessage() . "\n";
            return null;
        }
    }
    
    protected function saveChatMessage(int $userId, string $room, string $message): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO chat_messages (user_id, room, message, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $room, $message]);
            
        } catch (Exception $e) {
            echo "Error saving chat message: " . $e->getMessage() . "\n";
        }
    }
    
    protected function getUserGuild(int $userId): ?int
    {
        try {
            $stmt = $this->db->prepare("
                SELECT guild_id FROM guild_members WHERE user_id = ? AND status = 'active'
            ");
            $stmt->execute([$userId]);
            
            return $stmt->fetchColumn() ?: null;
            
        } catch (Exception $e) {
            echo "Error getting user guild: " . $e->getMessage() . "\n";
            return null;
        }
    }
    
    protected function getGuildMembers(int $guildId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT user_id FROM guild_members WHERE guild_id = ? AND status = 'active'
            ");
            $stmt->execute([$guildId]);
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
            
        } catch (Exception $e) {
            echo "Error getting guild members: " . $e->getMessage() . "\n";
            return [];
        }
    }
    
    protected function processCombatAction(int $userId, string $combatId, string $action, array $data): ?array
    {
        // Simplified combat processing - integrate with actual combat system
        echo "Processing combat action: {$action} from user {$userId}\n";
        
        return [
            'combat_id' => $combatId,
            'action' => $action,
            'user_id' => $userId,
            'result' => 'success',
            'timestamp' => time()
        ];
    }
    
    protected function updateUserLocation(int $userId, int $locationId): void
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE player_profiles SET current_location_id = ? WHERE user_id = ?
            ");
            $stmt->execute([$locationId, $userId]);
            
        } catch (Exception $e) {
            echo "Error updating user location: " . $e->getMessage() . "\n";
        }
    }
}

// Start the WebSocket server
$config = require __DIR__ . '/../config/config.php';
$host = $config['websocket']['host'] ?? '0.0.0.0';
$port = $config['websocket']['port'] ?? 8080;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WastelandWebSocketServer()
        )
    ),
    $port,
    $host
);

echo "🚀 WebSocket server started on {$host}:{$port}\n";
echo "Ready for connections!\n\n";

$server->run();