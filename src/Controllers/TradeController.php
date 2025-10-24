<?php

// Trading & Economy System Implementation
class TradeController {
    private $database;
    
    public function __construct() {
        $this->database = new Database();
    }
    
    public function market() {
        if (!Auth::check()) {
            return Utils::redirect('/login');
        }
        
        $userId = Auth::userId();
        
        // Get available merchants
        $merchants = $this->getAvailableMerchants($userId);
        
        // Get player's location for local merchants
        $location = $this->getPlayerLocation($userId);
        
        // Get market data
        $marketItems = $this->getMarketItems($location);
        
        // Get player's trading reputation
        $reputation = $this->getTradingReputation($userId);
        
        // Get player's caps
        $playerCaps = $this->getPlayerCaps($userId);
        
        return Utils::render('game/market', [
            'merchants' => $merchants,
            'marketItems' => $marketItems,
            'reputation' => $reputation,
            'playerCaps' => $playerCaps,
            'location' => $location
        ]);
    }
    
    public function merchant($merchantId) {
        if (!Auth::check()) {
            return Utils::redirect('/login');
        }
        
        $userId = Auth::userId();
        
        // Get merchant details
        $merchant = $this->getMerchant($merchantId);
        if (!$merchant) {
            return Utils::redirect('/market');
        }
        
        // Check if merchant is accessible
        $accessible = $this->isMerchantAccessible($userId, $merchantId);
        if (!$accessible['success']) {
            return Utils::render('game/merchant_unavailable', [
                'merchant' => $merchant,
                'reason' => $accessible['reason']
            ]);
        }
        
        // Get merchant's inventory
        $inventory = $this->getMerchantInventory($merchantId);
        
        // Get buying prices (what merchant will buy from player)
        $buyingPrices = $this->getMerchantBuyingPrices($merchantId);
        
        // Get player's tradeable items
        $playerItems = $this->getPlayerTradeableItems($userId);
        
        // Calculate reputation modifiers
        $reputation = $this->getTradingReputation($userId);
        $reputationModifier = $this->calculateReputationModifier($reputation[$merchant['faction']] ?? 0);
        
        return Utils::render('game/merchant', [
            'merchant' => $merchant,
            'inventory' => $inventory,
            'buyingPrices' => $buyingPrices,
            'playerItems' => $playerItems,
            'reputationModifier' => $reputationModifier,
            'playerCaps' => $this->getPlayerCaps($userId)
        ]);
    }
    
    public function buyItem() {
        if (!Auth::check()) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not authenticated']);
        }
        
