<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Wasteland Dominion' ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="/assets/css/app.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/favicon.ico">
    
    <!-- Meta tags -->
    <meta name="description" content="<?= $description ?? 'Post-apocalyptic survival MMO game' ?>">
    <meta name="keywords" content="wasteland, survival, MMO, post-apocalyptic, game">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= $title ?? 'Wasteland Dominion' ?>">
    <meta property="og:description" content="<?= $description ?? 'Post-apocalyptic survival MMO game' ?>">
    <meta property="og:type" content="website">
    
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="<?= $bodyClass ?? '' ?>">
    <?php if (isset($showNavigation) && $showNavigation): ?>
    <nav class="main-navigation">
        <div class="nav-container">
            <a href="/" class="nav-logo">
                <img src="/assets/img/logo.svg" alt="Wasteland Dominion">
            </a>
            
            <div class="nav-menu">
                <a href="/map" class="nav-link">
                    <i class="fas fa-map"></i> Mapa
                </a>
                <a href="/character" class="nav-link">
                    <i class="fas fa-user"></i> Postava
                </a>
                <a href="/inventory" class="nav-link">
                    <i class="fas fa-boxes"></i> Inventář
                </a>
                <a href="/quests" class="nav-link">
                    <i class="fas fa-scroll"></i> Questy
                </a>
                <a href="/guilds" class="nav-link">
                    <i class="fas fa-shield-alt"></i> Guildy
                </a>
                <a href="/logout" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Odhlásit
                </a>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <main class="main-content">
        <?= $content ?? '' ?>
    </main>
    
    <?php if (isset($showFooter) && $showFooter): ?>
    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; 2025 Wasteland Dominion. All rights reserved.</p>
        </div>
    </footer>
    <?php endif; ?>
    
    <!-- JavaScript -->
    <script src="/assets/js/app.min.js"></script>
    
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($inlineJS)): ?>
        <script><?= $inlineJS ?></script>
    <?php endif; ?>
</body>
</html>