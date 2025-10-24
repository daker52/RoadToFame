<?php include __DIR__ . '/../base.php'; ?>

<style>
.quest-interface {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    min-height: 100vh;
    color: #e8e8e8;
    padding: 20px;
}

.quest-header {
    background: linear-gradient(90deg, #ff6b35, #ffd23f);
    color: #1a1a1a;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.quest-tabs {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}

.quest-tab {
    padding: 15px 30px;
    background: #2d2d2d;
    border: 2px solid #ff6b35;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: bold;
}

.quest-tab.active {
    background: linear-gradient(45deg, #ff6b35, #ffd23f);
    color: #1a1a1a;
}

.quest-tab:hover {
    background: rgba(255, 107, 53, 0.2);
}

.quest-content {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 30px;
}

.quest-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.quest-item {
    background: #2d2d2d;
    border: 2px solid #ff6b35;
    border-radius: 10px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.3s;
}

.quest-item:hover {
    background: rgba(255, 107, 53, 0.1);
    transform: translateY(-2px);
}

.quest-item.active {
    border-color: #39ff14;
    background: rgba(57, 255, 20, 0.1);
}

.quest-title {
    color: #ffd23f;
    font-size: 1.3em;
    font-weight: bold;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.quest-description {
    color: #e8e8e8;
    margin-bottom: 15px;
    line-height: 1.4;
}

.quest-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9em;
}

.quest-type {
    padding: 5px 10px;
    border-radius: 15px;
    font-weight: bold;
}

.type-exploration { background: #3498db; color: white; }
.type-combat { background: #e74c3c; color: white; }
.type-collection { background: #f39c12; color: white; }
.type-delivery { background: #27ae60; color: white; }
.type-story { background: #9b59b6; color: white; }

.quest-level {
    color: #ffd23f;
    font-weight: bold;
}

.quest-details {
    background: #2d2d2d;
    border: 2px solid #39ff14;
    border-radius: 10px;
    padding: 25px;
    height: fit-content;
}

.quest-npc {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    padding: 15px;
    background: rgba(57, 255, 20, 0.1);
    border-radius: 8px;
}

.npc-avatar {
    width: 60px;
    height: 60px;
    background: radial-gradient(circle, #39ff14, #2ed615);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5em;
    color: #1a1a1a;
}

.npc-info h4 {
    color: #39ff14;
    margin: 0 0 5px 0;
}

.npc-info p {
    color: #aaa;
    margin: 0;
    font-size: 0.9em;
}

.quest-objectives {
    margin-bottom: 20px;
}

.objectives-title {
    color: #ffd23f;
    font-size: 1.2em;
    font-weight: bold;
    margin-bottom: 15px;
}

.objective-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: rgba(255, 107, 53, 0.1);
    border-radius: 5px;
    margin-bottom: 8px;
}

.objective-text {
    flex: 1;
}

.objective-progress {
    color: #39ff14;
    font-weight: bold;
    margin-left: 10px;
}

.objective-complete {
    background: rgba(57, 255, 20, 0.2);
    color: #39ff14;
}

.quest-rewards {
    margin-bottom: 25px;
}

.rewards-title {
    color: #ffd23f;
    font-size: 1.2em;
    font-weight: bold;
    margin-bottom: 15px;
}

.reward-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: rgba(255, 215, 63, 0.1);
    border-radius: 5px;
    margin-bottom: 5px;
}

.quest-actions {
    display: flex;
    gap: 10px;
}

.btn-quest {
    padding: 12px 25px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s;
    flex: 1;
}

.btn-accept {
    background: linear-gradient(45deg, #27ae60, #2ecc71);
    color: white;
}

.btn-accept:hover {
    background: linear-gradient(45deg, #2ecc71, #27ae60);
    transform: translateY(-2px);
}

.btn-abandon {
    background: linear-gradient(45deg, #e74c3c, #c0392b);
    color: white;
}

.btn-abandon:hover {
    background: linear-gradient(45deg, #c0392b, #e74c3c);
    transform: translateY(-2px);
}

.btn-complete {
    background: linear-gradient(45deg, #f39c12, #e67e22);
    color: white;
}

.btn-complete:hover {
    background: linear-gradient(45deg, #e67e22, #f39c12);
    transform: translateY(-2px);
}

.btn-quest:disabled {
    background: #666;
    color: #999;
    cursor: not-allowed;
    transform: none;
}

.no-quests {
    text-align: center;
    color: #aaa;
    font-style: italic;
    padding: 40px;
}

.quest-progress-bar {
    width: 100%;
    height: 8px;
    background: #1a1a1a;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 10px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #39ff14, #2ed615);
    transition: width 0.3s;
}

.quest-status {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: bold;
    margin-left: 10px;
}

.status-available { background: #27ae60; color: white; }
.status-in-progress { background: #f39c12; color: white; }
.status-completed { background: #39ff14; color: #1a1a1a; }
.status-failed { background: #e74c3c; color: white; }
</style>

<div class="quest-interface">
    <div class="quest-header">
        <h1><i class="fas fa-scroll"></i> Quest Journal</h1>
        <div style="color: #1a1a1a; font-weight: bold;">
            Active Quests: <?= count($activeQuests) ?> | Available: <?= count($availableQuests) ?>
        </div>
    </div>
    
    <div class="quest-tabs">
        <div class="quest-tab active" onclick="switchTab('available')">
            <i class="fas fa-list"></i> Available Quests
        </div>
        <div class="quest-tab" onclick="switchTab('active')">
            <i class="fas fa-tasks"></i> Active Quests
        </div>
    </div>
    
    <div class="quest-content">
        <div class="quest-list" id="quest-list">
            <!-- Available Quests -->
            <div id="available-quests" class="quest-section">
                <?php if (empty($availableQuests)): ?>
                    <div class="no-quests">
                        <i class="fas fa-exclamation-circle" style="font-size: 2em; margin-bottom: 15px;"></i>
                        <div>No available quests at your current location.</div>
                        <div style="margin-top: 10px; color: #666;">Try exploring other areas or completing current quests.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($availableQuests as $quest): ?>
                        <div class="quest-item" onclick="selectQuest(<?= $quest['id'] ?>, 'available')" data-quest-id="<?= $quest['id'] ?>">
                            <div class="quest-title">
                                <i class="fas fa-star"></i>
                                <?= htmlspecialchars($quest['title']) ?>
                                <span class="quest-type type-<?= $quest['type_name'] ?>">
                                    <?= ucfirst($quest['type_name']) ?>
                                </span>
                            </div>
                            <div class="quest-description">
                                <?= htmlspecialchars($quest['description']) ?>
                            </div>
                            <div class="quest-meta">
                                <div class="quest-level">Level <?= $quest['required_level'] ?>+</div>
                                <?php if ($quest['npc_name']): ?>
                                    <div style="color: #39ff14;">
                                        <i class="fas fa-user"></i> <?= htmlspecialchars($quest['npc_name']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Active Quests -->
            <div id="active-quests" class="quest-section" style="display: none;">
                <?php if (empty($activeQuests)): ?>
                    <div class="no-quests">
                        <i class="fas fa-clipboard-list" style="font-size: 2em; margin-bottom: 15px;"></i>
                        <div>No active quests.</div>
                        <div style="margin-top: 10px; color: #666;">Accept some quests to start your adventure!</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($activeQuests as $quest): ?>
                        <div class="quest-item" onclick="selectQuest(<?= $quest['quest_id'] ?>, 'active')" data-quest-id="<?= $quest['quest_id'] ?>">
                            <div class="quest-title">
                                <i class="fas fa-play"></i>
                                <?= htmlspecialchars($quest['title']) ?>
                                <span class="quest-status status-<?= $quest['status'] ?>">
                                    <?= ucfirst($quest['status']) ?>
                                </span>
                            </div>
                            <div class="quest-description">
                                <?= htmlspecialchars($quest['description']) ?>
                            </div>
                            <div class="quest-meta">
                                <div style="color: #39ff14;">
                                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($quest['location_name']) ?>
                                </div>
                                <div style="color: #aaa;">
                                    Started: <?= date('M j', strtotime($quest['started_at'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="quest-details" id="quest-details">
            <div style="text-align: center; color: #aaa; padding: 40px;">
                <i class="fas fa-mouse-pointer" style="font-size: 2em; margin-bottom: 15px;"></i>
                <div>Select a quest to view details</div>
            </div>
        </div>
    </div>
</div>

<script>
let currentTab = 'available';
let selectedQuest = null;

function switchTab(tab) {
    currentTab = tab;
    
    // Update tab appearance
    document.querySelectorAll('.quest-tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');
    
    // Show/hide quest sections
    document.getElementById('available-quests').style.display = tab === 'available' ? 'block' : 'none';
    document.getElementById('active-quests').style.display = tab === 'active' ? 'block' : 'none';
    
    // Clear selection
    selectedQuest = null;
    document.querySelectorAll('.quest-item').forEach(item => item.classList.remove('active'));
    document.getElementById('quest-details').innerHTML = `
        <div style="text-align: center; color: #aaa; padding: 40px;">
            <i class="fas fa-mouse-pointer" style="font-size: 2em; margin-bottom: 15px;"></i>
            <div>Select a quest to view details</div>
        </div>
    `;
}

function selectQuest(questId, type) {
    selectedQuest = { id: questId, type: type };
    
    // Update visual selection
    document.querySelectorAll('.quest-item').forEach(item => item.classList.remove('active'));
    event.currentTarget.classList.add('active');
    
    // Load quest details
    loadQuestDetails(questId);
}

function loadQuestDetails(questId) {
    fetch(`/quests/quest/${questId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayQuestDetails(data.quest, data.instance, data.objectives);
            }
        })
        .catch(error => {
            console.error('Error loading quest details:', error);
        });
}

function displayQuestDetails(quest, instance, objectives) {
    const detailsContainer = document.getElementById('quest-details');
    
    let objectivesHtml = '';
    if (objectives && objectives.length > 0) {
        const progress = instance ? JSON.parse(instance.progress || '{}') : {};
        
        objectivesHtml = `
            <div class="quest-objectives">
                <div class="objectives-title">
                    <i class="fas fa-tasks"></i> Objectives
                </div>
                ${objectives.map(obj => {
                    const current = progress[obj.id] || 0;
                    const isComplete = current >= obj.required;
                    return `
                        <div class="objective-item ${isComplete ? 'objective-complete' : ''}">
                            <div class="objective-text">
                                ${isComplete ? '<i class="fas fa-check"></i>' : '<i class="fas fa-circle"></i>'} 
                                ${obj.description}
                            </div>
                            <div class="objective-progress">
                                ${current}/${obj.required}
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
    }
    
    let rewardsHtml = '';
    if (quest.rewards) {
        const rewards = JSON.parse(quest.rewards);
        rewardsHtml = `
            <div class="quest-rewards">
                <div class="rewards-title">
                    <i class="fas fa-gift"></i> Rewards
                </div>
                ${rewards.experience ? `
                    <div class="reward-item">
                        <span><i class="fas fa-star"></i> Experience</span>
                        <span>+${rewards.experience} XP</span>
                    </div>
                ` : ''}
                ${rewards.items ? Object.entries(rewards.items).map(([itemId, qty]) => `
                    <div class="reward-item">
                        <span><i class="fas fa-box"></i> Item #${itemId}</span>
                        <span>x${qty}</span>
                    </div>
                `).join('') : ''}
            </div>
        `;
    }
    
    let actionsHtml = '';
    if (instance && instance.status === 'in_progress') {
        const canComplete = objectives.every(obj => {
            const progress = JSON.parse(instance.progress || '{}');
            return (progress[obj.id] || 0) >= obj.required;
        });
        
        actionsHtml = `
            <div class="quest-actions">
                <button class="btn-quest btn-complete" onclick="completeQuest(${quest.id})" ${!canComplete ? 'disabled' : ''}>
                    <i class="fas fa-check"></i> Complete Quest
                </button>
                <button class="btn-quest btn-abandon" onclick="abandonQuest(${quest.id})">
                    <i class="fas fa-times"></i> Abandon
                </button>
            </div>
        `;
    } else if (!instance || instance.status === 'available') {
        actionsHtml = `
            <div class="quest-actions">
                <button class="btn-quest btn-accept" onclick="acceptQuest(${quest.id})">
                    <i class="fas fa-plus"></i> Accept Quest
                </button>
            </div>
        `;
    }
    
    detailsContainer.innerHTML = `
        <div class="quest-title" style="margin-bottom: 20px;">
            ${quest.title}
            <span class="quest-type type-${quest.type_name}">${quest.type_name}</span>
        </div>
        
        ${quest.npc_name ? `
            <div class="quest-npc">
                <div class="npc-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="npc-info">
                    <h4>${quest.npc_name}</h4>
                    <p>${quest.npc_description || 'A wasteland survivor'}</p>
                </div>
            </div>
        ` : ''}
        
        <div style="color: #e8e8e8; margin-bottom: 20px; line-height: 1.4;">
            ${quest.description}
        </div>
        
        ${objectivesHtml}
        ${rewardsHtml}
        ${actionsHtml}
    `;
}

function acceptQuest(questId) {
    fetch('/quests/accept', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `quest_id=${questId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            // Refresh the page to update quest lists
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Failed to accept quest', 'error');
    });
}

function abandonQuest(questId) {
    if (!confirm('Are you sure you want to abandon this quest? All progress will be lost.')) {
        return;
    }
    
    fetch('/quests/abandon', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `quest_id=${questId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Failed to abandon quest', 'error');
    });
}

function completeQuest(questId) {
    fetch('/quests/complete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `quest_id=${questId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Quest completed! ' + JSON.stringify(data.rewards), 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Failed to complete quest', 'error');
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
        max-width: 400px;
        ${type === 'success' ? 'background: #27ae60;' : 'background: #e74c3c;'}
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 4000);
}
</script>