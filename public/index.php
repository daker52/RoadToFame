<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎮 Wasteland Dominion - Post-Apokalyptická Strategická Hra</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body class="wasteland-bg">
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="logo-container">
                <svg class="logo" viewBox="0 0 200 100" xmlns="http://www.w3.org/2000/svg">
                    <!-- Radioactive symbol with post-apo styling -->
                    <defs>
                        <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#ff6b35"/>
                            <stop offset="100%" style="stop-color:#f7931e"/>
                        </linearGradient>
                        <filter id="glow">
                            <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                            <feMerge> 
                                <feMergeNode in="coloredBlur"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                    </defs>
                    
                    <!-- Main radioactive symbol -->
                    <g transform="translate(100,50)" filter="url(#glow)">
                        <circle cx="0" cy="0" r="5" fill="url(#logoGradient)"/>
                        <path d="M 0,-25 L -8,-15 L 8,-15 Z" fill="url(#logoGradient)"/>
                        <path d="M 21.65,12.5 L 13.65,4.5 L 13.65,20.5 Z" fill="url(#logoGradient)"/>
                        <path d="M -21.65,12.5 L -13.65,20.5 L -13.65,4.5 Z" fill="url(#logoGradient)"/>
                    </g>
                    
                    <!-- Text -->
                    <text x="100" y="80" text-anchor="middle" class="logo-text" fill="url(#logoGradient)">
                        WASTELAND DOMINION
                    </text>
                </svg>
            </div>
            <nav class="nav">
                <a href="#story" class="nav-link">Příběh</a>
                <a href="#features" class="nav-link">Herní Svět</a>
                <a href="#news" class="nav-link">Novinky</a>
                <a href="forum.php" class="nav-link">Fórum</a>
            </nav>
        </header>

        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h1 class="hero-title">
                    Vítej ve světě po <span class="highlight">nukleární apokalypse</span>
                </h1>
                <p class="hero-subtitle">
                    Rok 2087. Svět, jak jsme ho znali, neexistuje. Z popela civilizace vznikl nový řád - 
                    kde přežije jen ten nejsilnější, nejchytřejší a nejbezohlednější.
                </p>
                
                <!-- Auth Buttons -->
                <div class="auth-buttons">
                    <button class="btn btn-primary" onclick="showRegister()">
                        🚀 Vstoupit do Hry
                    </button>
                    <button class="btn btn-secondary" onclick="showLogin()">
                        🔓 Přihlásit se
                    </button>
                </div>
            </div>
            
            <!-- Stats Preview -->
            <div class="stats-preview">
                <div class="stat-item">
                    <span class="stat-number">10</span>
                    <span class="stat-label">Postapokalyptických Měst</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">500+</span>
                    <span class="stat-label">Úkolů k Plnění</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">1000+</span>
                    <span class="stat-label">Zbraní a Předmětů</span>
                </div>
            </div>
        </section>

        <!-- Story Section -->
        <section id="story" class="story">
            <h2 class="section-title">📖 Příběh Světa</h2>
            <div class="story-grid">
                <div class="story-card">
                    <h3>🌆 Před Válkou</h3>
                    <p>Svět roku 2087 byl plný pokroku a technologií. Města se tyčila k nebi, 
                    umělá inteligence řídila každodenní život a lidstvo se zdálo neporazitelné.</p>
                </div>
                <div class="story-card">
                    <h3>💥 Den Zkázy</h3>
                    <p>Jeden jediný den změnil vše. Nukleární válka trvala pouhé tři hodiny, 
                    ale její následky budou trvat tisíce let. Civilizace padla během okamžiku.</p>
                </div>
                <div class="story-card">
                    <h3>🏜️ Nový Svět</h3>
                    <p>Z popela vznikl brutální svět, kde každý den je bojem o přežití. 
                    Města se proměnila v pevnosti, zdroje jsou vzácné a důvěra je luxus.</p>
                </div>
                <div class="story-card">
                    <h3>⚔️ Tvá Cesta</h3>
                    <p>Ty jsi jeden z přeživších. Vybereš si svou cestu - budeš obchodníkem, 
                    válečníkem, technikem nebo možná něčím úplně jiným. Tvoje volby určí tvůj osud.</p>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="features">
            <h2 class="section-title">🎮 Herní Svět</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🏙️</div>
                    <h3>10 Unikátních Měst</h3>
                    <p>Každé město má svou atmosféru, obyvatele a příležitosti. 
                    Od bezpečného New Eden po nebezpečné Deadman's Cross.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⚔️</div>
                    <h3>Strategické Souboje</h3>
                    <p>Automatické souboje s taktickými prvky. Tvé statistiky, 
                    vybavení a strategie rozhodují o vítězství.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎒</div>
                    <h3>Tisíce Předmětů</h3>
                    <p>Zbraně, zbroje, modifikace a consumables. 
                    Od zarezlé trubky po high-tech plazmatickou pušku.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Multiplayer Guildy</h3>
                    <p>Spojuj se s ostatními hráči, vytvářejte aliance, 
                    bojujte společně a dominujte wastelandu.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📈</div>
                    <h3>Progresní Systém</h3>
                    <p>Zvyšuj level, vylepšuj statistiky, odemykej nové 
                    schopnosti a staň se legendou wastelandu.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💎</div>
                    <h3>Ekonomický Systém</h3>
                    <p>Obchoduj s bottle caps, sběraj diamanty, 
                    investuj do vybavení a buduj své impérium.</p>
                </div>
            </div>
        </section>

        <!-- News Section -->
        <section id="news" class="news">
            <h2 class="section-title">📰 Nejnovější Zprávy z Wastelandu</h2>
            <div class="news-grid">
                <article class="news-item">
                    <div class="news-date">24. říjen 2025</div>
                    <h3>🚀 Beta verze brzy dostupná!</h3>
                    <p>Připravujeme beta test pro prvních 100 hráčů. 
                    Registruj se jako první a získej exkluzivní přístup.</p>
                </article>
                <article class="news-item">
                    <div class="news-date">20. říjen 2025</div>
                    <h3>⚔️ Nové soubojové mechaniky</h3>
                    <p>Představujeme pokročilé soubojové systémy s kombinacemi útoků 
                    a taktickými možnostmi.</p>
                </article>
                <article class="news-item">
                    <div class="news-date">15. říjen 2025</div>
                    <h3>🏗️ Stavíme svět budoucnosti</h3>
                    <p>První pohled na herní města a lokace. 
                    Každé místo má svou jedinečnou atmosféru.</p>
                </article>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>Wasteland Dominion</h4>
                <p>Post-apokalyptická strategická hra</p>
            </div>
            <div class="footer-section">
                <h4>Rychlé Odkazy</h4>
                <a href="register.php">Registrace</a>
                <a href="forum.php">Fórum</a>
                <a href="guide.php">Návody</a>
            </div>
            <div class="footer-section">
                <h4>Komunita</h4>
                <a href="#discord">Discord</a>
                <a href="#reddit">Reddit</a>
                <a href="#youtube">YouTube</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Wasteland Dominion. Všechna práva vyhrazena.</p>
        </div>
    </footer>

    <!-- Modals -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('registerModal')">&times;</span>
            <h2>🚀 Vstoupit do Wastelandu</h2>
            <form id="registerForm" action="auth/register.php" method="POST">
                <input type="text" name="username" placeholder="Uživatelské jméno" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Heslo" required>
                <input type="password" name="password_confirm" placeholder="Potvrdit heslo" required>
                <label>
                    <input type="checkbox" name="terms" required>
                    Souhlasím s <a href="terms.php">podmínkami</a>
                </label>
                <button type="submit" class="btn btn-primary">Vytvořit Účet</button>
            </form>
        </div>
    </div>

    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('loginModal')">&times;</span>
            <h2>🔓 Přihlášení</h2>
            <form id="loginForm" action="auth/login.php" method="POST">
                <input type="text" name="username" placeholder="Uživatelské jméno nebo email" required>
                <input type="password" name="password" placeholder="Heslo" required>
                <label>
                    <input type="checkbox" name="remember">
                    Zapamatovat si mě
                </label>
                <button type="submit" class="btn btn-primary">Přihlásit se</button>
                <a href="auth/forgot.php" class="forgot-link">Zapomenuté heslo?</a>
            </form>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>