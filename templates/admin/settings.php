<?php $title = 'System Settings'; ?>
<?php include __DIR__ . '/../admin.php'; ?>

<script>
document.getElementById('content').innerHTML = `
    <h1 style="color: #ffd23f; margin-bottom: 30px;">
        <i class="fas fa-cogs"></i> System Settings
    </h1>
    
    <form id="settingsForm" style="max-width: 600px;">
        <div style="background: #2d2d2d; padding: 30px; border-radius: 10px; border: 2px solid #ff6b35;">
            <h2 style="color: #ffd23f; margin-bottom: 20px;">General Settings</h2>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #e8e8e8; margin-bottom: 5px;">Site Name</label>
                <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name']) ?>" 
                       style="width: 100%; padding: 10px; background: #1a1a1a; border: 2px solid #ff6b35; border-radius: 5px; color: #e8e8e8;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #e8e8e8; margin-bottom: 10px;">
                    <input type="checkbox" name="maintenance_mode" value="1" <?= $settings['maintenance_mode'] ? 'checked' : '' ?>
                           style="margin-right: 10px;">
                    Maintenance Mode
                </label>
                <small style="color: #aaa;">When enabled, only admins can access the site</small>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #e8e8e8; margin-bottom: 10px;">
                    <input type="checkbox" name="registration_enabled" value="1" <?= $settings['registration_enabled'] ? 'checked' : '' ?>
                           style="margin-right: 10px;">
                    Registration Enabled
                </label>
                <small style="color: #aaa;">Allow new users to register</small>
            </div>
            
            <h2 style="color: #ffd23f; margin: 30px 0 20px 0;">Game Settings</h2>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #e8e8e8; margin-bottom: 5px;">Max Users per City</label>
                <input type="number" name="max_users_per_city" value="<?= $settings['max_users_per_city'] ?>" min="1" max="10000"
                       style="width: 100%; padding: 10px; background: #1a1a1a; border: 2px solid #ff6b35; border-radius: 5px; color: #e8e8e8;">
            </div>
            
            <div style="margin-bottom: 30px;">
                <label style="display: block; color: #e8e8e8; margin-bottom: 5px;">Daily Quest Limit</label>
                <input type="number" name="daily_quest_limit" value="<?= $settings['daily_quest_limit'] ?>" min="1" max="100"
                       style="width: 100%; padding: 10px; background: #1a1a1a; border: 2px solid #ff6b35; border-radius: 5px; color: #e8e8e8;">
            </div>
            
            <button type="submit" style="background: linear-gradient(90deg, #ff6b35, #ffd23f); color: #1a1a1a; padding: 12px 30px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">
                Save Settings
            </button>
        </div>
    </form>
    
    <div style="margin-top: 30px; background: #2d2d2d; padding: 20px; border-radius: 10px; border: 2px solid #ff6b35;">
        <h2 style="color: #ffd23f; margin-bottom: 15px;">
            <i class="fas fa-database"></i> Database Management
        </h2>
        <div style="color: #e8e8e8; margin-bottom: 20px;">
            <p>Manage database operations and maintenance tasks.</p>
        </div>
        <button onclick="backupDatabase()" style="background: #27ae60; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin-right: 10px; cursor: pointer;">
            Backup Database
        </button>
        <button onclick="optimizeDatabase()" style="background: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
            Optimize Database
        </button>
    </div>
`;

// Settings form handler
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/admin/settings', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('An error occurred', 'error');
    });
});

function backupDatabase() {
    if (confirm('Create a database backup? This may take a few minutes.')) {
        showNotification('Database backup started...', 'success');
        // Implement backup logic
    }
}

function optimizeDatabase() {
    if (confirm('Optimize database tables? This may temporarily slow down the site.')) {
        showNotification('Database optimization started...', 'success');
        // Implement optimization logic
    }
}
</script>