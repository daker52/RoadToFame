<?php
namespace App\Controllers;

use App\Auth;
use App\Models\Inventory;
use App\Models\User;

class InventoryController extends BaseController
{
    private $auth;
    private $inventoryModel;
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->auth = new Auth();
        $this->inventoryModel = new Inventory();
        $this->userModel = new User();
        
        // Require authentication
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('/');
        }
    }
    
    public function index()
    {
        $user = $this->auth->getCurrentUser();
        $category = $_GET['category'] ?? null;
        
        $inventory = $this->inventoryModel->getUserInventory($user['id'], $category);
        $capacity = $this->inventoryModel->getInventoryCapacity($user['id']);
        $categories = $this->getItemCategories();
        $equipment = $this->getUserEquipment($user['id']);
        
        $this->render('game/inventory', [
            'inventory' => $inventory,
            'capacity' => $capacity,
            'categories' => $categories,
            'equipment' => $equipment,
            'currentCategory' => $category
        ]);
    }
    
    public function useItem()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $user = $this->auth->getCurrentUser();
        $itemId = (int)($_POST['item_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if (!$itemId || $quantity < 1) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid parameters']);
            return;
        }
        
        $result = $this->inventoryModel->useItem($user['id'], $itemId, $quantity);
        $this->jsonResponse($result);
    }
    
    public function equipItem()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $user = $this->auth->getCurrentUser();
        $itemId = (int)($_POST['item_id'] ?? 0);
        
        if (!$itemId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid item ID']);
            return;
        }
        
        $result = $this->inventoryModel->equipItem($user['id'], $itemId);
        $this->jsonResponse($result);
    }
    
    public function unequipItem()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $user = $this->auth->getCurrentUser();
        $itemId = (int)($_POST['item_id'] ?? 0);
        
        if (!$itemId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid item ID']);
            return;
        }
        
        if ($this->inventoryModel->unequipItem($user['id'], $itemId)) {
            $this->jsonResponse(['success' => true, 'message' => 'Item unequipped']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to unequip item']);
        }
    }
    
    public function dropItem()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $user = $this->auth->getCurrentUser();
        $itemId = (int)($_POST['item_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if (!$itemId || $quantity < 1) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid parameters']);
            return;
        }
        
        if ($this->inventoryModel->removeItem($user['id'], $itemId, $quantity)) {
            $this->jsonResponse(['success' => true, 'message' => 'Item dropped']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to drop item']);
        }
    }
    
    public function itemInfo($itemId)
    {
        $item = $this->db->query("
            SELECT 
                i.*,
                ic.name as category_name,
                ic.icon as category_icon
            FROM items i
            JOIN item_categories ic ON i.category = ic.id
            WHERE i.id = ?
        ", [$itemId])->fetch();
        
        if (!$item) {
            $this->jsonResponse(['success' => false, 'message' => 'Item not found'], 404);
            return;
        }
        
        $this->jsonResponse([
            'success' => true,
            'item' => $item
        ]);
    }
    
    private function getItemCategories()
    {
        return $this->db->query("
            SELECT * FROM item_categories 
            ORDER BY sort_order ASC
        ")->fetchAll();
    }
    
    private function getUserEquipment($userId)
    {
        return $this->db->query("
            SELECT 
                ue.*,
                i.name,
                i.description,
                i.rarity,
                i.stats_bonus,
                i.equipment_slot as slot_name
            FROM user_equipment ue
            JOIN items i ON ue.item_id = i.id
            WHERE ue.user_id = ?
            ORDER BY ue.slot ASC
        ", [$userId])->fetchAll();
    }
}