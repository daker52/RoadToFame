<?php

// Multiplayer & Guild System Implementation
class MultiplayerController {
    private $database;
    
    public function __construct() {
        $this->database = new Database();
    }
    
    public function guilds() {
        if (!Auth::check()) {
            return Utils::redirect('/login');
        }
        
        $userId = Auth::userId();
        
        // Get player's guild if any
        $playerGuild = $this->getPlayerGuild($userId);
        
        // Get available guilds to join
        $availableGuilds = $this->getAvailableGuilds($userId);
        
        // Get guild rankings
        $guildRankings = $this->getGuildRankings();
        
        return Utils::render('game/guilds', [
            'playerGuild' => $playerGuild,
            'availableGuilds' => $availableGuilds,
            'guildRankings' => $guildRankings,
            'canCreateGuild' => $this->canCreateGuild($userId)
        ]);
    }
    
    public function guild($guildId) {
        if (!Auth::check()) {
            return Utils::redirect('/login');
        }
        
        $userId = Auth::userId();
        
        // Get guild details
        $guild = $this->getGuild($guildId);
        if (!$guild) {
            return Utils::redirect('/guilds');
        }
        
        // Check if player is member
        $membership = $this->getGuildMembership($userId, $guildId);
        
        // Get guild members
        $members = $this->getGuildMembers($guildId);
        
        // Get guild territories
        $territories = $this->getGuildTerritories($guildId);
        
        // Get guild wars/alliances
        $diplomacy = $this->getGuildDiplomacy($guildId);
        
        // Get guild events/activities
        $activities = $this->getGuildActivities($guildId);
        
        return Utils::render('game/guild', [
            'guild' => $guild,
            'membership' => $membership,
            'members' => $members,
            'territories' => $territories,
            'diplomacy' => $diplomacy,
            'activities' => $activities,
            'canJoin' => !$membership && $this->canJoinGuild($userId, $guild)
        ]);
    }
    
    public function createGuild() {
        if (!Auth::check()) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not authenticated']);
        }
        
        $userId = Auth::userId();
        $guildName = trim($_POST['guild_name'] ?? '');
        $guildTag = trim($_POST['guild_tag'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $guildType = $_POST['guild_type'] ?? 'neutral';
        
        if (strlen($guildName) < 3 || strlen($guildName) > 50) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Guild name must be 3-50 characters']);
        }
        
        if (strlen($guildTag) < 2 || strlen($guildTag) > 6) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Guild tag must be 2-6 characters']);
        }
        
