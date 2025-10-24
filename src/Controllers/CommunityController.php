<?php

// Community & Social System Implementation
class CommunityController {
    private $database;
    
    public function __construct() {
        $this->database = new Database();
    }
    
    public function dashboard() {
        if (!Auth::check()) {
            return Utils::redirect('/login');
        }
        
        $userId = Auth::userId();
        
        // Get recent chat messages
        $recentMessages = $this->getRecentChatMessages();
        
        // Get server events
        $serverEvents = $this->getActiveServerEvents();
        
        // Get leaderboards
        $leaderboards = $this->getLeaderboards();
        
        // Get player achievements
        $achievements = $this->getPlayerAchievements($userId);
        
        // Get online players
        $onlinePlayers = $this->getOnlinePlayers();
        
        // Get community news
        $communityNews = $this->getCommunityNews();
        
        return Utils::render('game/community', [
            'recentMessages' => $recentMessages,
            'serverEvents' => $serverEvents,
            'leaderboards' => $leaderboards,
            'achievements' => $achievements,
            'onlinePlayers' => $onlinePlayers,
            'communityNews' => $communityNews
        ]);
    }
    
    public function chat() {
        if (!Auth::check()) {
            return Utils::redirect('/login');
        }
        
        $userId = Auth::userId();
        $channel = $_GET['channel'] ?? 'global';
        
        // Get chat channels player has access to
        $availableChannels = $this->getAvailableChannels($userId);
        
        // Get chat messages for current channel
        $messages = $this->getChatMessages($channel, $userId);
        
        // Get online players in channel
        $onlineInChannel = $this->getOnlineInChannel($channel);
        
        return Utils::render('game/chat', [
            'availableChannels' => $availableChannels,
            'currentChannel' => $channel,
            'messages' => $messages,
            'onlineInChannel' => $onlineInChannel
        ]);
    }
    
    public function sendMessage() {
        if (!Auth::check()) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not authenticated']);
        }
        
        $userId = Auth::userId();
        $channel = $_POST['channel'] ?? 'global';
        $message = trim($_POST['message'] ?? '');
        
