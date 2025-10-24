// Wasteland Dominion - Game Specific JavaScript

class GameInterface {
    constructor() {
        this.currentLocation = null;
        this.inventory = null;
        this.combat = null;
        
        this.init();
    }
    
    init() {
        this.setupGameEventListeners();
        this.loadGameData();
        
        console.log('🎯 Game Interface initialized');
    }
    
    setupGameEventListeners() {
        // Map interactions
        document.addEventListener('click', (e) => {
            if (e.target.matches('.map-location')) {
                this.handleLocationClick(e.target);
            }
        });
        
        // Inventory interactions  
        document.addEventListener('click', (e) => {
            if (e.target.matches('.inventory-item')) {
                this.handleItemClick(e.target);
            }
        });
        
        // Combat actions
        document.addEventListener('click', (e) => {
            if (e.target.matches('.combat-action')) {
                this.handleCombatAction(e.target);
            }
        });
        
        // Quest interactions
        document.addEventListener('click', (e) => {
            if (e.target.matches('.quest-action')) {
                this.handleQuestAction(e.target);
            }
        });
    }
    
    async loadGameData() {
        await Promise.all([
            this.loadInventory(),
            this.loadCurrentLocation(),
            this.loadActiveQuests()
        ]);
    }
    
    async loadInventory() {
        try {
            const response = await fetch('/api/player/inventory');
            const data = await response.json();
            
            if (data.success) {
                this.inventory = data.data;
                this.updateInventoryUI();
            }
        } catch (error) {
            console.error('Failed to load inventory:', error);
        }
    }
    
    updateInventoryUI() {
        const inventoryGrid = document.querySelector('.inventory-grid');
        if (!inventoryGrid || !this.inventory) return;
        
        inventoryGrid.innerHTML = '';
        
        // Create inventory slots
        for (let i = 0; i < 25; i++) {
            const slot = document.createElement('div');
            slot.className = 'inventory-slot';
            slot.dataset.slotId = i;
            
            // Find item for this slot
            const item = this.inventory.items.find(item => item.slot === i);
            
            if (item) {
                slot.classList.add('occupied');
                if (item.equipped) {
                    slot.classList.add('equipped');
                }
                
                slot.innerHTML = `
                    <div class="item-icon">${this.getItemIcon(item.type)}</div>
                    ${item.quantity > 1 ? `<div class="item-quantity">${item.quantity}</div>` : ''}
                `;
                
                slot.dataset.itemId = item.id;
                slot.dataset.tooltip = `${item.name} - ${item.description}`;
            }
            
            inventoryGrid.appendChild(slot);
        }
    }
    
    getItemIcon(itemType) {
        const icons = {
            weapon: '⚔️',
            armor: '🛡️',
            consumable: '🍃',
            resource: '⚙️',
            key: '🔑',
            misc: '📦'
        };
        
        return icons[itemType] || '❓';
    }
    
    handleLocationClick(locationElement) {
        const locationId = locationElement.dataset.locationId;
        const locationName = locationElement.dataset.locationName;
        
        if (confirm(`Travel to ${locationName}?`)) {
            this.travelToLocation(locationId);
        }
    }
    
