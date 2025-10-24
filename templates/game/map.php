<?php include __DIR__ . '/../base.php'; ?>

<style>
.game-interface {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    min-height: 100vh;
    color: #e8e8e8;
}

.game-header {
    background: linear-gradient(90deg, #ff6b35, #ffd23f);
    color: #1a1a1a;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.5);
}

.player-stats {
    display: flex;
    gap: 20px;
    font-weight: bold;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.map-container {
    display: grid;
    grid-template-columns: 300px 1fr 300px;
    height: calc(100vh - 70px);
    gap: 20px;
    padding: 20px;
}

.map-sidebar {
    background: #2d2d2d;
    border: 2px solid #ff6b35;
    border-radius: 10px;
    padding: 20px;
    overflow-y: auto;
}

.map-main {
    background: #1a1a1a;
    border: 2px solid #ff6b35;
    border-radius: 10px;
    position: relative;
    overflow: hidden;
}

.world-map {
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600"><defs><pattern id="grid" width="50" height="50" patternUnits="userSpaceOnUse"><path d="M 50 0 L 0 0 0 50" fill="none" stroke="%23444" stroke-width="1" opacity="0.3"/></pattern></defs><rect width="100%" height="100%" fill="%23222"/><rect width="100%" height="100%" fill="url(%23grid)"/></svg>') center/cover;
    position: relative;
}

.location-marker {
    position: absolute;
    width: 40px;
    height: 40px;
    background: radial-gradient(circle, #ff6b35, #ffd23f);
    border: 3px solid #1a1a1a;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #1a1a1a;
    box-shadow: 0 0 15px rgba(255, 107, 53, 0.5);
}

.location-marker:hover {
    transform: scale(1.2);
    box-shadow: 0 0 25px rgba(255, 107, 53, 0.8);
}

.location-marker.current {
    background: radial-gradient(circle, #39ff14, #2ed615);
    animation: pulse 2s infinite;
}

.location-marker.danger-1 { border-color: #27ae60; }
.location-marker.danger-2 { border-color: #f39c12; }
.location-marker.danger-3 { border-color: #e74c3c; }
.location-marker.danger-4 { border-color: #8e44ad; }
.location-marker.danger-5 { border-color: #2c3e50; }

@keyframes pulse {
    0%, 100% { box-shadow: 0 0 15px rgba(57, 255, 20, 0.5); }
    50% { box-shadow: 0 0 30px rgba(57, 255, 20, 1); }
}

.location-info {
    background: #2d2d2d;
    border: 2px solid #ff6b35;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.location-name {
    color: #ffd23f;
    font-size: 1.5em;
    font-weight: bold;
    margin-bottom: 10px;
}

.location-description {
    color: #e8e8e8;
    margin-bottom: 15px;
    line-height: 1.4;
}

.danger-level {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 15px;
    font-weight: bold;
    font-size: 0.9em;
}

.danger-1 { background: #27ae60; color: white; }
.danger-2 { background: #f39c12; color: white; }
.danger-3 { background: #e74c3c; color: white; }
.danger-4 { background: #8e44ad; color: white; }
.danger-5 { background: #2c3e50; color: white; }

.action-buttons {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn-game {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s;
}

.btn-explore {
    background: linear-gradient(45deg, #ff6b35, #ffd23f);
    color: #1a1a1a;
}

.btn-explore:hover {
    background: linear-gradient(45deg, #ffd23f, #ff6b35);
    transform: translateY(-2px);
}

.btn-travel {
    background: #27ae60;
    color: white;
}

.btn-travel:hover {
    background: #229954;
    transform: translateY(-2px);
}

.resources-list {
    margin-top: 20px;
}

.resource-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: rgba(255, 107, 53, 0.1);
    border-radius: 5px;
    margin-bottom: 5px;
}

.connections-list {
    margin-top: 20px;
}

.connection-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: rgba(57, 255, 20, 0.1);
    border-radius: 5px;
    margin-bottom: 5px;
    cursor: pointer;
    transition: all 0.3s;
}

.connection-item:hover {
    background: rgba(57, 255, 20, 0.2);
}

.travel-cost {
    color: #ffd23f;
    font-weight: bold;
}

.exploration-results {
    background: #2d2d2d;
    border: 2px solid #39ff14;
    border-radius: 10px;
    padding: 20px;
    margin-top: 20px;
    display: none;
}

.results-title {
    color: #39ff14;
    font-size: 1.3em;
    font-weight: bold;
    margin-bottom: 15px;
}

.found-items {
    margin-bottom: 15px;
}

.item-found {
    display: flex;
    justify-content: space-between;
    padding: 5px 10px;
    background: rgba(57, 255, 20, 0.1);
    border-radius: 5px;
    margin-bottom: 5px;
}

.events-list {
    color: #ffd23f;
}

.event-item {
    padding: 5px 0;
    border-bottom: 1px solid #4a4a4a;
}
</style>

<div class="game-interface">
    <div class="game-header">
        <h1><i class="fas fa-map-marked-alt"></i> Wasteland Map</h1>
        <div class="player-stats">
            <div class="stat-item">
                <i class="fas fa-bolt"></i>
                <span id="energy"><?= $userProfile['current_energy'] ?? 100 ?>/<?= $userProfile['max_energy'] ?? 100 ?></span>
            </div>
            <div class="stat-item">
                <i class="fas fa-level-up-alt"></i>
                <span>Level <?= $userProfile['level'] ?? 1 ?></span>
            </div>
            <div class="stat-item">
                <i class="fas fa-map-marker-alt"></i>
                <span id="current-location">Current Location</span>
            </div>
        </div>
    </div>
    
    <div class="map-container">
        <div class="map-sidebar">
            <h3 style="color: #ffd23f; margin-bottom: 20px;">
                <i class="fas fa-info-circle"></i> Location Info
            </h3>
            
            <div id="location-details">
                <div class="location-info">
                    <div class="location-name">Select a location</div>
                    <div class="location-description">Click on a location marker to view details.</div>
                </div>
            </div>
        </div>
        
        <div class="map-main">
            <div class="world-map" id="world-map">
                <?php foreach ($locations as $index => $location): ?>
                    <div class="location-marker danger-<?= $location['danger_level'] ?> <?= $location['id'] == $currentLocation ? 'current' : '' ?>"
                         data-location-id="<?= $location['id'] ?>"
                         style="left: <?= ($index % 4) * 25 + 10 ?>%; top: <?= floor($index / 4) * 20 + 10 ?>%;"
                         title="<?= htmlspecialchars($location['name']) ?>">
                        <?= $location['id'] ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="map-sidebar">
            <h3 style="color: #ffd23f; margin-bottom: 20px;">
                <i class="fas fa-compass"></i> Actions
            </h3>
            
            <div class="action-buttons">
                <button class="btn-game btn-explore" onclick="exploreLocation()">
                    <i class="fas fa-search"></i> Explore
                </button>
            </div>
            
            <div id="exploration-results" class="exploration-results">
                <div class="results-title">
                    <i class="fas fa-gem"></i> Exploration Results
                </div>
                <div id="results-content"></div>
            </div>
            
            <div class="resources-list" id="resources-list">
                <h4 style="color: #ffd23f; margin-bottom: 10px;">Available Resources</h4>
                <div id="resources-content">Select a location to view resources</div>
            </div>
            
            <div class="connections-list" id="connections-list">
                <h4 style="color: #ffd23f; margin-bottom: 10px;">Travel Options</h4>
                <div id="connections-content">Select a location to view connections</div>
            </div>
        </div>
    </div>
</div>

<script>
let currentLocationId = <?= $currentLocation ?>;
let userEnergy = <?= $userProfile['current_energy'] ?? 100 ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    loadLocationDetails(currentLocationId);
    
    // Add click handlers to location markers
    document.querySelectorAll('.location-marker').forEach(marker => {
        marker.addEventListener('click', function() {
            const locationId = this.dataset.locationId;
            loadLocationDetails(locationId);
        });
    });
});

function loadLocationDetails(locationId) {
    fetch(`/map/location/${locationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateLocationInfo(data.location);
                updateResourcesList(data.resources);
                updateConnectionsList(data.connections);
            }
        })
        .catch(error => {
            console.error('Error loading location:', error);
        });
}

function updateLocationInfo(location) {
    const detailsContainer = document.getElementById('location-details');
    detailsContainer.innerHTML = `
        <div class="location-info">
            <div class="location-name">${location.name}</div>
            <div class="location-description">${location.description}</div>
            <div style="margin-top: 10px;">
                <span class="danger-level danger-${location.danger_level}">
                    Danger Level: ${location.danger_level}/5
                </span>
            </div>
            <div style="margin-top: 10px; color: #aaa;">
                Players here: ${location.player_count || 0}
            </div>
        </div>
    `;
    
    document.getElementById('current-location').textContent = location.name;
}

function updateResourcesList(resources) {
    const resourcesContent = document.getElementById('resources-content');
    
    if (resources.length === 0) {
        resourcesContent.innerHTML = '<div style="color: #aaa;">No resources available</div>';
        return;
    }
    
    resourcesContent.innerHTML = resources.map(resource => `
        <div class="resource-item">
            <div>
                <strong>${resource.name}</strong>
                <div style="font-size: 0.9em; color: #aaa;">${resource.description}</div>
            </div>
            <div class="travel-cost">${resource.spawn_rate}%</div>
        </div>
    `).join('');
}

function updateConnectionsList(connections) {
    const connectionsContent = document.getElementById('connections-content');
    
    if (connections.length === 0) {
        connectionsContent.innerHTML = '<div style="color: #aaa;">No travel routes available</div>';
        return;
    }
    
    connectionsContent.innerHTML = connections.map(connection => `
        <div class="connection-item" onclick="travelToLocation(${connection.id})">
            <div>
                <strong>${connection.name}</strong>
                <div style="font-size: 0.9em; color: #aaa;">
                    ${connection.travel_time} minutes travel
                </div>
            </div>
            <div class="travel-cost">${connection.travel_cost} energy</div>
        </div>
    `).join('');
}

function exploreLocation() {
    if (userEnergy < 10) {
        showNotification('Not enough energy to explore!', 'error');
        return;
    }
    
    const exploreBtn = document.querySelector('.btn-explore');
    exploreBtn.disabled = true;
    exploreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exploring...';
    
    fetch('/map/explore', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showExplorationResults(data.results);
            userEnergy -= data.energyUsed;
            updateEnergyDisplay();
            showNotification('Exploration completed!', 'success');
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Exploration failed!', 'error');
    })
    .finally(() => {
        exploreBtn.disabled = false;
        exploreBtn.innerHTML = '<i class="fas fa-search"></i> Explore';
    });
}

function showExplorationResults(results) {
    const resultsContainer = document.getElementById('exploration-results');
    const resultsContent = document.getElementById('results-content');
    
    let content = `
        <div style="margin-bottom: 15px;">
            <strong style="color: #39ff14;">Experience gained: +${results.experience}</strong>
        </div>
    `;
    
    if (results.items.length > 0) {
        content += `
            <div class="found-items">
                <h5 style="color: #ffd23f;">Items Found:</h5>
                ${results.items.map(item => `
                    <div class="item-found">
                        <span>${item.name}</span>
                        <span>x${item.amount}</span>
                    </div>
                `).join('')}
            </div>
        `;
    }
    
    if (results.events.length > 0) {
        content += `
            <div class="events-list">
                <h5 style="color: #ffd23f;">Events:</h5>
                ${results.events.map(event => `
                    <div class="event-item">${event}</div>
                `).join('')}
            </div>
        `;
    }
    
    resultsContent.innerHTML = content;
    resultsContainer.style.display = 'block';
}

function travelToLocation(destinationId) {
    if (!confirm('Are you sure you want to travel to this location?')) {
        return;
    }
    
    fetch('/map/travel', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `destination_id=${destinationId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            // Update current location marker
            document.querySelectorAll('.location-marker').forEach(marker => {
                marker.classList.remove('current');
                if (marker.dataset.locationId == destinationId) {
                    marker.classList.add('current');
                }
            });
            currentLocationId = destinationId;
            loadLocationDetails(destinationId);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Travel failed!', 'error');
    });
}

function updateEnergyDisplay() {
    document.getElementById('energy').textContent = `${userEnergy}/<?= $userProfile['max_energy'] ?? 100 ?>`;
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
</script>