<?php $title = 'Admin Dashboard'; ?>
<?php include __DIR__ . '/../admin.php'; ?>

<script>
document.getElementById('content').innerHTML = `
    <h1 style="color: #ffd23f; margin-bottom: 30px;">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_users'] ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['active_users'] ?></div>
            <div class="stat-label">Active Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['banned_users'] ?></div>
            <div class="stat-label">Banned Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['new_users_today'] ?></div>
            <div class="stat-label">New Today</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['online_users'] ?></div>
            <div class="stat-label">Online Now</div>
        </div>
    </div>
    
    <div style="background: #2d2d2d; padding: 20px; border-radius: 10px; border: 2px solid #ff6b35;">
        <h2 style="color: #ffd23f; margin-bottom: 15px;">
            <i class="fas fa-info-circle"></i> System Status
        </h2>
        <div style="color: #e8e8e8;">
            <p><strong>Server:</strong> Running normally</p>
            <p><strong>Database:</strong> Connected</p>
            <p><strong>Last Backup:</strong> <?= date('Y-m-d H:i:s') ?></p>
            <p><strong>Uptime:</strong> 24 hours, 15 minutes</p>
        </div>
    </div>
    
    <div style="margin-top: 30px; background: #2d2d2d; padding: 20px; border-radius: 10px; border: 2px solid #ff6b35;">
        <h2 style="color: #ffd23f; margin-bottom: 15px;">
            <i class="fas fa-chart-line"></i> Recent Activity
        </h2>
        <div style="color: #e8e8e8;">
            <p>• New user registration: <?= date('H:i:s') ?></p>
            <p>• System backup completed: <?= date('H:i:s', strtotime('-1 hour')) ?></p>
            <p>• User login: <?= date('H:i:s', strtotime('-2 minutes')) ?></p>
            <p>• Quest completed: <?= date('H:i:s', strtotime('-5 minutes')) ?></p>
        </div>
    </div>
`;
</script>