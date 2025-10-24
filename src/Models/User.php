<?php

namespace WastelandDominion\Models;

class User extends BaseModel
{
    protected $table = 'users';
    protected $fillable = [
        'username', 'email', 'password_hash', 'email_verified_at', 'is_active'
    ];
    protected $hidden = ['password_hash'];
    
    public function createUser(array $userData): int
    {
        // Hash password
        $userData['password_hash'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        unset($userData['password']);
        
        $this->beginTransaction();
        
        try {
            // Create user
            $userId = $this->create($userData);
            
            // Create user profile with default values
            $this->createDefaultProfile($userId);
            
            // Create currency account
            $this->createCurrencyAccount($userId);
            
            $this->commit();
            
            return $userId;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }
    
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    public function findByCredentials(string $username, string $password): ?array
    {
        // Try username first
        $user = $this->findBy('username', $username);
        
        // If not found, try email
        if (!$user) {
            $user = $this->findBy('email', $username);
        }
        
        if ($user && $this->verifyPassword($password, $user['password_hash'])) {
            return $user;
        }
        
        return null;
    }
    
    public function updateLastLogin(int $userId): void
    {
        $this->update($userId, [
            'last_login_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function isEmailTaken(string $email): bool
    {
        return $this->exists(['email' => $email]);
    }
    
    public function isUsernameTaken(string $username): bool
    {
        return $this->exists(['username' => $username]);
    }
    
    public function getProfile(int $userId): ?array
    {
        return $this->db->fetch("
            SELECT 
                u.*,
                up.*,
                p.name as profession_name,
                c.name as current_city_name,
                l.name as current_location_name,
                ca.bottle_caps,
                ca.diamonds,
                ca.energy_cells,
                ca.scrap_metal
            FROM users u
            LEFT JOIN user_profiles up ON u.id = up.user_id
            LEFT JOIN professions p ON up.profession_id = p.id
            LEFT JOIN cities c ON up.current_city_id = c.id
            LEFT JOIN locations l ON up.current_location_id = l.id
            LEFT JOIN currency_accounts ca ON u.id = ca.user_id
            WHERE u.id = ?
        ", [$userId]);
    }
    
    private function createDefaultProfile(int $userId): void
    {
        // Get default city (first active city)
        $defaultCity = $this->db->fetch(
            "SELECT id FROM cities WHERE is_active = 1 ORDER BY id LIMIT 1"
        );
        
        if (!$defaultCity) {
            throw new \Exception("No active cities found");
        }
        
        // Get default profession (first active profession)
        $defaultProfession = $this->db->fetch(
            "SELECT id FROM professions WHERE is_active = 1 ORDER BY id LIMIT 1"
        );
        
        if (!$defaultProfession) {
            throw new \Exception("No active professions found");
        }
        
        $this->db->insert('user_profiles', [
            'user_id' => $userId,
            'display_name' => 'Survivor',
            'profession_id' => $defaultProfession['id'],
            'current_city_id' => $defaultCity['id'],
            'level' => 1,
            'experience' => 0,
            'strength' => 10,
            'agility' => 10,
            'intelligence' => 10,
            'endurance' => 10,
            'luck' => 10,
            'current_health' => 100,
            'max_health' => 100,
            'current_energy' => 100,
            'max_energy' => 100
        ]);
    }
    
    private function createCurrencyAccount(int $userId): void
    {
        $this->db->insert('currency_accounts', [
            'user_id' => $userId,
            'bottle_caps' => 100,
            'diamonds' => 0,
            'energy_cells' => 0,
            'scrap_metal' => 0
        ]);
    }
    
    public function getActiveUsers(int $limit = 10): array
    {
        return $this->db->fetchAll("
            SELECT 
                u.id,
                u.username,
                up.display_name,
                up.level,
                c.name as current_city,
                u.last_login_at
            FROM users u
            JOIN user_profiles up ON u.id = up.user_id
            JOIN cities c ON up.current_city_id = c.id
            WHERE u.is_active = 1
            ORDER BY u.last_login_at DESC
            LIMIT ?
        ", [$limit]);
    }
    
    public function getUserStats(int $userId): array
    {
        $stats = $this->db->fetch("
            SELECT 
                up.level,
                up.experience,
                up.strength,
                up.agility,
                up.intelligence,
                up.endurance,
                up.luck,
                up.current_health,
                up.max_health,
                up.current_energy,
                up.max_energy,
                (SELECT COUNT(*) FROM quest_instances WHERE user_id = ? AND status = 'completed') as completed_quests,
                (SELECT COUNT(*) FROM combat_logs WHERE user_id = ? AND result = 'victory') as victories,
                (SELECT SUM(experience_gained) FROM combat_logs WHERE user_id = ?) as total_combat_exp
            FROM user_profiles up
            WHERE up.user_id = ?
        ", [$userId, $userId, $userId, $userId]);
        
        return $stats ?: [];
    }
}