        $userId = Auth::userId();
        $merchantId = $_POST['merchant_id'] ?? null;
        $itemId = $_POST['item_id'] ?? null;
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if (!$merchantId || !$itemId || $quantity < 1) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Invalid parameters']);
        }
        
        // Verify merchant and item availability
        $merchantItem = $this->database->query("
            SELECT mi.*, i.name, i.weight, m.name as merchant_name, m.faction
            FROM merchant_inventory mi
            JOIN items i ON mi.item_id = i.id
            JOIN merchants m ON mi.merchant_id = m.id
            WHERE mi.merchant_id = ? AND mi.item_id = ? AND mi.quantity >= ?
        ", [$merchantId, $itemId, $quantity])->fetch(PDO::FETCH_ASSOC);
        
        if (!$merchantItem) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Item not available']);
        }
        
        // Calculate final price with reputation discount
        $reputation = $this->getTradingReputation($userId);
        $reputationModifier = $this->calculateReputationModifier($reputation[$merchantItem['faction']] ?? 0);
        $finalPrice = ceil($merchantItem['price'] * $quantity * $reputationModifier['buy']);
        
        // Check player caps
        $playerCaps = $this->getPlayerCaps($userId);
        if ($playerCaps < $finalPrice) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Nedostatek caps']);
        }
        
        // Check inventory space/weight
        $inventoryCheck = $this->checkInventorySpace($userId, $itemId, $quantity);
        if (!$inventoryCheck['success']) {
            return Utils::jsonResponse($inventoryCheck);
        }
        
        try {
            $this->database->pdo->beginTransaction();
            
            // Deduct caps from player
            $this->database->query("
                UPDATE characters 
                SET caps = caps - ? 
                WHERE user_id = ?
            ", [$finalPrice, $userId]);
            
            // Add item to player inventory
            $this->addItemToInventory($userId, $itemId, $quantity);
            
            // Remove item from merchant inventory
            $this->database->query("
                UPDATE merchant_inventory 
                SET quantity = quantity - ? 
                WHERE merchant_id = ? AND item_id = ?
            ", [$quantity, $merchantId, $itemId]);
            
            // Update market demand (increases price for future purchases)
            $this->updateMarketDemand($itemId, $quantity, 'increase');
            
            // Grant trading experience
            $expGained = ceil($finalPrice / 10);
            $this->grantTradingExp($userId, $expGained);
            
            // Update reputation
            $this->updateReputation($userId, $merchantItem['faction'], 1);
            
            $this->database->pdo->commit();
            
            return Utils::jsonResponse([
                'success' => true,
                'message' => "Koupeno {$quantity}x {$merchantItem['name']} za {$finalPrice} caps",
                'expGained' => $expGained
            ]);
            
        } catch (Exception $e) {
            $this->database->pdo->rollback();
            return Utils::jsonResponse(['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()]);
        }
    }
    
    public function sellItem() {
        if (!Auth::check()) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not authenticated']);
        }
        
        $userId = Auth::userId();
        $merchantId = $_POST['merchant_id'] ?? null;
        $itemId = $_POST['item_id'] ?? null;
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if (!$merchantId || !$itemId || $quantity < 1) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Invalid parameters']);
        }
        
        // Check if player has the item
        $playerItem = $this->database->query("
            SELECT ui.*, i.name, i.value
            FROM user_inventory ui
            JOIN items i ON ui.item_id = i.id
            WHERE ui.user_id = ? AND ui.item_id = ? AND ui.quantity >= ? AND ui.equipped = 0
        ", [$userId, $itemId, $quantity])->fetch(PDO::FETCH_ASSOC);
        
        if (!$playerItem) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Item not found or insufficient quantity']);
        }
        
        // Get merchant's buying rate for this item
        $buyingPrice = $this->getMerchantBuyingPrice($merchantId, $itemId);
        if (!$buyingPrice) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Merchant doesn\'t buy this item']);
        }
        
        // Calculate final price with reputation bonus
        $merchant = $this->getMerchant($merchantId);
        $reputation = $this->getTradingReputation($userId);
        $reputationModifier = $this->calculateReputationModifier($reputation[$merchant['faction']] ?? 0);
        $finalPrice = floor($buyingPrice * $quantity * $reputationModifier['sell']);
        
        try {
            $this->database->pdo->beginTransaction();
            
            // Remove item from player inventory
            if ($playerItem['quantity'] <= $quantity) {
                $this->database->query("
                    DELETE FROM user_inventory 
                    WHERE user_id = ? AND item_id = ?
                ", [$userId, $itemId]);
            } else {
                $this->database->query("
                    UPDATE user_inventory 
                    SET quantity = quantity - ? 
                    WHERE user_id = ? AND item_id = ?
                ", [$quantity, $userId, $itemId]);
            }
            
            // Add caps to player
            $this->database->query("
                UPDATE characters 
                SET caps = caps + ? 
                WHERE user_id = ?
            ", [$finalPrice, $userId]);
            
            // Add item to merchant inventory (or update existing)
            $this->database->query("
                INSERT INTO merchant_inventory (merchant_id, item_id, quantity, price, restocked_at)
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                    quantity = quantity + VALUES(quantity),
                    restocked_at = NOW()
            ", [$merchantId, $itemId, $quantity, $buyingPrice]);
            
            // Update market supply (decreases price for future purchases)
            $this->updateMarketDemand($itemId, $quantity, 'decrease');
            
            // Grant trading experience
            $expGained = ceil($finalPrice / 15);
            $this->grantTradingExp($userId, $expGained);
            
            // Update reputation
            $this->updateReputation($userId, $merchant['faction'], 1);
            
            $this->database->pdo->commit();
            
            return Utils::jsonResponse([
                'success' => true,
                'message' => "Prodáno {$quantity}x {$playerItem['name']} za {$finalPrice} caps",
                'expGained' => $expGained
            ]);
            
        } catch (Exception $e) {
            $this->database->pdo->rollback();
            return Utils::jsonResponse(['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()]);
        }
    }
    
    public function negotiate() {
        if (!Auth::check()) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not authenticated']);
        }
        
        $userId = Auth::userId();
        $merchantId = $_POST['merchant_id'] ?? null;
        $itemId = $_POST['item_id'] ?? null;
        $proposedPrice = (int)($_POST['proposed_price'] ?? 0);
        $transactionType = $_POST['type'] ?? 'buy'; // buy or sell
        
        if (!$merchantId || !$itemId || $proposedPrice <= 0) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Invalid parameters']);
        }
        
        // Get merchant data
        $merchant = $this->getMerchant($merchantId);
        if (!$merchant) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Merchant not found']);
        }
        
        // Get player's charisma and trading skill
        $character = $this->database->query("
            SELECT charisma FROM characters WHERE user_id = ?
        ", [$userId])->fetch(PDO::FETCH_ASSOC);
        
        $tradingSkill = $this->database->query("
            SELECT level FROM character_skills 
            WHERE user_id = ? AND skill_name = 'trading'
        ", [$userId])->fetch(PDO::FETCH_ASSOC)['level'] ?? 1;
        
        // Calculate negotiation success chance
        $baseChance = 30; // Base 30% success rate
        $charismaBonus = $character['charisma'] * 2; // +2% per charisma point
        $skillBonus = $tradingSkill * 3; // +3% per trading level
        $reputationBonus = $this->getReputationBonus($userId, $merchant['faction']);
        
        $successChance = $baseChance + $charismaBonus + $skillBonus + $reputationBonus;
        $successChance = max(10, min(85, $successChance)); // Cap between 10-85%
        
        // Calculate price difference penalty
        $originalPrice = $this->getItemPrice($merchantId, $itemId, $transactionType);
        $priceDifference = abs($proposedPrice - $originalPrice) / $originalPrice;
        $difficultyPenalty = $priceDifference * 30; // Harder to negotiate bigger differences
        
        $finalChance = max(5, $successChance - $difficultyPenalty);
        
        // Roll for success
        $roll = rand(1, 100);
        $success = $roll <= $finalChance;
        
        if ($success) {
            // Successful negotiation
            $this->database->query("
                INSERT INTO active_negotiations (
                    user_id, merchant_id, item_id, original_price, 
                    negotiated_price, transaction_type, expires_at
                ) VALUES (?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))
            ", [$userId, $merchantId, $itemId, $originalPrice, $proposedPrice, $transactionType]);
            
            // Grant trading experience for successful negotiation
            $this->grantTradingExp($userId, 5);
            
            $message = "Vyjednávání úspěšné! Cena {$proposedPrice} caps platí 5 minut.";
            
        } else {
            // Failed negotiation
            $message = "Merchant odmítl tvou nabídku. Zkus to znovu později.";
            
            // Small reputation penalty for failed negotiation
            $this->updateReputation($userId, $merchant['faction'], -1);
        }
        
        return Utils::jsonResponse([
            'success' => $success,
            'message' => $message,
            'finalChance' => round($finalChance, 1),
            'roll' => $roll
        ]);
    }
    
    public function auction() {
        if (!Auth::check()) {
            return Utils::redirect('/login');
        }
        
        $userId = Auth::userId();
        
        // Get active auctions
        $activeAuctions = $this->getActiveAuctions();
        
        // Get player's active bids
        $playerBids = $this->getPlayerBids($userId);
        
        // Get player's auction history
        $auctionHistory = $this->getPlayerAuctionHistory($userId);
        
        return Utils::render('game/auction', [
            'activeAuctions' => $activeAuctions,
            'playerBids' => $playerBids,
            'auctionHistory' => $auctionHistory,
            'playerCaps' => $this->getPlayerCaps($userId)
        ]);
    }
    
    public function createAuction() {
        if (!Auth::check()) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not authenticated']);
        }
        
        $userId = Auth::userId();
        $itemId = $_POST['item_id'] ?? null;
        $quantity = (int)($_POST['quantity'] ?? 1);
        $startingBid = (int)($_POST['starting_bid'] ?? 1);
        $duration = (int)($_POST['duration'] ?? 24); // hours
        
        if (!$itemId || $quantity < 1 || $startingBid < 1) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Invalid parameters']);
        }
        
        // Check if player has the item
        $playerItem = $this->database->query("
            SELECT ui.*, i.name
            FROM user_inventory ui
            JOIN items i ON ui.item_id = i.id
            WHERE ui.user_id = ? AND ui.item_id = ? AND ui.quantity >= ? AND ui.equipped = 0
        ", [$userId, $itemId, $quantity])->fetch(PDO::FETCH_ASSOC);
        
        if (!$playerItem) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Item not found or insufficient quantity']);
        }
        
        // Calculate auction house fee (5% of starting bid)
        $auctionFee = ceil($startingBid * 0.05);
        
        // Check if player has enough caps for fee
        $playerCaps = $this->getPlayerCaps($userId);
        if ($playerCaps < $auctionFee) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Nedostatek caps pro poplatek aukce']);
        }
        
        try {
            $this->database->pdo->beginTransaction();
            
            // Remove item from player inventory
            if ($playerItem['quantity'] <= $quantity) {
                $this->database->query("
                    DELETE FROM user_inventory 
                    WHERE user_id = ? AND item_id = ?
                ", [$userId, $itemId]);
            } else {
                $this->database->query("
                    UPDATE user_inventory 
                    SET quantity = quantity - ? 
                    WHERE user_id = ? AND item_id = ?
                ", [$quantity, $userId, $itemId]);
            }
            
            // Deduct auction fee
            $this->database->query("
                UPDATE characters 
                SET caps = caps - ? 
                WHERE user_id = ?
            ", [$auctionFee, $userId]);
            
            // Create auction
            $endTime = date('Y-m-d H:i:s', strtotime("+{$duration} hours"));
            $this->database->query("
                INSERT INTO auctions (
                    seller_id, item_id, quantity, starting_bid, 
                    current_bid, end_time, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
            ", [$userId, $itemId, $quantity, $startingBid, $startingBid, $endTime]);
            
            $this->database->pdo->commit();
            
            return Utils::jsonResponse([
                'success' => true,
                'message' => "Aukce vytvořena! Poplatek: {$auctionFee} caps"
            ]);
            
        } catch (Exception $e) {
            $this->database->pdo->rollback();
            return Utils::jsonResponse(['success' => false, 'message' => 'Failed to create auction: ' . $e->getMessage()]);
        }
    }
    
    public function placeBid() {
        if (!Auth::check()) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not authenticated']);
        }
        
        $userId = Auth::userId();
        $auctionId = $_POST['auction_id'] ?? null;
        $bidAmount = (int)($_POST['bid_amount'] ?? 0);
        
        if (!$auctionId || $bidAmount <= 0) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Invalid parameters']);
        }
        
        // Get auction details
        $auction = $this->database->query("
            SELECT * FROM auctions 
            WHERE id = ? AND status = 'active' AND end_time > NOW()
        ", [$auctionId])->fetch(PDO::FETCH_ASSOC);
        
        if (!$auction) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Auction not found or ended']);
        }
        
        if ($auction['seller_id'] == $userId) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Cannot bid on your own auction']);
        }
        
        // Check minimum bid increment (must be at least 5% higher)
        $minBid = ceil($auction['current_bid'] * 1.05);
        if ($bidAmount < $minBid) {
            return Utils::jsonResponse(['success' => false, 'message' => "Minimum bid: {$minBid} caps"]);
        }
        
        // Check player caps
        $playerCaps = $this->getPlayerCaps($userId);
        if ($playerCaps < $bidAmount) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Nedostatek caps']);
        }
        
        try {
            $this->database->pdo->beginTransaction();
            
            // Refund previous highest bidder
            if ($auction['highest_bidder']) {
                $this->database->query("
                    UPDATE characters 
                    SET caps = caps + ? 
                    WHERE user_id = ?
                ", [$auction['current_bid'], $auction['highest_bidder']]);
            }
            
            // Deduct caps from new bidder
            $this->database->query("
                UPDATE characters 
                SET caps = caps - ? 
                WHERE user_id = ?
            ", [$bidAmount, $userId]);
            
            // Update auction
            $this->database->query("
                UPDATE auctions 
                SET current_bid = ?, highest_bidder = ?, bid_count = bid_count + 1 
                WHERE id = ?
            ", [$bidAmount, $userId, $auctionId]);
            
            // Record bid in history
            $this->database->query("
                INSERT INTO auction_bids (auction_id, bidder_id, bid_amount, created_at)
                VALUES (?, ?, ?, NOW())
            ", [$auctionId, $userId, $bidAmount]);
            
            // Extend auction if bid placed in last 5 minutes
            $timeLeft = strtotime($auction['end_time']) - time();
            if ($timeLeft < 300) { // Less than 5 minutes left
                $newEndTime = date('Y-m-d H:i:s', strtotime($auction['end_time']) + 300);
                $this->database->query("
                    UPDATE auctions SET end_time = ? WHERE id = ?
                ", [$newEndTime, $auctionId]);
            }
            
            $this->database->pdo->commit();
            
            return Utils::jsonResponse([
                'success' => true,
                'message' => "Bid placed: {$bidAmount} caps",
                'newHighestBid' => $bidAmount
            ]);
            
        } catch (Exception $e) {
            $this->database->pdo->rollback();
            return Utils::jsonResponse(['success' => false, 'message' => 'Failed to place bid: ' . $e->getMessage()]);
        }
    }
    
    // Helper methods
    private function getAvailableMerchants($userId) {
        $location = $this->getPlayerLocation($userId);
        
        return $this->database->query("
            SELECT m.*, l.name as location_name
            FROM merchants m
            JOIN locations l ON m.location_id = l.id
            WHERE m.location_id = ? AND m.active = 1
            ORDER BY m.reputation_required ASC
        ", [$location['id']])->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getPlayerLocation($userId) {
        return $this->database->query("
            SELECT l.* FROM characters c
            JOIN locations l ON c.location_id = l.id
            WHERE c.user_id = ?
        ", [$userId])->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getTradingReputation($userId) {
        return $this->database->query("
            SELECT faction, reputation FROM player_reputation 
            WHERE user_id = ?
        ", [$userId])->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getPlayerCaps($userId) {
        return $this->database->query("
            SELECT caps FROM characters WHERE user_id = ?
        ", [$userId])->fetch(PDO::FETCH_ASSOC)['caps'] ?? 0;
    }
    
    private function calculateReputationModifier($reputation) {
        // Reputation affects buying/selling prices
        // Positive reputation = better prices
        $buyModifier = 1.0 - ($reputation / 1000) * 0.2; // Up to 20% discount when buying
        $sellModifier = 1.0 + ($reputation / 1000) * 0.1; // Up to 10% bonus when selling
        
        return [
            'buy' => max(0.8, min(1.0, $buyModifier)),
            'sell' => max(1.0, min(1.1, $sellModifier))
        ];
    }
}
?>