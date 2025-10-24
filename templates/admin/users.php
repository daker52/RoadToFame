<?php $title = 'User Management'; ?>
<?php include __DIR__ . '/../admin.php'; ?>

<script>
document.getElementById('content').innerHTML = `
    <h1 style="color: #ffd23f; margin-bottom: 30px;">
        <i class="fas fa-users"></i> User Management
    </h1>
    
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <input type="text" class="search-box" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>">
        <div style="color: #e8e8e8;">
            Total: <?= $totalUsers ?> users
        </div>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Status</th>
                <th>Joined</th>
                <th>Last Login</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td>
                    <?php if ($user['is_banned']): ?>
                        <span class="status-badge status-banned">Banned</span>
                    <?php elseif ($user['is_admin']): ?>
                        <span class="status-badge status-admin">Admin</span>
                    <?php else: ?>
                        <span class="status-badge status-active">Active</span>
                    <?php endif; ?>
                </td>
                <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                <td><?= $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : 'Never' ?></td>
                <td>
                    <?php if (!$user['is_banned']): ?>
                        <button class="btn-admin btn-ban" 
                                data-action="ban" 
                                data-user-id="<?= $user['id'] ?>" 
                                data-user-name="<?= htmlspecialchars($user['username']) ?>">
                            Ban
                        </button>
                    <?php else: ?>
                        <button class="btn-admin btn-unban" 
                                data-action="unban" 
                                data-user-id="<?= $user['id'] ?>" 
                                data-user-name="<?= htmlspecialchars($user['username']) ?>">
                            Unban
                        </button>
                    <?php endif; ?>
                    
                    <?php if (!$user['is_admin']): ?>
                        <button class="btn-admin btn-promote" 
                                data-action="promote" 
                                data-user-id="<?= $user['id'] ?>" 
                                data-user-name="<?= htmlspecialchars($user['username']) ?>">
                            Promote
                        </button>
                    <?php else: ?>
                        <button class="btn-admin btn-delete" 
                                data-action="demote" 
                                data-user-id="<?= $user['id'] ?>" 
                                data-user-name="<?= htmlspecialchars($user['username']) ?>">
                            Demote
                        </button>
                    <?php endif; ?>
                    
                    <button class="btn-admin btn-delete" 
                            data-action="delete" 
                            data-user-id="<?= $user['id'] ?>" 
                            data-user-name="<?= htmlspecialchars($user['username']) ?>">
                        Delete
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($currentPage > 1): ?>
            <a href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>">Previous</a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
            <?php if ($i == $currentPage): ?>
                <span class="current"><?= $i ?></span>
            <?php else: ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($currentPage < $totalPages): ?>
            <a href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>">Next</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
`;
</script>