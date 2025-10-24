<?php include __DIR__ . '/../base.php'; ?>

<style>
.character-interface {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    min-height: 100vh;
    color: #e8e8e8;
    padding: 20px;
}

.character-header {
    background: linear-gradient(90deg, #ff6b35, #ffd23f);
    color: #1a1a1a;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.character-name {
    font-size: 2em;
    font-weight: bold;
}

.character-level {
    display: flex;
    align-items: center;
    gap: 15px;
}

.level-badge {
    background: #1a1a1a;
    color: #ffd23f;
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: bold;
    font-size: 1.2em;
}

.experience-bar {
    width: 200px;
    height: 10px;
    background: #1a1a1a;
    border-radius: 5px;
    overflow: hidden;
}

.experience-fill {
    height: 100%;
    background: linear-gradient(90deg, #39ff14, #2ed615);
    transition: width 0.3s;
}

.character-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.character-stats {
    background: #2d2d2d;
    border: 2px solid #ff6b35;
    border-radius: 10px;
    padding: 25px;
}

.character-vitals {
    background: #2d2d2d;
    border: 2px solid #39ff14;
    border-radius: 10px;
    padding: 25px;
}

.section-title {
    color: #ffd23f;
    font-size: 1.5em;
    font-weight: bold;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.attribute-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: rgba(255, 107, 53, 0.1);
    border-radius: 8px;
    margin-bottom: 10px;
    transition: all 0.3s;
}

.attribute-item:hover {
    background: rgba(255, 107, 53, 0.2);
}

.attribute-name {
    font-weight: bold;
    color: #e8e8e8;
    display: flex;
    align-items: center;
    gap: 10px;
}

.attribute-value {
    font-size: 1.5em;
    font-weight: bold;
    color: #ffd23f;
    min-width: 40px;
    text-align: center;
}

.attribute-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-upgrade {
    background: linear-gradient(45deg, #39ff14, #2ed615);
    color: #1a1a1a;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s;
}

.btn-upgrade:hover {
    background: linear-gradient(45deg, #2ed615, #39ff14);
    transform: translateY(-2px);
}

.btn-upgrade:disabled {
    background: #666;
    color: #999;
    cursor: not-allowed;
    transform: none;
}

.vital-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: rgba(57, 255, 20, 0.1);
    border-radius: 8px;
    margin-bottom: 15px;
}

.vital-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.vital-name {
    font-weight: bold;
    color: #e8e8e8;
}

.vital-values {
    font-size: 1.2em;
    font-weight: bold;
    color: #39ff14;
}

.progress-bar {
    width: 150px;
    height: 8px;
    background: #1a1a1a;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    transition: width 0.3s;
}

.health-bar { background: linear-gradient(90deg, #e74c3c, #c0392b); }
.energy-bar { background: linear-gradient(90deg, #3498db, #2980b9); }

.skill-points {
    background: linear-gradient(135deg, #8e44ad, #9b59b6);
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
}

.skill-points-value {
    font-size: 2em;
    font-weight: bold;
    margin-bottom: 10px;
}

.attribute-bonus {
    font-size: 0.9em;
    color: #aaa;
    margin-top: 5px;
}

.character-portrait {
    width: 120px;
    height: 120px;
    background: radial-gradient(circle, #ff6b35, #ffd23f);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3em;
    color: #1a1a1a;
    border: 5px solid #1a1a1a;
}

.level-progress {
    display: flex;
    flex-direction: column;
    gap: 5px;
    color: #1a1a1a;
}

.experience-text {
    font-size: 0.9em;
    font-weight: bold;
}
</style>

<div class="character-interface">
    <div class="character-header">
        <div style="display: flex; align-items: center; gap: 20px;">
            <div class="character-portrait">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <div class="character-name"><?= htmlspecialchars($profile['username'] ?? 'Wasteland Survivor') ?></div>
                <div style="color: #666; font-size: 1.1em;">
                    <?= htmlspecialchars($profile['full_name'] ?? 'Unnamed Survivor') ?>
                </div>
            </div>
        </div>
        
        <div class="character-level">
            <div class="level-badge">
                Level <?= $stats['level'] ?? 1 ?>
            </div>
            <div class="level-progress">
                <div class="experience-text">
                    EXP: <?= number_format($stats['experience'] ?? 0) ?> / <?= number_format($this->calculateExperienceForLevel(($stats['level'] ?? 1) + 1)) ?>
                </div>
                <div class="experience-bar">
                    <div class="experience-fill" style="width: <?= $this->calculateExperienceProgress($stats) ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="character-content">
        <div class="character-stats">
            <div class="section-title">
                <i class="fas fa-dumbbell"></i> Attributes
            </div>
            
            <div class="skill-points">
                <div class="skill-points-value" id="skill-points"><?= $skillPoints ?></div>
                <div>Available Skill Points</div>
            </div>
            
            <div class="attribute-item">
                <div class="attribute-name">
                    <i class="fas fa-fist-raised" style="color: #e74c3c;"></i>
                    Strength
                    <div class="attribute-bonus">+<?= floor(($stats['strength'] ?? 10) / 2) ?> damage</div>
                </div>
                <div class="attribute-value" id="strength-value"><?= $stats['strength'] ?? 10 ?></div>
                <div class="attribute-controls">
                    <button class="btn-upgrade" onclick="upgradeAttribute('strength')" 
                            <?= $skillPoints < 1 ? 'disabled' : '' ?>>
                        <i class="fas fa-plus"></i> +1
                    </button>
                </div>
            </div>
            
            <div class="attribute-item">
                <div class="attribute-name">
                    <i class="fas fa-running" style="color: #f39c12;"></i>
                    Agility
                    <div class="attribute-bonus">+<?= floor(($stats['agility'] ?? 10) / 3) ?> speed</div>
                </div>
                <div class="attribute-value" id="agility-value"><?= $stats['agility'] ?? 10 ?></div>
                <div class="attribute-controls">
                    <button class="btn-upgrade" onclick="upgradeAttribute('agility')" 
                            <?= $skillPoints < 1 ? 'disabled' : '' ?>>
                        <i class="fas fa-plus"></i> +1
                    </button>
                </div>
            </div>
            
            <div class="attribute-item">
                <div class="attribute-name">
                    <i class="fas fa-brain" style="color: #9b59b6;"></i>
                    Intelligence
                    <div class="attribute-bonus">+<?= floor(($stats['intelligence'] ?? 10) / 2) ?> XP bonus</div>
                </div>
                <div class="attribute-value" id="intelligence-value"><?= $stats['intelligence'] ?? 10 ?></div>
                <div class="attribute-controls">
                    <button class="btn-upgrade" onclick="upgradeAttribute('intelligence')" 
                            <?= $skillPoints < 1 ? 'disabled' : '' ?>>
                        <i class="fas fa-plus"></i> +1
                    </button>
                </div>
            </div>
            
            <div class="attribute-item">
                <div class="attribute-name">
                    <i class="fas fa-heart" style="color: #e74c3c;"></i>
                    Endurance
                    <div class="attribute-bonus">+<?= ($stats['endurance'] ?? 10) * 10 ?> health</div>
                </div>
                <div class="attribute-value" id="endurance-value"><?= $stats['endurance'] ?? 10 ?></div>
                <div class="attribute-controls">
                    <button class="btn-upgrade" onclick="upgradeAttribute('endurance')" 
                            <?= $skillPoints < 1 ? 'disabled' : '' ?>>
                        <i class="fas fa-plus"></i> +1
                    </button>
                </div>
            </div>
            
            <div class="attribute-item">
                <div class="attribute-name">
                    <i class="fas fa-dice" style="color: #39ff14;"></i>
                    Luck
                    <div class="attribute-bonus">+<?= floor(($stats['luck'] ?? 10) / 2) ?>% find chance</div>
                </div>
                <div class="attribute-value" id="luck-value"><?= $stats['luck'] ?? 10 ?></div>
                <div class="attribute-controls">
                    <button class="btn-upgrade" onclick="upgradeAttribute('luck')" 
                            <?= $skillPoints < 1 ? 'disabled' : '' ?>>
                        <i class="fas fa-plus"></i> +1
                    </button>
                </div>
            </div>
        </div>
        
        <div class="character-vitals">
            <div class="section-title">
                <i class="fas fa-heartbeat"></i> Vitals
            </div>
            
            <div class="vital-bar">
                <div class="vital-info">
                    <i class="fas fa-heart" style="color: #e74c3c;"></i>
                    <span class="vital-name">Health</span>
                </div>
                <div class="vital-values" id="health-values">
                    <?= $stats['current_health'] ?? 100 ?> / <?= $stats['max_health'] ?? 100 ?>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill health-bar" 
                         style="width: <?= (($stats['current_health'] ?? 100) / ($stats['max_health'] ?? 100)) * 100 ?>%"></div>
                </div>
            </div>
            
            <div class="vital-bar">
                <div class="vital-info">
                    <i class="fas fa-bolt" style="color: #3498db;"></i>
                    <span class="vital-name">Energy</span>
                </div>
                <div class="vital-values" id="energy-values">
                    <?= $stats['current_energy'] ?? 100 ?> / <?= $stats['max_energy'] ?? 100 ?>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill energy-bar" 
                         style="width: <?= (($stats['current_energy'] ?? 100) / ($stats['max_energy'] ?? 100)) * 100 ?>%"></div>
                </div>
            </div>
            
            <div style="margin-top: 30px;">
                <div class="section-title">
                    <i class="fas fa-chart-line"></i> Combat Stats
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div style="background: rgba(57, 255, 20, 0.1); padding: 15px; border-radius: 8px;">
                        <div style="color: #39ff14; font-weight: bold;">Damage</div>
                        <div style="font-size: 1.3em; color: #e8e8e8;">
                            <?= 10 + floor(($stats['strength'] ?? 10) / 2) ?>
                        </div>
                    </div>
                    
                    <div style="background: rgba(57, 255, 20, 0.1); padding: 15px; border-radius: 8px;">
                        <div style="color: #39ff14; font-weight: bold;">Defense</div>
                        <div style="font-size: 1.3em; color: #e8e8e8;">
                            <?= 5 + floor(($stats['endurance'] ?? 10) / 3) ?>
                        </div>
                    </div>
                    
                    <div style="background: rgba(57, 255, 20, 0.1); padding: 15px; border-radius: 8px;">
                        <div style="color: #39ff14; font-weight: bold;">Speed</div>
                        <div style="font-size: 1.3em; color: #e8e8e8;">
                            <?= 10 + floor(($stats['agility'] ?? 10) / 2) ?>
                        </div>
                    </div>
                    
                    <div style="background: rgba(57, 255, 20, 0.1); padding: 15px; border-radius: 8px;">
                        <div style="color: #39ff14; font-weight: bold;">Critical</div>
                        <div style="font-size: 1.3em; color: #e8e8e8;">
                            <?= 5 + floor(($stats['luck'] ?? 10) / 4) ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let skillPoints = <?= $skillPoints ?>;

function upgradeAttribute(attribute) {
    if (skillPoints < 1) {
        showNotification('Not enough skill points!', 'error');
        return;
    }
    
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch('/character/upgrade', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `attribute=${attribute}&points=1`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update display
            document.getElementById(`${attribute}-value`).textContent = data.newValue;
            skillPoints -= data.pointsUsed;
            document.getElementById('skill-points').textContent = skillPoints;
            
            // Update button states
            updateUpgradeButtons();
            
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Upgrade failed!', 'error');
    })
    .finally(() => {
        btn.disabled = skillPoints < 1;
        btn.innerHTML = '<i class="fas fa-plus"></i> +1';
    });
}

function updateUpgradeButtons() {
    const buttons = document.querySelectorAll('.btn-upgrade');
    buttons.forEach(btn => {
        btn.disabled = skillPoints < 1;
    });
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        font-weight: bold;
        z-index: 1000;
        ${type === 'success' ? 'background: #27ae60;' : 'background: #e74c3c;'}
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Auto-refresh energy and health (simulated)
setInterval(() => {
    // This would normally fetch from server
    // For now, just visual feedback
}, 30000);
</script>