<?php include __DIR__ . '/../base.php'; ?>

<style>
.crafting-interface {
    background: linear-gradient(135deg, #2c1810 0%, #3d2817 50%, #2c1810 100%);
    min-height: 100vh;
    color: #e8e8e8;
    padding: 20px;
}

.crafting-header {
    background: linear-gradient(90deg, #d35400, #e67e22);
    color: #fff;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    text-align: center;
}

.crafting-layout {
    display: grid;
    grid-template-columns: 250px 1fr 300px;
    gap: 20px;
    min-height: 600px;
}

/* Workshop Panel */
.workshop-panel {
    background: #3d2817;
    border: 2px solid #d35400;
    border-radius: 10px;
    padding: 20px;
}

.workshop-item {
    background: rgba(211, 84, 0, 0.1);
    border: 1px solid #d35400;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    cursor: pointer;
    transition: all 0.3s;
}

.workshop-item:hover {
    background: rgba(211, 84, 0, 0.2);
    transform: translateY(-2px);
}

.workshop-item.active {
    background: linear-gradient(45deg, #d35400, #e67e22);
    color: #fff;
}

.workshop-level {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
}

.level-bar {
    width: 100px;
    height: 6px;
    background: #1a1a1a;
    border-radius: 3px;
    overflow: hidden;
}

.level-fill {
    height: 100%;
    background: linear-gradient(90deg, #27ae60, #2ecc71);
    transition: width 0.3s;
}

/* Recipes Panel */
.recipes-panel {
    background: #3d2817;
    border: 2px solid #e67e22;
    border-radius: 10px;
    padding: 20px;
}

.recipe-categories {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.category-tab {
    padding: 8px 15px;
    background: rgba(230, 126, 34, 0.2);
    border: 1px solid #e67e22;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 0.9em;
}

.category-tab:hover {
    background: rgba(230, 126, 34, 0.3);
}

.category-tab.active {
    background: linear-gradient(45deg, #e67e22, #f39c12);
    color: #fff;
}

.recipes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    max-height: 500px;
    overflow-y: auto;
}

.recipe-card {
    background: rgba(26, 26, 26, 0.5);
    border: 2px solid #555;
    border-radius: 10px;
    padding: 15px;
    cursor: pointer;
    transition: all 0.3s;
}

.recipe-card:hover {
    border-color: #e67e22;
    transform: scale(1.02);
}

.recipe-card.known {
    border-color: #27ae60;
}

.recipe-card.selected {
    border-color: #f39c12;
    background: rgba(243, 156, 18, 0.1);
}

.recipe-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.recipe-icon {
    width: 40px;
    height: 40px;
    background: #d35400;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
}

.recipe-name {
    font-weight: bold;
    color: #f39c12;
}

.recipe-requirements {
    font-size: 0.85em;
    color: #bdc3c7;
    margin-bottom: 10px;
}

.recipe-materials {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.material-chip {
    background: rgba(52, 152, 219, 0.2);
    border: 1px solid #3498db;
    border-radius: 15px;
    padding: 2px 8px;
    font-size: 0.75em;
    color: #3498db;
}

.material-chip.insufficient {
    background: rgba(231, 76, 60, 0.2);
    border-color: #e74c3c;
    color: #e74c3c;
}

/* Crafting Details Panel */
.details-panel {
    background: #3d2817;
    border: 2px solid #f39c12;
    border-radius: 10px;
    padding: 20px;
}

.recipe-details {
    margin-bottom: 25px;
}

.recipe-title {
    color: #f39c12;
    font-size: 1.3em;
    font-weight: bold;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.recipe-description {
    color: #bdc3c7;
    line-height: 1.4;
    margin-bottom: 15px;
    font-size: 0.9em;
}

.crafting-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 20px;
}

.info-item {
    background: rgba(26, 26, 26, 0.3);
    padding: 10px;
    border-radius: 5px;
    border-left: 3px solid #f39c12;
}

.info-label {
    font-size: 0.8em;
    color: #95a5a6;
    margin-bottom: 5px;
}

.info-value {
    color: #ecf0f1;
    font-weight: bold;
}

.materials-required {
    margin-bottom: 20px;
}

.materials-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.material-requirement {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px;
    background: rgba(26, 26, 26, 0.3);
    border-radius: 5px;
    border-left: 3px solid #3498db;
}

.material-requirement.insufficient {
    border-left-color: #e74c3c;
    background: rgba(231, 76, 60, 0.1);
}

.material-name {
    display: flex;
    align-items: center;
    gap: 8px;
}

.material-icon {
    width: 24px;
    height: 24px;
    background: #3498db;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    color: white;
}

.material-count {
    font-weight: bold;
}

.material-count.sufficient {
    color: #27ae60;
}

.material-count.insufficient {
    color: #e74c3c;
}

.crafting-controls {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.quantity-input {
    width: 80px;
    padding: 8px;
    background: #2c3e50;
    border: 1px solid #34495e;
    border-radius: 5px;
    color: #ecf0f1;
    text-align: center;
}

.btn-craft {
    background: linear-gradient(45deg, #27ae60, #2ecc71);
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-craft:hover {
    background: linear-gradient(45deg, #229954, #27ae60);
    transform: translateY(-2px);
}

.btn-craft:disabled {
    background: #7f8c8d;
    cursor: not-allowed;
    transform: none;
}

.active-projects {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #34495e;
}

.project-item {
    background: rgba(39, 174, 96, 0.1);
    border: 1px solid #27ae60;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
}

.project-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.project-name {
    font-weight: bold;
    color: #27ae60;
}

.project-status {
    font-size: 0.8em;
    color: #f39c12;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #2c3e50;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 10px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #27ae60, #2ecc71);
    transition: width 0.5s;
}

.project-time {
    font-size: 0.85em;
    color: #95a5a6;
    text-align: center;
}

.btn-complete {
    background: linear-gradient(45deg, #f39c12, #e67e22);
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9em;
    margin-top: 10px;
    width: 100%;
}

.btn-complete:hover {
    background: linear-gradient(45deg, #e67e22, #d35400);
}

.btn-complete:disabled {
    background: #7f8c8d;
    cursor: not-allowed;
}

.no-selection {
    text-align: center;
    color: #95a5a6;
    padding: 40px;
    font-style: italic;
}
</style>

<div class="crafting-interface">
    <div class="crafting-header">
        <h1><i class="fas fa-hammer"></i> Crafting Workshop</h1>
        <p>Vytvárej, vylepšuj a experimentuj s předměty v postapokalyptickém světě</p>
    </div>
    
    <div class="crafting-layout">
        <!-- Workshop Panel -->
        <div class="workshop-panel">
            <h3 style="color: #d35400; margin-bottom: 15px;">
                <i class="fas fa-industry"></i> Dílny
            </h3>
            
            <?php if (empty($workshops)): ?>
                <div style="text-align: center; color: #95a5a6; padding: 20px;">
                    <i class="fas fa-tools" style="font-size: 2em; margin-bottom: 10px;"></i>
                    <p>Žádné dílny</p>
                    <p style="font-size: 0.9em;">Postav si první dílnu pro pokročilé crafting</p>
                </div>
            <?php else: ?>
                <?php foreach ($workshops as $workshop): ?>
                    <div class="workshop-item" data-workshop-id="<?= $workshop['id'] ?>">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong><?= ucfirst($workshop['workshop_type']) ?></strong>
                                <div style="font-size: 0.9em; color: #bdc3c7;">
                                    Level <?= $workshop['level'] ?>
                                </div>
                            </div>
                            <i class="fas fa-cog" style="color: #d35400;"></i>
                        </div>
                        
                        <div class="workshop-level">
                            <span style="font-size: 0.8em;">Efficiency: +<?= $workshop['level'] * 10 ?>%</span>
                            <div class="level-bar">
                                <div class="level-fill" style="width: <?= min(100, $workshop['level'] * 20) ?>%"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <button class="btn-craft" style="margin-top: 15px; background: linear-gradient(45deg, #9b59b6, #8e44ad);" onclick="showWorkshopUpgrades()">
                <i class="fas fa-plus"></i> Upgrade Workshop
            </button>
        </div>
        
        <!-- Recipes Panel -->
        <div class="recipes-panel">
            <h3 style="color: #e67e22; margin-bottom: 15px;">
                <i class="fas fa-book"></i> Recepty
            </h3>
            
            <div class="recipe-categories">
                <div class="category-tab active" data-category="all">Vše</div>
                <div class="category-tab" data-category="weapons">Zbraně</div>
                <div class="category-tab" data-category="armor">Brnění</div>
                <div class="category-tab" data-category="consumables">Spotřeba</div>
                <div class="category-tab" data-category="tools">Nástroje</div>
                <div class="category-tab" data-category="materials">Materiály</div>
            </div>
            
            <div class="recipes-grid" id="recipes-grid">
                <?php if (empty($recipes)): ?>
                    <div style="grid-column: 1/-1; text-align: center; color: #95a5a6; padding: 40px;">
                        <i class="fas fa-flask" style="font-size: 3em; margin-bottom: 15px;"></i>
                        <div>Žádné recepty</div>
                        <div style="font-size: 0.9em; margin-top: 10px;">Najdi manuály nebo se uč od NPC</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($recipes as $recipe): ?>
                        <div class="recipe-card <?= $recipe['learned_at'] ? 'known' : '' ?>" 
                             data-recipe-id="<?= $recipe['id'] ?>"
                             data-category="<?= $recipe['category'] ?>"
                             onclick="selectRecipe(<?= $recipe['id'] ?>)">
                            
                            <div class="recipe-header">
                                <div class="recipe-icon">
                                    <?= $recipe['result_item_icon'] ?? '🔧' ?>
                                </div>
                                <div>
                                    <div class="recipe-name"><?= htmlspecialchars($recipe['name']) ?></div>
                                    <?php if (!$recipe['learned_at']): ?>
                                        <div style="font-size: 0.8em; color: #e74c3c;">Neznámý recept</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="recipe-requirements">
                                <div>⚡ Čas: <?= formatTime($recipe['crafting_time']) ?></div>
                                <div>🎯 Skill: <?= $recipe['skill_required'] ?> Lv.<?= $recipe['skill_level'] ?></div>
                            </div>
                            
                            <div class="recipe-materials" id="materials-<?= $recipe['id'] ?>">
                                <!-- Materials will be loaded dynamically -->
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Details Panel -->
        <div class="details-panel">
            <div id="recipe-details">
                <div class="no-selection">
                    <i class="fas fa-mouse-pointer" style="font-size: 2em; margin-bottom: 15px;"></i>
                    <div>Vyber recept pro zobrazení detailů</div>
                </div>
            </div>
            
            <div class="active-projects">
                <h4 style="color: #27ae60; margin-bottom: 15px;">
                    <i class="fas fa-clock"></i> Aktivní projekty
                </h4>
                
                <?php if (empty($activeProjects)): ?>
                    <div style="text-align: center; color: #95a5a6; padding: 20px;">
                        <i class="fas fa-clipboard-list"></i>
                        <div style="margin-top: 10px;">Žádné aktivní projekty</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($activeProjects as $project): ?>
                        <div class="project-item" data-project-id="<?= $project['id'] ?>">
                            <div class="project-header">
                                <div class="project-name">
                                    <?= htmlspecialchars($project['recipe_name']) ?> (<?= $project['quantity'] ?>x)
                                </div>
                                <div class="project-status" id="status-<?= $project['id'] ?>">
                                    V procesu...
                                </div>
                            </div>
                            
                            <div class="progress-bar">
                                <div class="progress-fill" id="progress-<?= $project['id'] ?>" style="width: 0%"></div>
                            </div>
                            
                            <div class="project-time" id="time-<?= $project['id'] ?>">
                                <!-- Time remaining will be calculated by JS -->
                            </div>
                            
                            <button class="btn-complete" id="complete-<?= $project['id'] ?>" 
                                    onclick="completeCrafting(<?= $project['id'] ?>)" disabled>
                                <i class="fas fa-check"></i> Dokončit
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
let selectedRecipeId = null;
let activeWorkshopId = null;
let currentCategory = 'all';

// Recipe selection and filtering
function selectRecipe(recipeId) {
    selectedRecipeId = recipeId;
    
    // Update visual selection
    document.querySelectorAll('.recipe-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    const selectedCard = document.querySelector(`[data-recipe-id="${recipeId}"]`);
    if (selectedCard) {
        selectedCard.classList.add('selected');
    }
    
    loadRecipeDetails(recipeId);
}

function loadRecipeDetails(recipeId) {
    fetch(`/crafting/recipe-info/${recipeId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRecipeDetails(data.recipe, data.materials, data.playerMaterials);
            }
        })
        .catch(error => {
            console.error('Error loading recipe details:', error);
        });
}

function displayRecipeDetails(recipe, materials, playerMaterials) {
    const detailsContainer = document.getElementById('recipe-details');
    
    let canCraft = true;
    let materialsHtml = '';
    
    materials.forEach(material => {
        const playerQuantity = playerMaterials[material.item_id] || 0;
        const sufficient = playerQuantity >= material.quantity;
        if (!sufficient) canCraft = false;
        
        materialsHtml += `
            <div class="material-requirement ${sufficient ? '' : 'insufficient'}">
                <div class="material-name">
                    <div class="material-icon">${material.icon || '📦'}</div>
                    <span>${material.item_name}</span>
                </div>
                <div class="material-count ${sufficient ? 'sufficient' : 'insufficient'}">
                    ${playerQuantity}/${material.quantity}
                </div>
            </div>
        `;
    });
    
    detailsContainer.innerHTML = `
        <div class="recipe-details">
            <div class="recipe-title">
                <span>${recipe.result_item_icon || '🔧'}</span>
                ${recipe.name}
            </div>
            
            <div class="recipe-description">
                ${recipe.description || 'Pokročilý crafting předmět pro přežití v pustině.'}
            </div>
            
            <div class="crafting-info">
                <div class="info-item">
                    <div class="info-label">Čas crafting</div>
                    <div class="info-value">${formatTime(recipe.crafting_time)}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Požadovaný skill</div>
                    <div class="info-value">${recipe.skill_required} Lv.${recipe.skill_level}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Výsledek</div>
                    <div class="info-value">${recipe.result_quantity}x ${recipe.result_item_name}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Zkušenosti</div>
                    <div class="info-value">+${recipe.experience_reward} XP</div>
                </div>
            </div>
            
            <div class="materials-required">
                <h4 style="color: #3498db; margin-bottom: 10px;">Požadované materiály:</h4>
                <div class="materials-list">
                    ${materialsHtml}
                </div>
            </div>
            
            <div class="crafting-controls">
                <div class="quantity-control">
                    <label style="color: #bdc3c7;">Množství:</label>
                    <input type="number" class="quantity-input" value="1" min="1" max="10" id="craft-quantity">
                </div>
                
                <button class="btn-craft" id="start-craft-btn" ${canCraft ? '' : 'disabled'} onclick="startCrafting()">
                    <i class="fas fa-hammer"></i>
                    ${canCraft ? 'Začít Crafting' : 'Nedostatek materiálů'}
                </button>
            </div>
        </div>
    `;
}

function startCrafting() {
    if (!selectedRecipeId) {
        showNotification('Vyber recept', 'error');
        return;
    }
    
    const quantity = parseInt(document.getElementById('craft-quantity').value) || 1;
    
    const formData = new FormData();
    formData.append('recipe_id', selectedRecipeId);
    formData.append('quantity', quantity);
    if (activeWorkshopId) {
        formData.append('workshop_id', activeWorkshopId);
    }
    
    fetch('/crafting/start', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showNotification(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            setTimeout(() => window.location.reload(), 1000);
        }
    })
    .catch(error => {
        showNotification('Chyba při zahajování crafting', 'error');
    });
}

function completeCrafting(projectId) {
    const formData = new FormData();
    formData.append('project_id', projectId);
    
    fetch('/crafting/complete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showNotification(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            setTimeout(() => window.location.reload(), 1000);
        }
    })
    .catch(error => {
        showNotification('Chyba při dokončování crafting', 'error');
    });
}

// Category filtering
document.querySelectorAll('.category-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const category = this.dataset.category;
        
        // Update active tab
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        // Filter recipes
        document.querySelectorAll('.recipe-card').forEach(card => {
            const recipeCategory = card.dataset.category;
            if (category === 'all' || recipeCategory === category) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
        
        currentCategory = category;
    });
});

// Workshop selection
document.querySelectorAll('.workshop-item').forEach(item => {
    item.addEventListener('click', function() {
        const workshopId = this.dataset.workshopId;
        
        // Update active workshop
        document.querySelectorAll('.workshop-item').forEach(w => w.classList.remove('active'));
        this.classList.add('active');
        
        activeWorkshopId = workshopId;
        
        // Reload recipe details with workshop efficiency
        if (selectedRecipeId) {
            loadRecipeDetails(selectedRecipeId);
        }
    });
});

// Project timers
function updateProjectTimers() {
    const projects = document.querySelectorAll('.project-item');
    
    projects.forEach(project => {
        const projectId = project.dataset.projectId;
        const completionTime = project.dataset.completionTime;
        
        if (completionTime) {
            const now = new Date().getTime();
            const completion = new Date(completionTime).getTime();
            const timeLeft = completion - now;
            
            const timeElement = document.getElementById(`time-${projectId}`);
            const progressElement = document.getElementById(`progress-${projectId}`);
            const completeBtn = document.getElementById(`complete-${projectId}`);
            const statusElement = document.getElementById(`status-${projectId}`);
            
            if (timeLeft <= 0) {
                // Project is complete
                timeElement.textContent = 'Dokončeno!';
                progressElement.style.width = '100%';
                completeBtn.disabled = false;
                statusElement.textContent = 'Připraveno';
                statusElement.style.color = '#27ae60';
            } else {
                // Project in progress
                const hours = Math.floor(timeLeft / (1000 * 60 * 60));
                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
                
                timeElement.textContent = `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                // Calculate progress (assume 1 hour total for demo)
                const totalTime = 3600000; // 1 hour in milliseconds
                const elapsed = totalTime - timeLeft;
                const progress = Math.max(0, Math.min(100, (elapsed / totalTime) * 100));
                progressElement.style.width = `${progress}%`;
                
                completeBtn.disabled = true;
                statusElement.textContent = 'V procesu...';
            }
        }
    });
}

// Helper functions
function formatTime(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    
    if (hours > 0) {
        return `${hours}h ${minutes}m`;
    }
    return `${minutes}m`;
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

// Initialize timers
setInterval(updateProjectTimers, 1000);
updateProjectTimers();
</script>

<?php
function formatTime($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    
    if ($hours > 0) {
        return "{$hours}h {$minutes}m";
    }
    return "{$minutes}m";
}
?>