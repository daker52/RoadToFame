// Wasteland Dominion - Admin Panel JavaScript

class AdminPanel {
    constructor() {
        this.currentPage = 'dashboard';
        this.users = [];
        this.settings = {};
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadDashboard();
        
        console.log('🛠️ Admin Panel initialized');
    }
    
    setupEventListeners() {
        // Navigation
        document.addEventListener('click', (e) => {
            if (e.target.matches('.admin-nav-item')) {
                e.preventDefault();
                this.navigate(e.target.getAttribute('href'));
            }
        });
        
        // User actions
        document.addEventListener('click', (e) => {
            if (e.target.matches('.user-action')) {
                this.handleUserAction(e.target);
            }
        });
        
        // Settings form
        document.addEventListener('submit', (e) => {
            if (e.target.matches('#admin-settings-form')) {
                e.preventDefault();
                this.saveSettings(e.target);
            }
        });
        
        // Search functionality
        const searchInput = document.querySelector('#user-search');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.searchUsers(e.target.value);
                }, 300);
            });
        }
        
        // Bulk actions
        document.addEventListener('change', (e) => {
            if (e.target.matches('.user-select-all')) {
                this.toggleAllUsers(e.target.checked);
            }
        });
        
        document.addEventListener('click', (e) => {
            if (e.target.matches('#bulk-action-btn')) {
                this.handleBulkAction();
            }
        });
    }
    
    navigate(href) {
        const page = href.split('/').pop() || 'dashboard';
        this.currentPage = page;
        
        // Update active nav item
        document.querySelectorAll('.admin-nav-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelector(`[href="${href}"]`).classList.add('active');
        
        // Load appropriate content
        switch (page) {
            case 'dashboard':
                this.loadDashboard();
                break;
            case 'users':
                this.loadUsers();
                break;
            case 'settings':
                this.loadSettings();
                break;
            default:
                this.load404();
        }
    }
    
    async loadDashboard() {
        try {
            const response = await fetch('/admin/api/stats');
            const data = await response.json();
            
            if (data.success) {
                this.renderDashboard(data.data);
            }
        } catch (error) {
            console.error('Failed to load dashboard:', error);
            this.showError('Failed to load dashboard data');
        }
    }
    
    renderDashboard(stats) {
        const content = document.getElementById('content');
        content.innerHTML = `
            <div class="admin-dashboard">
                <h1>🎮 Admin Dashboard</h1>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-info">
                            <h3>${stats.total_users || 0}</h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">🎯</div>
                        <div class="stat-info">
                            <h3>${stats.active_users || 0}</h3>
                            <p>Active Users</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">🏆</div>
                        <div class="stat-info">
                            <h3>${stats.completed_quests || 0}</h3>
                            <p>Quests Completed</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">⚔️</div>
                        <div class="stat-info">
                            <h3>${stats.battles_fought || 0}</h3>
                            <p>Battles Fought</p>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-panels">
                    <div class="panel">
                        <h3>Recent Activity</h3>
                        <div class="activity-list">
                            ${(stats.recent_activity || []).map(activity => `
                                <div class="activity-item">
                                    <span class="activity-time">${activity.time}</span>
                                    <span class="activity-message">${activity.message}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    
                    <div class="panel">
                        <h3>System Status</h3>
                        <div class="system-status">
                            <div class="status-item ${stats.database_status === 'ok' ? 'status-ok' : 'status-error'}">
                                <span class="status-indicator"></span>
                                Database: ${stats.database_status || 'Unknown'}
                            </div>
                            <div class="status-item ${stats.cache_status === 'ok' ? 'status-ok' : 'status-error'}">
                                <span class="status-indicator"></span>
                                Cache: ${stats.cache_status || 'Unknown'}
                            </div>
                            <div class="status-item ${stats.websocket_status === 'ok' ? 'status-ok' : 'status-error'}">
                                <span class="status-indicator"></span>
                                WebSocket: ${stats.websocket_status || 'Unknown'}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    async loadUsers() {
        try {
            const response = await fetch('/admin/api/users');
            const data = await response.json();
            
            if (data.success) {
                this.users = data.data;
                this.renderUsers();
            }
        } catch (error) {
            console.error('Failed to load users:', error);
            this.showError('Failed to load users');
        }
    }
    
    renderUsers() {
        const content = document.getElementById('content');
        content.innerHTML = `
            <div class="admin-users">
                <div class="users-header">
                    <h1>👥 User Management</h1>
                    
                    <div class="users-controls">
                        <input type="text" id="user-search" placeholder="Search users..." class="form-control">
                        <select id="bulk-action-select" class="form-control">
                            <option value="">Bulk Actions</option>
                            <option value="ban">Ban Selected</option>
                            <option value="unban">Unban Selected</option>
                            <option value="delete">Delete Selected</option>
                        </select>
                        <button id="bulk-action-btn" class="btn btn-secondary">Apply</button>
                    </div>
                </div>
                
                <div class="users-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" class="user-select-all"></th>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Level</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${this.users.map(user => `
                                <tr class="user-row ${user.is_active ? '' : 'user-banned'}">
                                    <td><input type="checkbox" class="user-select" data-user-id="${user.id}"></td>
                                    <td>${user.id}</td>
                                    <td>
                                        <strong>${user.username}</strong>
                                        ${user.character_name ? `<br><small>${user.character_name}</small>` : ''}
                                    </td>
                                    <td>${user.email}</td>
                                    <td>${user.level || 1}</td>
                                    <td>
                                        <span class="status-badge ${user.is_active ? 'status-active' : 'status-banned'}">
                                            ${user.is_active ? 'Active' : 'Banned'}
                                        </span>
                                    </td>
                                    <td>${new Date(user.created_at).toLocaleDateString()}</td>
                                    <td>
                                        <button class="btn btn-sm user-action" data-action="view" data-user-id="${user.id}">
                                            👁️
                                        </button>
                                        <button class="btn btn-sm user-action" data-action="${user.is_active ? 'ban' : 'unban'}" data-user-id="${user.id}">
                                            ${user.is_active ? '🚫' : '✅'}
                                        </button>
                                        <button class="btn btn-sm btn-danger user-action" data-action="delete" data-user-id="${user.id}">
                                            🗑️
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }
    
    async handleUserAction(button) {
        const action = button.dataset.action;
        const userId = button.dataset.userId;
        
        let confirmed = true;
        
        if (action === 'delete') {
            confirmed = confirm('Are you sure you want to delete this user? This action cannot be undone.');
        } else if (action === 'ban') {
            confirmed = confirm('Are you sure you want to ban this user?');
        }
        
        if (!confirmed) return;
        
        try {
            const response = await fetch('/admin/api/user-action', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action, user_id: userId })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(data.message || 'Action completed successfully');
                this.loadUsers(); // Refresh user list
            } else {
                this.showError(data.message || 'Action failed');
            }
        } catch (error) {
            console.error('User action failed:', error);
            this.showError('Action failed: ' + error.message);
        }
    }
    
    toggleAllUsers(checked) {
        document.querySelectorAll('.user-select').forEach(checkbox => {
            checkbox.checked = checked;
        });
    }
    
    handleBulkAction() {
        const action = document.getElementById('bulk-action-select').value;
        const selectedUsers = Array.from(document.querySelectorAll('.user-select:checked'))
            .map(cb => cb.dataset.userId);
        
        if (!action || selectedUsers.length === 0) {
            this.showError('Please select an action and at least one user');
            return;
        }
        
        if (confirm(`Are you sure you want to ${action} ${selectedUsers.length} user(s)?`)) {
            this.executeBulkAction(action, selectedUsers);
        }
    }
    
    async executeBulkAction(action, userIds) {
        try {
            const response = await fetch('/admin/api/bulk-action', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action, user_ids: userIds })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(`Bulk action completed: ${data.affected} users affected`);
                this.loadUsers();
            } else {
                this.showError(data.message || 'Bulk action failed');
            }
        } catch (error) {
            console.error('Bulk action failed:', error);
            this.showError('Bulk action failed: ' + error.message);
        }
    }
    
    async searchUsers(query) {
        if (query.length < 3) {
            this.loadUsers();
            return;
        }
        
        try {
            const response = await fetch(`/admin/api/users?search=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success) {
                this.users = data.data;
                this.renderUsers();
            }
        } catch (error) {
            console.error('Search failed:', error);
        }
    }
    
    async loadSettings() {
        try {
            const response = await fetch('/admin/api/settings');
            const data = await response.json();
            
            if (data.success) {
                this.settings = data.data;
                this.renderSettings();
            }
        } catch (error) {
            console.error('Failed to load settings:', error);
            this.showError('Failed to load settings');
        }
    }
    
    renderSettings() {
        const content = document.getElementById('content');
        content.innerHTML = `
            <div class="admin-settings">
                <h1>⚙️ System Settings</h1>
                
                <form id="admin-settings-form">
                    <div class="settings-section">
                        <h3>Game Settings</h3>
                        
                        <div class="form-group">
                            <label class="form-label">Max Player Energy</label>
                            <input type="number" name="max_energy" value="${this.settings.max_energy || 100}" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Energy Regen Time (seconds)</label>
                            <input type="number" name="energy_regen_time" value="${this.settings.energy_regen_time || 300}" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Starting Caps</label>
                            <input type="number" name="starting_caps" value="${this.settings.starting_caps || 100}" class="form-control">
                        </div>
                    </div>
                    
                    <div class="settings-section">
                        <h3>Security Settings</h3>
                        
                        <div class="form-group">
                            <label class="form-label">API Rate Limit (per minute)</label>
                            <input type="number" name="api_rate_limit" value="${this.settings.api_rate_limit || 100}" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Session Lifetime (seconds)</label>
                            <input type="number" name="session_lifetime" value="${this.settings.session_lifetime || 3600}" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" name="maintenance_mode" ${this.settings.maintenance_mode ? 'checked' : ''}>
                                Maintenance Mode
                            </label>
                        </div>
                    </div>
                    
                    <div class="settings-actions">
                        <button type="submit" class="btn btn-primary">💾 Save Settings</button>
                        <button type="button" class="btn btn-secondary" onclick="location.reload()">🔄 Reset</button>
                    </div>
                </form>
            </div>
        `;
    }
    
    async saveSettings(form) {
        const formData = new FormData(form);
        const settings = Object.fromEntries(formData.entries());
        
        // Convert checkboxes
        settings.maintenance_mode = form.querySelector('[name="maintenance_mode"]').checked;
        
        try {
            const response = await fetch('/admin/api/settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(settings)
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Settings saved successfully');
                this.settings = settings;
            } else {
                this.showError(data.message || 'Failed to save settings');
            }
        } catch (error) {
            console.error('Failed to save settings:', error);
            this.showError('Failed to save settings: ' + error.message);
        }
    }
    
    load404() {
        const content = document.getElementById('content');
        content.innerHTML = `
            <div class="admin-404">
                <h1>404 - Page Not Found</h1>
                <p>The requested admin page could not be found.</p>
                <button class="btn btn-primary" onclick="window.location.href='/admin'">
                    Return to Dashboard
                </button>
            </div>
        `;
    }
    
    showSuccess(message) {
        this.showNotification(message, 'success');
    }
    
    showError(message) {
        this.showNotification(message, 'error');
    }
    
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `admin-notification notification-${type}`;
        notification.innerHTML = `
            <span class="notification-message">${message}</span>
            <button class="notification-close">&times;</button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
        
        // Manual close
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.remove();
        });
    }
}

// Initialize admin panel when page loads
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.admin-panel')) {
        window.adminPanel = new AdminPanel();
    }
});