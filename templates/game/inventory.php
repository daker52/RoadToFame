<?php include __DIR__ . '/../base.php'; ?>

<style>
.inventory-interface {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    min-height: 100vh;
    color: #e8e8e8;
    padding: 20px;
}

.inventory-header {
    background: linear-gradient(90deg, #ff6b35, #ffd23f);
    color: #1a1a1a;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.capacity-info {
    display: flex;
    gap: 20px;
    align-items: center;
}

.capacity-bar {
    width: 200px;
    height: 10px;
    background: #1a1a1a;
    border-radius: 5px;
    overflow: hidden;
}

.capacity-fill {
    height: 100%;
    background: linear-gradient(90deg, #39ff14, #ff6b35);
    transition: width 0.3s;
}

.inventory-content {
    display: grid;
    grid-template-columns: 200px 1fr 300px;
    gap: 20px;
    min-height: 600px;
}

.inventory-categories {
    background: #2d2d2d;
    border: 2px solid #ff6b35;
    border-radius: 10px;
    padding: 20px;
    height: fit-content;
}

.category-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    margin-bottom: 8px;
}

.category-item:hover {
    background: rgba(255, 107, 53, 0.2);
}

.category-item.active {
    background: linear-gradient(45deg, #ff6b35, #ffd23f);
    color: #1a1a1a;
}

.inventory-grid {
    background: #2d2d2d;
    border: 2px solid #ff6b35;
    border-radius: 10px;
    padding: 20px;
}

.items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 10px;
    max-height: 500px;
    overflow-y: auto;
}

.item-slot {
    width: 80px;
    height: 80px;
    background: rgba(26, 26, 26, 0.5);
    border: 2px solid #4a4a4a;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
    user-select: none;
}

.item-slot.has-item {
    border-color: #ff6b35;
    background: rgba(255, 107, 53, 0.1);
}

.item-slot.has-item:hover {
    transform: scale(1.05);
    box-shadow: 0 0 15px rgba(255, 107, 53, 0.5);
}

.item-slot.rarity-common { border-color: #999; }
.item-slot.rarity-uncommon { border-color: #39ff14; }
.item-slot.rarity-rare { border-color: #3498db; }
.item-slot.rarity-epic { border-color: #9b59b6; }
.item-slot.rarity-legendary { border-color: #f39c12; }

.item-icon {
    font-size: 24px;
    margin-bottom: 5px;
}

.item-quantity {
    font-size: 10px;
    color: #ffd23f;
    font-weight: bold;
}

.item-durability {
    position: absolute;
    bottom: 2px;
    left: 2px;
    right: 2px;
    height: 3px;
    background: #1a1a1a;
    border-radius: 1px;
}

.durability-bar {
    height: 100%;
    background: linear-gradient(90deg, #e74c3c, #f39c12, #39ff14);
    border-radius: 1px;
    transition: width 0.3s;
}

.item-details {
    background: #2d2d2d;
    border: 2px solid #39ff14;
    border-radius: 10px;
    padding: 20px;
    height: fit-content;
}

.equipment-panel {
    margin-top: 20px;
    background: #2d2d2d;
    border: 2px solid #9b59b6;
    border-radius: 10px;
    padding: 20px;
}

.equipment-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-top: 15px;
}

.equipment-slot {
    width: 70px;
    height: 70px;
    background: rgba(26, 26, 26, 0.5);
    border: 2px solid #9b59b6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
}

.equipment-slot.occupied {
    background: rgba(155, 89, 182, 0.2);
    border-color: #ffd23f;
}

.equipment-slot:hover {
    background: rgba(155, 89, 182, 0.3);
}

.slot-label {
    position: absolute;
    bottom: -20px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 10px;
    color: #aaa;
    white-space: nowrap;
}

.item-info-card {
    background: rgba(57, 255, 20, 0.1);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.item-name {
    color: #39ff14;
    font-size: 1.2em;
    font-weight: bold;
    margin-bottom: 10px;
}

.item-description {
    color: #e8e8e8;
    font-size: 0.9em;
    line-height: 1.4;
    margin-bottom: 15px;
}

.item-stats {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stat-label {
    color: #aaa;
}

.stat-value {
    color: #ffd23f;
    font-weight: bold;
}

.item-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn-item {
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s;
    flex: 1;
}

.btn-use {
    background: linear-gradient(45deg, #39ff14, #2ed615);
    color: #1a1a1a;
}

.btn-use:hover {
    background: linear-gradient(45deg, #2ed615, #39ff14);
    transform: translateY(-2px);
}

.btn-equip {
    background: linear-gradient(45deg, #9b59b6, #8e44ad);
    color: white;
}

.btn-equip:hover {
    background: linear-gradient(45deg, #8e44ad, #9b59b6);
    transform: translateY(-2px);
}

.btn-drop {
    background: linear-gradient(45deg, #e74c3c, #c0392b);
    color: white;
}

.btn-drop:hover {
    background: linear-gradient(45deg, #c0392b, #e74c3c);
    transform: translateY(-2px);
}

.drag-overlay {
    position: fixed;
    pointer-events: none;
    z-index: 1000;
    background: rgba(255, 107, 53, 0.8);
    border: 2px solid #ffd23f;
    border-radius: 5px;
    padding: 5px;
    color: #1a1a1a;
    font-weight: bold;
    display: none;
}

.drop-zone {
    border: 2px dashed #39ff14 !important;
    background: rgba(57, 255, 20, 0.1) !important;
}

.context-menu {
    position: fixed;
    background: #2d2d2d;
    border: 2px solid #ff6b35;
    border-radius: 8px;
    padding: 10px;
    z-index: 1001;
    display: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.5);
}

.context-menu-item {
    padding: 8px 15px;
    cursor: pointer;
    transition: all 0.3s;
    border-radius: 5px;
    white-space: nowrap;
}

.context-menu-item:hover {
    background: rgba(255, 107, 53, 0.2);
}

.no-item-selected {
    text-align: center;
    color: #aaa;
    font-style: italic;
    padding: 40px;
}
</style>

<div class="inventory-interface">
    <div class="inventory-header">
        <h1><i class="fas fa-boxes"></i> Inventář</h1>
        <div class="capacity-info">
            <div>
                <strong>Sloty:</strong> <span id="used-slots"><?= $capacity['used_slots'] ?></span>/<span id="max-slots"><?= $capacity['max_slots'] ?></span>
                <div class="capacity-bar">
                    <div class="capacity-fill" style="width: <?= ($capacity['used_slots'] / $capacity['max_slots']) * 100 ?>%"></div>
                </div>
            </div>
            <div>
                <strong>Váha:</strong> <span id="total-weight"><?= number_format($capacity['total_weight']) ?></span>/<span id="max-weight"><?= number_format($capacity['max_weight']) ?></span> kg
                <div class="capacity-bar">
                    <div class="capacity-fill" style="width: <?= ($capacity['total_weight'] / $capacity['max_weight']) * 100 ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="inventory-content">
        <div class="inventory-categories">
            <h3 style="color: #ffd23f; margin-bottom: 15px;">
                <i class="fas fa-filter"></i> Kategorie
            </h3>
            
            <div class="category-item <?= !$currentCategory ? 'active' : '' ?>" onclick="filterByCategory('')">
                <i class="fas fa-th-large"></i>
                <span>Vše</span>
            </div>
            
            <?php foreach ($categories as $category): ?>
                <div class="category-item <?= $currentCategory == $category['id'] ? 'active' : '' ?>" 
                     onclick="filterByCategory('<?= $category['id'] ?>')">
                    <i class="<?= $category['icon'] ?>"></i>
                    <span><?= htmlspecialchars($category['name']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="inventory-grid">
            <h3 style="color: #ffd23f; margin-bottom: 15px;">
                <i class="fas fa-cube"></i> Předměty
            </h3>
            
            <div class="items-grid" id="items-grid">
                <?php if (empty($inventory)): ?>
                    <div style="grid-column: 1/-1; text-align: center; color: #aaa; padding: 40px;">
                        <i class="fas fa-box-open" style="font-size: 3em; margin-bottom: 15px;"></i>
                        <div>Inventář je prázdný</div>
                        <div style="font-size: 0.9em; margin-top: 10px;">Prozkoumej svět a najdi nějaké předměty!</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($inventory as $item): ?>
                        <div class="item-slot has-item rarity-<?= $item['rarity'] ?>" 
                             data-item-id="<?= $item['item_id'] ?>"
                             data-quantity="<?= $item['quantity'] ?>"
                             data-durability="<?= $item['durability'] ?>"
                             onclick="selectItem(<?= $item['item_id'] ?>)"
                             oncontextmenu="showContextMenu(event, <?= $item['item_id'] ?>)"
                             draggable="true"
                             ondragstart="dragStart(event)"
                             ondrop="dropItem(event)"
                             ondragover="allowDrop(event)">
                            
                            <div class="item-icon">
                                <?= $this->getItemIcon($item['category']) ?>
                            </div>
                            
                            <?php if ($item['quantity'] > 1): ?>
                                <div class="item-quantity"><?= $item['quantity'] ?></div>
                            <?php endif; ?>
                            
                            <?php if ($item['durability'] < 100): ?>
                                <div class="item-durability">
                                    <div class="durability-bar" style="width: <?= $item['durability'] ?>%"></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Empty slots for visual grid -->
                    <?php for ($i = count($inventory); $i < $capacity['max_slots']; $i++): ?>
                        <div class="item-slot" 
                             ondrop="dropItem(event)"
                             ondragover="allowDrop(event)">
                        </div>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="item-details" id="item-details">
            <div class="no-item-selected">
                <i class="fas fa-mouse-pointer" style="font-size: 2em; margin-bottom: 15px;"></i>
                <div>Vyber předmět pro zobrazení detailů</div>
            </div>
        </div>
    </div>
    
    <div class="equipment-panel">
        <h3 style="color: #9b59b6; margin-bottom: 15px;">
            <i class="fas fa-user-shield"></i> Vybavení
        </h3>
        
        <div class="equipment-grid">
            <?php 
            $equipmentSlots = [
                'weapon' => ['icon' => 'fas fa-sword', 'label' => 'Zbraň'],
                'helmet' => ['icon' => 'fas fa-hard-hat', 'label' => 'Helma'],
                'armor' => ['icon' => 'fas fa-tshirt', 'label' => 'Brnění'],
                'gloves' => ['icon' => 'fas fa-mitten', 'label' => 'Rukavice'],
                'boots' => ['icon' => 'fas fa-socks', 'label' => 'Boty'],
                'accessory1' => ['icon' => 'fas fa-ring', 'label' => 'Doplněk 1'],
                'accessory2' => ['icon' => 'fas fa-ring', 'label' => 'Doplněk 2'],
                'backpack' => ['icon' => 'fas fa-backpack', 'label' => 'Batoh'],
                'tool' => ['icon' => 'fas fa-tools', 'label' => 'Nástroj']
            ];
            
            foreach ($equipmentSlots as $slot => $config):
                $equippedItem = null;
                foreach ($equipment as $eq) {
                    if ($eq['slot'] === $slot) {
                        $equippedItem = $eq;
                        break;
                    }
                }
            ?>
                <div class="equipment-slot <?= $equippedItem ? 'occupied' : '' ?>" 
                     data-slot="<?= $slot ?>"
                     onclick="<?= $equippedItem ? 'unequipItem(' . $equippedItem['item_id'] . ')' : '' ?>"
                     ondrop="equipFromDrop(event, '<?= $slot ?>')"
                     ondragover="allowDrop(event)">
                    
                    <?php if ($equippedItem): ?>
                        <div class="item-icon">
                            <?= $this->getItemIcon($equippedItem['category'] ?? 'misc') ?>
                        </div>
                        <div class="item-quantity" style="color: #9b59b6;"><?= $equippedItem['quantity'] ?></div>
                    <?php else: ?>
                        <i class="<?= $config['icon'] ?>" style="color: #666; font-size: 20px;"></i>
                    <?php endif; ?>
                    
                    <div class="slot-label"><?= $config['label'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Context Menu -->
<div class="context-menu" id="context-menu">
    <div class="context-menu-item" onclick="useSelectedItem()">
        <i class="fas fa-hand-paper"></i> Použít
    </div>
    <div class="context-menu-item" onclick="equipSelectedItem()">
        <i class="fas fa-user-shield"></i> Nasadit
    </div>
    <div class="context-menu-item" onclick="dropSelectedItem()">
        <i class="fas fa-trash"></i> Zahodit
    </div>
    <div class="context-menu-item" onclick="examineSelectedItem()">
        <i class="fas fa-search"></i> Prozkoumat
    </div>
</div>

<!-- Drag Overlay -->
<div class="drag-overlay" id="drag-overlay"></div>

<script>
let selectedItemId = null;
let draggedItemId = null;
let currentCategory = '<?= $currentCategory ?? "" ?>';

// Item selection
function selectItem(itemId) {
    selectedItemId = itemId;
    
    // Update visual selection
    document.querySelectorAll('.item-slot').forEach(slot => {
        slot.classList.remove('selected');
    });
    
    const selectedSlot = document.querySelector(`[data-item-id="${itemId}"]`);
    if (selectedSlot) {
        selectedSlot.classList.add('selected');
    }
    
    loadItemDetails(itemId);
}

function loadItemDetails(itemId) {
    fetch(`/inventory/item-info/${itemId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayItemDetails(data.item);
            }
        })
        .catch(error => {
            console.error('Error loading item details:', error);
        });
}

function displayItemDetails(item) {
    const detailsContainer = document.getElementById('item-details');
    
    const stats = JSON.parse(item.stats_bonus || '{}');
    const effects = JSON.parse(item.use_effects || '{}');
    
    detailsContainer.innerHTML = `
        <div class="item-info-card">
            <div class="item-name rarity-${item.rarity}">
                ${item.name}
                <span style="font-size: 0.8em; color: #aaa;">(${item.category_name})</span>
            </div>
            
            <div class="item-description">${item.description}</div>
            
            <div class="item-stats">
                <div class="stat-row">
                    <span class="stat-label">Hodnota:</span>
                    <span class="stat-value">${item.value} caps</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Váha:</span>
                    <span class="stat-value">${item.weight} kg</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Trvanlivost:</span>
                    <span class="stat-value">${item.durability || 100}/100</span>
                </div>
                
                ${Object.keys(stats).length > 0 ? `
                    <hr style="margin: 10px 0; border-color: #4a4a4a;">
                    <h4 style="color: #39ff14; margin: 10px 0;">Bonusy:</h4>
                    ${Object.entries(stats).map(([stat, value]) => `
                        <div class="stat-row">
                            <span class="stat-label">${stat}:</span>
                            <span class="stat-value ${value > 0 ? 'positive' : 'negative'}">
                                ${value > 0 ? '+' : ''}${value}
                            </span>
                        </div>
                    `).join('')}
                ` : ''}
                
                ${Object.keys(effects).length > 0 ? `
                    <hr style="margin: 10px 0; border-color: #4a4a4a;">
                    <h4 style="color: #ffd23f; margin: 10px 0;">Efekty:</h4>
                    ${Object.entries(effects).map(([effect, value]) => `
                        <div class="stat-row">
                            <span class="stat-label">${effect}:</span>
                            <span class="stat-value">+${value}</span>
                        </div>
                    `).join('')}
                ` : ''}
            </div>
            
            <div class="item-actions">
                ${item.usable ? `
                    <button class="btn-item btn-use" onclick="useItem(${item.id})">
                        <i class="fas fa-hand-paper"></i> Použít
                    </button>
                ` : ''}
                
                ${item.equipment_slot ? `
                    <button class="btn-item btn-equip" onclick="equipItem(${item.id})">
                        <i class="fas fa-user-shield"></i> Nasadit
                    </button>
                ` : ''}
                
                <button class="btn-item btn-drop" onclick="dropItem(${item.id})">
                    <i class="fas fa-trash"></i> Zahodit
                </button>
            </div>
        </div>
    `;
}

// Item actions
function useItem(itemId) {
    const quantity = parseInt(prompt('Kolik kusů chceš použít?', '1') || '0');
    if (quantity <= 0) return;
    
    fetch('/inventory/use', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `item_id=${itemId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        showNotification(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            setTimeout(() => window.location.reload(), 1000);
        }
    })
    .catch(error => {
        showNotification('Failed to use item', 'error');
    });
}

function equipItem(itemId) {
    fetch('/inventory/equip', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `item_id=${itemId}`
    })
    .then(response => response.json())
    .then(data => {
        showNotification(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            setTimeout(() => window.location.reload(), 1000);
        }
    })
    .catch(error => {
        showNotification('Failed to equip item', 'error');
    });
}

function unequipItem(itemId) {
    fetch('/inventory/unequip', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `item_id=${itemId}`
    })
    .then(response => response.json())
    .then(data => {
        showNotification(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            setTimeout(() => window.location.reload(), 1000);
        }
    })
    .catch(error => {
        showNotification('Failed to unequip item', 'error');
    });
}

function dropItem(itemId) {
    const quantity = parseInt(prompt('Kolik kusů chceš zahodit?', '1') || '0');
    if (quantity <= 0) return;
    
    if (!confirm(`Opravdu chceš zahodit ${quantity}x tento předmět?`)) return;
    
    fetch('/inventory/drop', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `item_id=${itemId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        showNotification(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            setTimeout(() => window.location.reload(), 1000);
        }
    })
    .catch(error => {
        showNotification('Failed to drop item', 'error');
    });
}

// Category filtering
function filterByCategory(category) {
    currentCategory = category;
    const url = new URL(window.location);
    if (category) {
        url.searchParams.set('category', category);
    } else {
        url.searchParams.delete('category');
    }
    window.location = url.toString();
}

// Drag and Drop
function dragStart(event) {
    draggedItemId = event.target.dataset.itemId;
    event.dataTransfer.setData('text/plain', draggedItemId);
    
    const dragOverlay = document.getElementById('drag-overlay');
    dragOverlay.textContent = event.target.querySelector('.item-icon').textContent;
    dragOverlay.style.display = 'block';
    
    // Add visual feedback
    event.target.style.opacity = '0.5';
    
    document.addEventListener('dragend', dragEnd);
}

function dragEnd(event) {
    event.target.style.opacity = '1';
    document.getElementById('drag-overlay').style.display = 'none';
    document.removeEventListener('dragend', dragEnd);
    
    // Remove drop zone highlights
    document.querySelectorAll('.drop-zone').forEach(element => {
        element.classList.remove('drop-zone');
    });
}

function allowDrop(event) {
    event.preventDefault();
    event.target.classList.add('drop-zone');
}

function dropOnSlot(event) {
    event.preventDefault();
    const itemId = event.dataTransfer.getData('text/plain');
    
    // Handle item movement or equipment
    console.log('Dropped item:', itemId, 'on slot:', event.target.dataset.slot);
}

function equipFromDrop(event, slot) {
    event.preventDefault();
    const itemId = event.dataTransfer.getData('text/plain');
    
    if (itemId) {
        equipItem(itemId);
    }
}

// Context Menu
function showContextMenu(event, itemId) {
    event.preventDefault();
    selectedItemId = itemId;
    
    const contextMenu = document.getElementById('context-menu');
    contextMenu.style.display = 'block';
    contextMenu.style.left = event.pageX + 'px';
    contextMenu.style.top = event.pageY + 'px';
    
    // Hide context menu on click elsewhere
    document.addEventListener('click', hideContextMenu);
}

function hideContextMenu() {
    document.getElementById('context-menu').style.display = 'none';
    document.removeEventListener('click', hideContextMenu);
}

function useSelectedItem() {
    if (selectedItemId) useItem(selectedItemId);
    hideContextMenu();
}

function equipSelectedItem() {
    if (selectedItemId) equipItem(selectedItemId);
    hideContextMenu();
}

function dropSelectedItem() {
    if (selectedItemId) dropItem(selectedItemId);
    hideContextMenu();
}

function examineSelectedItem() {
    if (selectedItemId) selectItem(selectedItemId);
    hideContextMenu();
}

// Mouse follow for drag overlay
document.addEventListener('dragover', function(event) {
    const dragOverlay = document.getElementById('drag-overlay');
    if (dragOverlay.style.display === 'block') {
        dragOverlay.style.left = (event.pageX + 10) + 'px';
        dragOverlay.style.top = (event.pageY + 10) + 'px';
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(event) {
    if (!selectedItemId) return;
    
    switch(event.key) {
        case 'u':
        case 'U':
            useItem(selectedItemId);
            break;
        case 'e':
        case 'E':
            equipItem(selectedItemId);
            break;
        case 'Delete':
        case 'd':
        case 'D':
            dropItem(selectedItemId);
            break;
    }
});

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
</script>

<?php
// Helper function for item icons
function getItemIcon($category) {
    $icons = [
        'weapon_melee' => '⚔️',
        'weapon_ranged' => '🔫',
        'weapon_heavy' => '💥',
        'armor_light' => '🧥',
        'armor_medium' => '🛡️',
        'armor_heavy' => '⚒️',
        'consumable' => '🧪',
        'mod' => '🔧',
        'material' => '📦',
        'misc' => '❓'
    ];
    
    return $icons[$category] ?? '📦';
}
?>