        // Check if player can create guild
        if (!$this->canCreateGuild($userId)) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Cannot create guild. Requirements not met.']);
        }
        
        // Check for duplicate names/tags
        $existing = $this->database->query("
            SELECT id FROM guilds 
            WHERE LOWER(name) = LOWER(?) OR LOWER(tag) = LOWER(?)
        ", [$guildName, $guildTag])->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Guild name or tag already exists']);
        }
        
        // Calculate creation cost
        $creationCost = 1000; // 1000 caps
        
        $playerCaps = $this->database->query("
            SELECT caps FROM characters WHERE user_id = ?
        ", [$userId])->fetch(PDO::FETCH_ASSOC)['caps'] ?? 0;
        
        if ($playerCaps < $creationCost) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Nedostatek caps (potřeba 1000)']);
        }
        
        try {
            $this->database->pdo->beginTransaction();
            
            // Deduct creation cost
            $this->database->query("
                UPDATE characters SET caps = caps - ? WHERE user_id = ?
            ", [$creationCost, $userId]);
            
            // Create guild
            $this->database->query("
                INSERT INTO guilds (
                    name, tag, description, guild_type, leader_id, 
                    treasury, created_at
                ) VALUES (?, ?, ?, ?, ?, 0, NOW())
            ", [$guildName, $guildTag, $description, $guildType, $userId]);
            
            $guildId = $this->database->pdo->lastInsertId();
            
            // Add creator as leader
            $this->database->query("
                INSERT INTO guild_members (
                    guild_id, user_id, rank, joined_at, contribution_points
                ) VALUES (?, ?, 'leader', NOW(), 0)
            ", [$guildId, $userId]);
            
            // Create default guild base
            $this->createGuildBase($guildId);
            
            $this->database->pdo->commit();
            
            return Utils::jsonResponse([
                'success' => true,
                'message' => 'Guild created successfully!',
                'guild_id' => $guildId
            ]);
            
        } catch (Exception $e) {
            $this->database->pdo->rollback();
            return Utils::jsonResponse(['success' => false, 'message' => 'Failed to create guild: ' . $e->getMessage()]);
        }
    }
    
    public function joinGuild() {
        if (!Auth::check()) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not authenticated']);
        }
        
        $userId = Auth::userId();
        $guildId = $_POST['guild_id'] ?? null;
        
        if (!$guildId) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Guild ID required']);
        }
        
        $guild = $this->getGuild($guildId);
        if (!$guild) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Guild not found']);
        }
        
        // Check if player can join
        if (!$this->canJoinGuild($userId, $guild)) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Cannot join this guild']);
        }
        
        // Check if guild has space
        $memberCount = $this->database->query("
            SELECT COUNT(*) as count FROM guild_members WHERE guild_id = ?
        ", [$guildId])->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($memberCount >= $guild['max_members']) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Guild is full']);
        }
        
        try {
            $this->database->pdo->beginTransaction();
            
            if ($guild['join_type'] === 'invite_only') {
                // Create join application
                $this->database->query("
                    INSERT INTO guild_applications (
                        guild_id, user_id, message, status, applied_at
                    ) VALUES (?, ?, 'Žádost o členství', 'pending', NOW())
                ", [$guildId, $userId]);
                
                $message = 'Application submitted. Waiting for guild approval.';
                
            } else {
                // Direct join for open guilds
                $this->database->query("
                    INSERT INTO guild_members (
                        guild_id, user_id, rank, joined_at, contribution_points
                    ) VALUES (?, ?, 'member', NOW(), 0)
                ", [$guildId, $userId]);
                
                // Update guild member count
                $this->database->query("
                    UPDATE guilds SET member_count = member_count + 1 WHERE id = ?
                ", [$guildId]);
                
                $message = 'Successfully joined the guild!';
            }
            
            $this->database->pdo->commit();
            
            return Utils::jsonResponse(['success' => true, 'message' => $message]);
            
        } catch (Exception $e) {
            $this->database->pdo->rollback();
            return Utils::jsonResponse(['success' => false, 'message' => 'Failed to join guild: ' . $e->getMessage()]);
        }
    }
    
    public function pvpZones() {
        if (!Auth::check()) {
            return Utils::redirect('/login');
        }
        
        $userId = Auth::userId();
        
        // Get available PvP zones
        $pvpZones = $this->getPvPZones();
        
        // Get player's PvP stats
        $pvpStats = $this->getPlayerPvPStats($userId);
        
        // Get active PvP matches
        $activeBattles = $this->getActivePvPBattles($userId);
        
        // Get PvP rankings
        $pvpRankings = $this->getPvPRankings();
        
        return Utils::render('game/pvp', [
            'pvpZones' => $pvpZones,
            'pvpStats' => $pvpStats,
            'activeBattles' => $activeBattles,
            'pvpRankings' => $pvpRankings
        ]);
    }
    
    public function enterPvP() {
        if (!Auth::check()) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not authenticated']);
        }
        
        $userId = Auth::userId();
        $zoneId = $_POST['zone_id'] ?? null;
        
        if (!$zoneId) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Zone ID required']);
        }
        
        // Check if zone exists and is active
        $zone = $this->database->query("
            SELECT * FROM pvp_zones WHERE id = ? AND active = 1
        ", [$zoneId])->fetch(PDO::FETCH_ASSOC);
        
        if (!$zone) {
            return Utils::jsonResponse(['success' => false, 'message' => 'PvP zone not available']);
        }
        
        // Check level requirements
        $character = $this->database->query("
            SELECT level FROM characters WHERE user_id = ?
        ", [$userId])->fetch(PDO::FETCH_ASSOC);
        
        if ($character['level'] < $zone['min_level'] || $character['level'] > $zone['max_level']) {
            return Utils::jsonResponse([
                'success' => false, 
                'message' => "Level requirement: {$zone['min_level']}-{$zone['max_level']}"
            ]);
        }
        
        // Check if player is already in PvP
        $existing = $this->database->query("
            SELECT id FROM pvp_matches 
            WHERE (challenger_id = ? OR defender_id = ?) AND status IN ('waiting', 'active')
        ", [$userId, $userId])->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Already in PvP queue or battle']);
        }
        
        // Add to PvP queue
        $this->database->query("
            INSERT INTO pvp_queue (
                user_id, zone_id, queue_time, preferences
            ) VALUES (?, ?, NOW(), '{}')
        ", [$userId, $zoneId]);
        
        // Try to find a match
        $opponent = $this->findPvPOpponent($userId, $zoneId);
        
        if ($opponent) {
            $matchId = $this->createPvPMatch($userId, $opponent['user_id'], $zoneId);
            
            return Utils::jsonResponse([
                'success' => true,
                'message' => 'Match found!',
                'match_id' => $matchId,
                'opponent' => $opponent['name']
            ]);
        } else {
            return Utils::jsonResponse([
                'success' => true,
                'message' => 'Added to PvP queue. Searching for opponent...',
                'queued' => true
            ]);
        }
    }
    
    public function territoryWars() {
        if (!Auth::check()) {
            return Utils::redirect('/login');
        }
        
        $userId = Auth::userId();
        
        // Get all territories and their control
        $territories = $this->getAllTerritories();
        
        // Get player's guild territory info
        $guildTerritories = $this->getPlayerGuildTerritories($userId);
        
        // Get active wars
        $activeWars = $this->getActiveTerritoryWars();
        
        // Get war history
        $warHistory = $this->getTerritoryWarHistory();
        
        return Utils::render('game/territory_wars', [
            'territories' => $territories,
            'guildTerritories' => $guildTerritories,
            'activeWars' => $activeWars,
            'warHistory' => $warHistory
        ]);
    }
    
    public function declareWar() {
        if (!Auth::check()) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not authenticated']);
        }
        
        $userId = Auth::userId();
        $targetTerritoryId = $_POST['territory_id'] ?? null;
        
        if (!$targetTerritoryId) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Territory ID required']);
        }
        
        // Check if player is guild leader/officer
        $guildRole = $this->getPlayerGuildRole($userId);
        if (!in_array($guildRole['rank'], ['leader', 'officer'])) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Only guild leaders/officers can declare war']);
        }
        
        $guild = $this->getPlayerGuild($userId);
        if (!$guild) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Must be in a guild to declare war']);
        }
        
        // Get territory info
        $territory = $this->database->query("
            SELECT * FROM territories WHERE id = ?
        ", [$targetTerritoryId])->fetch(PDO::FETCH_ASSOC);
        
        if (!$territory) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Territory not found']);
        }
        
        if ($territory['controlling_guild_id'] == $guild['id']) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Cannot attack your own territory']);
        }
        
        // Check war declaration cooldown
        $lastWar = $this->database->query("
            SELECT declared_at FROM territory_wars 
            WHERE attacking_guild_id = ? 
            ORDER BY declared_at DESC LIMIT 1
        ", [$guild['id']])->fetch(PDO::FETCH_ASSOC);
        
        if ($lastWar && strtotime($lastWar['declared_at']) > strtotime('-24 hours')) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Must wait 24 hours between war declarations']);
        }
        
        // Calculate war cost
        $warCost = 5000; // Base cost
        if ($guild['treasury'] < $warCost) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Guild treasury insufficient (need 5000 caps)']);
        }
        
        try {
            $this->database->pdo->beginTransaction();
            
            // Deduct war cost from guild treasury
            $this->database->query("
                UPDATE guilds SET treasury = treasury - ? WHERE id = ?
            ", [$warCost, $guild['id']]);
            
            // Declare war
            $warStartTime = date('Y-m-d H:i:s', strtotime('+24 hours')); // 24 hour preparation
            $this->database->query("
                INSERT INTO territory_wars (
                    territory_id, attacking_guild_id, defending_guild_id,
                    declared_at, war_start_time, status
                ) VALUES (?, ?, ?, NOW(), ?, 'declared')
            ", [$targetTerritoryId, $guild['id'], $territory['controlling_guild_id'], $warStartTime]);
            
            // Notify defending guild
            $this->sendGuildNotification(
                $territory['controlling_guild_id'],
                'war_declared',
                "Guild {$guild['name']} has declared war on {$territory['name']}!"
            );
            
            $this->database->pdo->commit();
            
            return Utils::jsonResponse([
                'success' => true,
                'message' => 'War declared! Battle begins in 24 hours.'
            ]);
            
        } catch (Exception $e) {
            $this->database->pdo->rollback();
            return Utils::jsonResponse(['success' => false, 'message' => 'Failed to declare war: ' . $e->getMessage()]);
        }
    }
    
    public function allianceSystem() {
        if (!Auth::check()) {
            return Utils::redirect('/login');
        }
        
        $userId = Auth::userId();
        
        // Get player's guild alliances
        $alliances = $this->getGuildAlliances($userId);
        
        // Get alliance proposals
        $proposals = $this->getAllianceProposals($userId);
        
        // Get potential alliance partners
        $potentialPartners = $this->getPotentialAlliances($userId);
        
        return Utils::render('game/alliances', [
            'alliances' => $alliances,
            'proposals' => $proposals,
            'potentialPartners' => $potentialPartners
        ]);
    }
    
    public function proposeAlliance() {
        if (!Auth::check()) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not authenticated']);
        }
        
        $userId = Auth::userId();
        $targetGuildId = $_POST['target_guild_id'] ?? null;
        $allianceType = $_POST['alliance_type'] ?? 'mutual_defense';
        $terms = trim($_POST['terms'] ?? '');
        
        if (!$targetGuildId) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Target guild required']);
        }
        
        // Check if player can propose alliances
        $guildRole = $this->getPlayerGuildRole($userId);
        if (!in_array($guildRole['rank'], ['leader', 'officer'])) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Only guild leaders/officers can propose alliances']);
        }
        
        $guild = $this->getPlayerGuild($userId);
        if (!$guild || $guild['id'] == $targetGuildId) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Invalid guild selection']);
        }
        
        // Check for existing alliance/proposal
        $existing = $this->database->query("
            SELECT id FROM guild_alliances 
            WHERE (guild1_id = ? AND guild2_id = ?) OR (guild1_id = ? AND guild2_id = ?)
        ", [$guild['id'], $targetGuildId, $targetGuildId, $guild['id']])->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Alliance already exists or pending']);
        }
        
        try {
            // Create alliance proposal
            $this->database->query("
                INSERT INTO alliance_proposals (
                    proposing_guild_id, target_guild_id, alliance_type, 
                    terms, proposed_at, status
                ) VALUES (?, ?, ?, ?, NOW(), 'pending')
            ", [$guild['id'], $targetGuildId, $allianceType, $terms]);
            
            // Notify target guild
            $this->sendGuildNotification(
                $targetGuildId,
                'alliance_proposal',
                "Guild {$guild['name']} has proposed an alliance!"
            );
            
            return Utils::jsonResponse([
                'success' => true,
                'message' => 'Alliance proposal sent!'
            ]);
            
        } catch (Exception $e) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Failed to send proposal: ' . $e->getMessage()]);
        }
    }
    
    // Helper methods
    private function getPlayerGuild($userId) {
        return $this->database->query("
            SELECT g.*, gm.rank, gm.joined_at, gm.contribution_points
            FROM guilds g
            JOIN guild_members gm ON g.id = gm.guild_id
            WHERE gm.user_id = ?
        ", [$userId])->fetch(PDO::FETCH_ASSOC);
    }
    
    private function canCreateGuild($userId) {
        // Check requirements: level 10+, no current guild, enough caps
        $character = $this->database->query("
            SELECT level, caps FROM characters WHERE user_id = ?
        ", [$userId])->fetch(PDO::FETCH_ASSOC);
        
        $existingMembership = $this->database->query("
            SELECT id FROM guild_members WHERE user_id = ?
        ", [$userId])->fetch(PDO::FETCH_ASSOC);
        
        return $character['level'] >= 10 && 
               $character['caps'] >= 1000 && 
               !$existingMembership;
    }
    
    private function findPvPOpponent($userId, $zoneId) {
        return $this->database->query("
            SELECT pq.user_id, c.name, c.level
            FROM pvp_queue pq
            JOIN characters c ON pq.user_id = c.user_id
            WHERE pq.zone_id = ? AND pq.user_id != ?
            ORDER BY pq.queue_time ASC
            LIMIT 1
        ", [$zoneId, $userId])->fetch(PDO::FETCH_ASSOC);
    }
    
    private function createPvPMatch($challengerId, $defenderId, $zoneId) {
        $this->database->query("
            INSERT INTO pvp_matches (
                challenger_id, defender_id, zone_id, status, created_at
            ) VALUES (?, ?, ?, 'active', NOW())
        ", [$challengerId, $defenderId, $zoneId]);
        
        // Remove both players from queue
        $this->database->query("
            DELETE FROM pvp_queue WHERE user_id IN (?, ?)
        ", [$challengerId, $defenderId]);
        
        return $this->database->pdo->lastInsertId();
    }
    
    private function createGuildBase($guildId) {
        // Create basic guild base with default buildings
        $this->database->query("
            INSERT INTO guild_bases (
                guild_id, location_x, location_y, defense_level, 
                buildings, created_at
            ) VALUES (?, ?, ?, 1, '{}', NOW())
        ", [$guildId, rand(100, 900), rand(100, 900)]);
    }
    
    private function sendGuildNotification($guildId, $type, $message) {
        $this->database->query("
            INSERT INTO guild_notifications (
                guild_id, notification_type, message, created_at, is_read
            ) VALUES (?, ?, ?, NOW(), 0)
        ", [$guildId, $type, $message]);
    }
}
?>