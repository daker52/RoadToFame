<?php include __DIR__ . '/base.php'; ?>

<style>
.admin-layout {
    min-height: 100vh;
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
}

.admin-sidebar {
    width: 250px;
    background: #2d1810;
    min-height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    border-right: 2px solid #ff6b35;
    box-shadow: 2px 0 10px rgba(0,0,0,0.5);
}

.admin-content {
    margin-left: 250px;
    padding: 20px;
}

.admin-header {
    background: linear-gradient(90deg, #ff6b35, #ffd23f);
    color: #1a1a1a;
    padding: 15px 20px;
    font-size: 1.2em;
    font-weight: bold;
    border-bottom: 2px solid #2d1810;
}

.admin-nav {
    padding: 20px 0;
}

.admin-nav-item {
    display: block;
    padding: 15px 20px;
    color: #e8e8e8;
    text-decoration: none;
    transition: all 0.3s;
    border-left: 3px solid transparent;
}

.admin-nav-item:hover,
.admin-nav-item.active {
    background: rgba(255, 107, 53, 0.1);
    border-left: 3px solid #ff6b35;
    color: #ffd23f;
}

.admin-nav-item i {
    margin-right: 10px;
    width: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: linear-gradient(135deg, #2d2d2d, #1a1a1a);
    border: 2px solid #ff6b35;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.stat-value {
    font-size: 2.5em;
    font-weight: bold;
    color: #ffd23f;
    margin-bottom: 10px;
}

.stat-label {
    color: #e8e8e8;
    font-size: 1.1em;
}

.admin-table {
    width: 100%;
    background: #2d2d2d;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.admin-table th {
    background: linear-gradient(90deg, #ff6b35, #ffd23f);
    color: #1a1a1a;
    padding: 15px;
    font-weight: bold;
}

.admin-table td {
    padding: 15px;
    border-bottom: 1px solid #4a4a4a;
    color: #e8e8e8;
}

.admin-table tr:hover {
    background: rgba(255, 107, 53, 0.1);
}

.btn-admin {
    padding: 8px 15px;
    margin: 2px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 0.9em;
}

.btn-ban { background: #e74c3c; color: white; }
.btn-ban:hover { background: #c0392b; }

.btn-unban { background: #27ae60; color: white; }
.btn-unban:hover { background: #229954; }

.btn-delete { background: #8e44ad; color: white; }
.btn-delete:hover { background: #7d3c98; }

.btn-promote { background: #f39c12; color: white; }
.btn-promote:hover { background: #e67e22; }

.search-box {
    width: 100%;
    max-width: 400px;
    padding: 12px;
    background: #1a1a1a;
    border: 2px solid #ff6b35;
    border-radius: 5px;
    color: #e8e8e8;
    margin-bottom: 20px;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
}

.pagination a, .pagination span {
    padding: 10px 15px;
    background: #2d2d2d;
    color: #e8e8e8;
    text-decoration: none;
    border-radius: 5px;
    border: 2px solid #ff6b35;
    transition: all 0.3s;
}

.pagination a:hover {
    background: #ff6b35;
    color: #1a1a1a;
}

.pagination .current {
    background: #ffd23f;
    color: #1a1a1a;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: bold;
}

.status-active { background: #27ae60; color: white; }
.status-banned { background: #e74c3c; color: white; }
.status-admin { background: #f39c12; color: white; }
</style>

<div class="admin-layout">
    <div class="admin-sidebar">
        <div class="admin-header">
            <i class="fas fa-skull"></i> Admin Panel
        </div>
        <nav class="admin-nav">
            <a href="/admin" class="admin-nav-item <?= $_SERVER['REQUEST_URI'] === '/admin' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="/admin/users" class="admin-nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/users') === 0 ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="/admin/settings" class="admin-nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') === 0 ? 'active' : '' ?>">
                <i class="fas fa-cogs"></i> Settings
            </a>
            <a href="/" class="admin-nav-item">
                <i class="fas fa-home"></i> Back to Site
            </a>
        </nav>
    </div>
    
    <div class="admin-content">
        <div id="content">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<script>
// Admin panel JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Handle user action buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-admin')) {
            handleUserAction(e.target);
        }
    });
    
    // Search functionality
    const searchBox = document.querySelector('.search-box');
    if (searchBox) {
        let searchTimeout;
        searchBox.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchTerm = this.value;
                const url = new URL(window.location);
                url.searchParams.set('search', searchTerm);
                url.searchParams.set('page', '1');
                window.location = url.toString();
            }, 500);
        });
    }
});

function handleUserAction(button) {
    const action = button.dataset.action;
    const userId = button.dataset.userId;
    const userName = button.dataset.userName;
    
    if (!confirm(`Are you sure you want to ${action} user "${userName}"?`)) {
        return;
    }
    
    button.disabled = true;
    button.textContent = 'Processing...';
    
    fetch('/admin/user-action', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=${action}&user_id=${userId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
            button.disabled = false;
            button.textContent = button.dataset.originalText || action.charAt(0).toUpperCase() + action.slice(1);
        }
    })
    .catch(error => {
        showNotification('An error occurred', 'error');
        button.disabled = false;
        button.textContent = button.dataset.originalText || action.charAt(0).toUpperCase() + action.slice(1);
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
</script>