        if (empty($message) || strlen($message) > 500) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Invalid message length']);
        }
        
        // Check if player is muted
        $muted = $this->database->query("
            SELECT * FROM player_mutes 
            WHERE user_id = ? AND (expires_at IS NULL OR expires_at > NOW())
        ", [$userId])->fetch(PDO::FETCH_ASSOC);
        
        if ($muted) {
            return Utils::jsonResponse(['success' => false, 'message' => 'You are muted']);
        }
        
        // Check message rate limiting
        $recentMessages = $this->database->query("
            SELECT COUNT(*) as count FROM chat_messages 
            WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
        ", [$userId])->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($recentMessages >= 10) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Rate limit exceeded']);
        }
        
        // Check channel permissions
        if (!$this->canAccessChannel($userId, $channel)) {
            return Utils::jsonResponse(['success' => false, 'message' => 'No access to this channel']);
        }
        
        // Filter message content
        $filteredMessage = $this->filterMessage($message);
        
        try {
            // Save message
            $this->database->query("
                INSERT INTO chat_messages (
                    user_id, channel, message, created_at
                ) VALUES (?, ?, ?, NOW())
            ", [$userId, $channel, $filteredMessage]);
            
            $messageId = $this->database->pdo->lastInsertId();
            
            // Get user info for response
            $user = $this->database->query("
                SELECT name FROM characters WHERE user_id = ?
            ", [$userId])->fetch(PDO::FETCH_ASSOC);
            
            // Broadcast to other players (WebSocket would be ideal here)
            $this->broadcastMessage($channel, [
                'id' => $messageId,
                'user_id' => $userId,
                'username' => $user['name'],
                'message' => $filteredMessage,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
            return Utils::jsonResponse([
                'success' => true,
                'message_id' => $messageId
            ]);
            
        } catch (Exception $e) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Failed to send message']);
        }
    }
    
    public function events() {
        if (!Auth::check()) {
            return Utils::redirect('/login');
        }
        
        $userId = Auth::userId();
        
        // Get active events
        $activeEvents = $this->getActiveEvents();
        
        // Get upcoming events
        $upcomingEvents = $this->getUpcomingEvents();
        
        // Get player event participation
        $participation = $this->getPlayerEventParticipation($userId);
        
        // Get event leaderboards
        $eventLeaderboards = $this->getEventLeaderboards();
        
        return Utils::render('game/events', [
            'activeEvents' => $activeEvents,
            'upcomingEvents' => $upcomingEvents,
            'participation' => $participation,
            'eventLeaderboards' => $eventLeaderboards
        ]);
    }
    
    public function joinEvent() {
        if (!Auth::check()) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not authenticated']);
        }
        
        $userId = Auth::userId();
        $eventId = $_POST['event_id'] ?? null;
        
        if (!$eventId) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Event ID required']);
        }
        
        // Get event details
        $event = $this->database->query("
            SELECT * FROM server_events 
            WHERE id = ? AND status = 'active' AND end_time > NOW()
        ", [$eventId])->fetch(PDO::FETCH_ASSOC);
        
        if (!$event) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Event not found or ended']);
        }
        
        // Check if already participating
        $existing = $this->database->query("
            SELECT id FROM event_participants 
            WHERE event_id = ? AND user_id = ?
        ", [$eventId, $userId])->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Already participating']);
        }
        
        // Check level requirements
        $character = $this->database->query("
            SELECT level FROM characters WHERE user_id = ?
        ", [$userId])->fetch(PDO::FETCH_ASSOC);
        
        if ($character['level'] < $event['min_level']) {
            return Utils::jsonResponse([
                'success' => false, 
                'message' => "Minimum level required: {$event['min_level']}"
            ]);
        }
        
        // Check participant limit
        if ($event['max_participants']) {
            $currentParticipants = $this->database->query("
                SELECT COUNT(*) as count FROM event_participants WHERE event_id = ?
            ", [$eventId])->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($currentParticipants >= $event['max_participants']) {
                return Utils::jsonResponse(['success' => false, 'message' => 'Event is full']);
            }
        }
        
        try {
            // Join event
            $this->database->query("
                INSERT INTO event_participants (
                    event_id, user_id, joined_at, score
                ) VALUES (?, ?, NOW(), 0)
            ", [$eventId, $userId]);
            
            // Initialize event progress if needed
            $this->initializeEventProgress($userId, $eventId);
            
            return Utils::jsonResponse([
                'success' => true,
                'message' => "Joined event: {$event['name']}"
            ]);
            
        } catch (Exception $e) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Failed to join event']);
        }
    }
    
    public function leaderboards() {
        if (!Auth::check()) {
            return Utils::redirect('/login');
        }
        
        $userId = Auth::userId();
        $category = $_GET['category'] ?? 'level';
        
        // Get leaderboard data
        $leaderboard = $this->getLeaderboard($category);
        
        // Get player's rank in this category
        $playerRank = $this->getPlayerRank($userId, $category);
        
        // Get available leaderboard categories
        $categories = $this->getLeaderboardCategories();
        
        return Utils::render('game/leaderboards', [
            'leaderboard' => $leaderboard,
            'playerRank' => $playerRank,
            'categories' => $categories,
            'currentCategory' => $category
        ]);
    }
    
    public function achievements() {
        if (!Auth::check()) {
            return Utils::redirect('/login');
        }
        
        $userId = Auth::userId();
        
        // Get all achievements with player progress
        $achievements = $this->getAllAchievementsWithProgress($userId);
        
        // Get player's unlocked achievements
        $unlockedAchievements = $this->getUnlockedAchievements($userId);
        
        // Get achievement categories
        $categories = $this->getAchievementCategories();
        
        // Calculate completion statistics
        $stats = $this->getAchievementStats($userId);
        
        return Utils::render('game/achievements', [
            'achievements' => $achievements,
            'unlockedAchievements' => $unlockedAchievements,
            'categories' => $categories,
            'stats' => $stats
        ]);
    }
    
    public function createServerEvent() {
        if (!Auth::check()) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not authenticated']);
        }
        
        $userId = Auth::userId();
        
        // Check if user is admin
        if (!$this->isAdmin($userId)) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Admin access required']);
        }
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $eventType = $_POST['event_type'] ?? 'general';
        $startTime = $_POST['start_time'] ?? null;
        $duration = (int)($_POST['duration'] ?? 24); // hours
        $rewards = $_POST['rewards'] ?? '{}';
        
        if (empty($name) || empty($startTime)) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Name and start time required']);
        }
        
        try {
            $endTime = date('Y-m-d H:i:s', strtotime($startTime) + ($duration * 3600));
            
            $this->database->query("
                INSERT INTO server_events (
                    name, description, event_type, start_time, end_time,
                    rewards, created_by, created_at, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'scheduled')
            ", [$name, $description, $eventType, $startTime, $endTime, $rewards, $userId]);
            
            $eventId = $this->database->pdo->lastInsertId();
            
            // Schedule event activation
            $this->scheduleEventActivation($eventId, $startTime);
            
            return Utils::jsonResponse([
                'success' => true,
                'message' => 'Server event created',
                'event_id' => $eventId
            ]);
            
        } catch (Exception $e) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Failed to create event']);
        }
    }
    
    public function checkAchievements($userId, $action, $data = []) {
        // This method is called from other parts of the game to check for achievement unlocks
        
        $achievements = $this->database->query("
            SELECT * FROM achievements WHERE trigger_action = ?
        ", [$action])->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($achievements as $achievement) {
            // Check if player already has this achievement
            $existing = $this->database->query("
                SELECT id FROM player_achievements 
                WHERE user_id = ? AND achievement_id = ?
            ", [$userId, $achievement['id']])->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) continue;
            
            // Check achievement requirements
            if ($this->checkAchievementRequirements($userId, $achievement, $data)) {
                $this->unlockAchievement($userId, $achievement['id']);
            } else {
                // Update progress if applicable
                $this->updateAchievementProgress($userId, $achievement['id'], $data);
            }
        }
    }
    
    public function socialInteractions() {
        if (!Auth::check()) {
            return Utils::redirect('/login');
        }
        
        $userId = Auth::userId();
        
        // Get friend list
        $friends = $this->getFriends($userId);
        
        // Get friend requests
        $friendRequests = $this->getFriendRequests($userId);
        
        // Get recent player activities
        $activities = $this->getRecentActivities($userId);
        
        // Get nearby players
        $nearbyPlayers = $this->getNearbyPlayers($userId);
        
        return Utils::render('game/social', [
            'friends' => $friends,
            'friendRequests' => $friendRequests,
            'activities' => $activities,
            'nearbyPlayers' => $nearbyPlayers
        ]);
    }
    
    public function sendFriendRequest() {
        if (!Auth::check()) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not authenticated']);
        }
        
        $userId = Auth::userId();
        $targetUsername = trim($_POST['username'] ?? '');
        
        if (empty($targetUsername)) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Username required']);
        }
        
        // Find target user
        $targetUser = $this->database->query("
            SELECT user_id, name FROM characters WHERE LOWER(name) = LOWER(?)
        ", [$targetUsername])->fetch(PDO::FETCH_ASSOC);
        
        if (!$targetUser) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Player not found']);
        }
        
        if ($targetUser['user_id'] == $userId) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Cannot add yourself']);
        }
        
        // Check if already friends or request exists
        $existing = $this->database->query("
            SELECT id FROM friendships 
            WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)
        ", [$userId, $targetUser['user_id'], $targetUser['user_id'], $userId])->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Already friends or request pending']);
        }
        
        try {
            // Send friend request
            $this->database->query("
                INSERT INTO friendships (
                    user1_id, user2_id, status, requested_at
                ) VALUES (?, ?, 'pending', NOW())
            ", [$userId, $targetUser['user_id']]);
            
            // Notify target user
            $this->sendNotification($targetUser['user_id'], 'friend_request', 
                "Friend request from {$this->getPlayerName($userId)}");
            
            return Utils::jsonResponse([
                'success' => true,
                'message' => "Friend request sent to {$targetUser['name']}"
            ]);
            
        } catch (Exception $e) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Failed to send friend request']);
        }
    }
    
    // Helper methods
    private function getRecentChatMessages($limit = 50) {
        return $this->database->query("
            SELECT cm.*, c.name as username
            FROM chat_messages cm
            JOIN characters c ON cm.user_id = c.user_id
            WHERE cm.channel = 'global'
            ORDER BY cm.created_at DESC
            LIMIT ?
        ", [$limit])->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getActiveServerEvents() {
        return $this->database->query("
            SELECT * FROM server_events 
            WHERE status = 'active' AND start_time <= NOW() AND end_time > NOW()
            ORDER BY end_time ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getLeaderboards() {
        return [
            'level' => $this->getTopPlayers('level'),
            'caps' => $this->getTopPlayers('caps'),
            'pvp_wins' => $this->getTopPlayers('pvp_wins'),
            'guild_contributions' => $this->getTopGuildContributors()
        ];
    }
    
    private function getTopPlayers($category, $limit = 10) {
        $validCategories = ['level', 'caps', 'experience', 'pvp_wins', 'kills'];
        
        if (!in_array($category, $validCategories)) {
            return [];
        }
        
        return $this->database->query("
            SELECT c.name, c.{$category}, c.level
            FROM characters c
            ORDER BY c.{$category} DESC
            LIMIT ?
        ", [$limit])->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function filterMessage($message) {
        // Basic profanity filter and spam prevention
        $bannedWords = ['spam', 'hack', 'cheat']; // Would be more comprehensive
        
        foreach ($bannedWords as $word) {
            $message = str_ireplace($word, str_repeat('*', strlen($word)), $message);
        }
        
        return $message;
    }
    
    private function canAccessChannel($userId, $channel) {
        // Check channel permissions
        switch ($channel) {
            case 'global':
                return true;
            case 'guild':
                return $this->database->query("
                    SELECT id FROM guild_members WHERE user_id = ?
                ", [$userId])->fetch(PDO::FETCH_ASSOC) ? true : false;
            case 'admin':
                return $this->isAdmin($userId);
            default:
                return false;
        }
    }
    
    private function isAdmin($userId) {
        return $this->database->query("
            SELECT admin FROM users WHERE id = ?
        ", [$userId])->fetch(PDO::FETCH_ASSOC)['admin'] ?? false;
    }
    
    private function unlockAchievement($userId, $achievementId) {
        $achievement = $this->database->query("
            SELECT * FROM achievements WHERE id = ?
        ", [$achievementId])->fetch(PDO::FETCH_ASSOC);
        
        // Unlock achievement
        $this->database->query("
            INSERT INTO player_achievements (
                user_id, achievement_id, unlocked_at
            ) VALUES (?, ?, NOW())
        ", [$userId, $achievementId]);
        
        // Grant rewards
        if ($achievement['reward_type'] && $achievement['reward_amount']) {
            $this->grantAchievementReward($userId, $achievement);
        }
        
        // Notify player
        $this->sendNotification($userId, 'achievement_unlocked', 
            "Achievement unlocked: {$achievement['name']}!");
    }
    
    private function broadcastMessage($channel, $messageData) {
        // In a real implementation, this would use WebSockets
        // For now, we'll store it for polling-based updates
        $this->database->query("
            INSERT INTO chat_broadcasts (
                channel, message_data, created_at
            ) VALUES (?, ?, NOW())
        ", [$channel, json_encode($messageData)]);
    }
}
?>