    async travelToLocation(locationId) {
        try {
            const response = await fetch('/api/player/location/change', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ location_id: parseInt(locationId) })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Travel successful!', 'success');
                this.updatePlayerLocation(data.data.new_location);
                
                // Update energy display
                if (data.data.current_energy !== undefined) {
                    this.updateEnergyBar(data.data.current_energy);
                }
            } else {
                this.showNotification(data.message || 'Travel failed', 'error');
            }
        } catch (error) {
            console.error('Travel failed:', error);
            this.showNotification('Travel failed: ' + error.message, 'error');
        }
    }
    
    updatePlayerLocation(newLocation) {
        // Update current location marker on map
        document.querySelectorAll('.map-location').forEach(loc => {
            loc.classList.remove('current');
        });
        
        const newLocationElement = document.querySelector(`[data-location-id="${newLocation.id}"]`);
        if (newLocationElement) {
            newLocationElement.classList.add('current');
        }
        
        // Update location name in UI
        const locationDisplay = document.querySelector('.current-location');
        if (locationDisplay) {
            locationDisplay.textContent = newLocation.name;
        }
    }
    
    updateEnergyBar(currentEnergy) {
        const energyBar = document.querySelector('.stat-fill.energy');
        if (energyBar) {
            const maxEnergy = 100; // Should come from config
            const energyPercent = (currentEnergy / maxEnergy) * 100;
            energyBar.style.width = `${energyPercent}%`;
        }
        
        const energyText = document.querySelector('.energy-text');
        if (energyText) {
            energyText.textContent = currentEnergy;
        }
    }
    
    handleItemClick(itemElement) {
        const itemId = itemElement.dataset.itemId;
        const itemType = itemElement.dataset.itemType;
        
        // Show context menu
        this.showItemContextMenu(itemElement, itemId, itemType);
    }
    
    showItemContextMenu(element, itemId, itemType) {
        const contextMenu = document.createElement('div');
        contextMenu.className = 'context-menu';
        
        const actions = this.getItemActions(itemType);
        
        contextMenu.innerHTML = actions.map(action => 
            `<div class="context-menu-item" data-action="${action.id}" data-item="${itemId}">
                ${action.label}
            </div>`
        ).join('');
        
        document.body.appendChild(contextMenu);
        
        // Position menu
        const rect = element.getBoundingClientRect();
        contextMenu.style.left = `${rect.right + 10}px`;
        contextMenu.style.top = `${rect.top}px`;
        
        // Handle clicks
        contextMenu.addEventListener('click', (e) => {
            const action = e.target.dataset.action;
            const itemId = e.target.dataset.item;
            
            if (action && itemId) {
                this.executeItemAction(action, itemId);
            }
            
            contextMenu.remove();
        });
        
        // Close on outside click
        setTimeout(() => {
            document.addEventListener('click', () => {
                if (contextMenu.parentNode) {
                    contextMenu.remove();
                }
            }, { once: true });
        }, 100);
    }
    
    getItemActions(itemType) {
        const actions = [
            { id: 'inspect', label: '🔍 Inspect' },
            { id: 'drop', label: '🗑️ Drop' }
        ];
        
        if (itemType === 'weapon' || itemType === 'armor') {
            actions.unshift({ id: 'equip', label: '⚡ Equip' });
        } else if (itemType === 'consumable') {
            actions.unshift({ id: 'use', label: '🍃 Use' });
        }
        
        return actions;
    }
    
    async executeItemAction(action, itemId) {
        try {
            const response = await fetch(`/api/inventory/${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ item_id: parseInt(itemId) })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification(data.message || 'Action completed', 'success');
                this.loadInventory(); // Refresh inventory
            } else {
                this.showNotification(data.message || 'Action failed', 'error');
            }
        } catch (error) {
            console.error('Item action failed:', error);
            this.showNotification('Action failed: ' + error.message, 'error');
        }
    }
    
    handleCombatAction(actionElement) {
        const action = actionElement.dataset.action;
        const target = actionElement.dataset.target;
        
        this.executeCombatAction(action, target);
    }
    
    async executeCombatAction(action, target) {
        try {
            const response = await fetch(`/api/combat/${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ target: target })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.updateCombatLog(data.data.log);
                this.updateCombatState(data.data.state);
            }
        } catch (error) {
            console.error('Combat action failed:', error);
        }
    }
    
    updateCombatLog(logEntries) {
        const combatLog = document.querySelector('.combat-log');
        if (!combatLog) return;
        
        logEntries.forEach(entry => {
            const logElement = document.createElement('div');
            logElement.className = `log-entry ${entry.type}`;
            logElement.textContent = entry.message;
            
            combatLog.appendChild(logElement);
        });
        
        // Scroll to bottom
        combatLog.scrollTop = combatLog.scrollHeight;
    }
    
    updateCombatState(state) {
        // Update health bars
        const playerHealth = document.querySelector('.player .health-fill');
        if (playerHealth && state.player) {
            const healthPercent = (state.player.health / state.player.max_health) * 100;
            playerHealth.style.width = `${healthPercent}%`;
        }
        
        const enemyHealth = document.querySelector('.enemy .health-fill');
        if (enemyHealth && state.enemy) {
            const healthPercent = (state.enemy.health / state.enemy.max_health) * 100;
            enemyHealth.style.width = `${healthPercent}%`;
        }
        
        // Check for combat end
        if (state.ended) {
            this.handleCombatEnd(state.result);
        }
    }
    
    handleCombatEnd(result) {
        const message = result.victory ? 'Victory!' : 'Defeat!';
        const type = result.victory ? 'success' : 'error';
        
        this.showNotification(message, type);
        
        if (result.rewards) {
            this.showRewards(result.rewards);
        }
        
        // Return to exploration after delay
        setTimeout(() => {
            window.location.reload();
        }, 3000);
    }
    
    showRewards(rewards) {
        const rewardModal = document.createElement('div');
        rewardModal.className = 'modal rewards-modal';
        rewardModal.innerHTML = `
            <div class="modal-content">
                <h3>🏆 Rewards Earned!</h3>
                <div class="rewards-list">
                    ${rewards.experience ? `<div>📈 Experience: +${rewards.experience}</div>` : ''}
                    ${rewards.caps ? `<div>💰 Caps: +${rewards.caps}</div>` : ''}
                    ${rewards.items ? rewards.items.map(item => `<div>📦 ${item.name} x${item.quantity}</div>`).join('') : ''}
                </div>
                <button class="btn btn-primary" onclick="this.parentNode.parentNode.remove()">
                    Continue
                </button>
            </div>
        `;
        
        document.body.appendChild(rewardModal);
    }
    
    handleQuestAction(actionElement) {
        const action = actionElement.dataset.action;
        const questId = actionElement.dataset.questId;
        
        this.executeQuestAction(action, questId);
    }
    
    async executeQuestAction(action, questId) {
        try {
            const response = await fetch(`/api/quests/${action}/${questId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification(data.message || 'Quest updated', 'success');
                this.loadActiveQuests(); // Refresh quest list
            }
        } catch (error) {
            console.error('Quest action failed:', error);
        }
    }
    
    async loadActiveQuests() {
        try {
            const response = await fetch('/api/quests/active');
            const data = await response.json();
            
            if (data.success) {
                this.updateQuestUI(data.data);
            }
        } catch (error) {
            console.error('Failed to load quests:', error);
        }
    }
    
    updateQuestUI(quests) {
        const questList = document.querySelector('.quest-list');
        if (!questList) return;
        
        questList.innerHTML = quests.map(quest => `
            <div class="quest-item ${quest.status}">
                <div class="quest-title">
                    ${quest.title}
                    <span class="quest-type">${quest.type}</span>
                </div>
                <div class="quest-description">${quest.description}</div>
                <div class="quest-progress">
                    <div class="quest-progress-fill" style="width: ${quest.progress}%"></div>
                </div>
                <div class="quest-actions">
                    ${quest.can_complete ? 
                        `<button class="btn btn-success quest-action" data-action="complete" data-quest-id="${quest.id}">
                            Complete Quest
                        </button>` : ''
                    }
                    <button class="btn btn-secondary quest-action" data-action="abandon" data-quest-id="${quest.id}">
                        Abandon
                    </button>
                </div>
            </div>
        `).join('');
    }
    
    async loadCurrentLocation() {
        // Implementation for loading current location data
        console.log('Loading current location...');
    }
    
    showNotification(message, type = 'info') {
        // Use the main game's notification system
        if (window.game && window.game.showNotification) {
            window.game.showNotification(message, type);
        } else {
            console.log(`${type.toUpperCase()}: ${message}`);
        }
    }
}

// Initialize game interface
document.addEventListener('DOMContentLoaded', () => {
    window.gameInterface = new GameInterface();
});