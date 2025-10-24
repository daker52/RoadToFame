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
                <svg class="logo" viewBox="0 0 300 120" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <!-- Enhanced gradients -->
                        <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#ff6b35"/>
                            <stop offset="50%" style="stop-color:#f7931e"/>
                            <stop offset="100%" style="stop-color:#ff4500"/>
                        </linearGradient>
                        
                        <radialGradient id="skullGradient" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" style="stop-color:#ffd23f"/>
                            <stop offset="100%" style="stop-color:#ff6b35"/>
                        </radialGradient>
                        
                        <!-- Enhanced glow effect -->
                        <filter id="glow" x="-50%" y="-50%" width="200%" height="200%">
                            <feGaussianBlur stdDeviation="4" result="coloredBlur"/>
                            <feMerge> 
                                <feMergeNode in="coloredBlur"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                        
                        <!-- Flickering animation -->
                        <filter id="flicker">
                            <feGaussianBlur stdDeviation="2" result="blur"/>
                            <feColorMatrix type="saturate" values="1.5"/>
                        </filter>
                    </defs>
                    
                    <!-- Background radiation effect -->
                    <circle cx="60" cy="60" r="50" fill="none" stroke="url(#logoGradient)" stroke-width="1" opacity="0.3">
                        <animate attributeName="r" values="45;55;45" dur="4s" repeatCount="indefinite"/>
                    </circle>
                    <circle cx="60" cy="60" r="35" fill="none" stroke="url(#logoGradient)" stroke-width="0.5" opacity="0.2">
                        <animate attributeName="r" values="30;40;30" dur="3s" repeatCount="indefinite"/>
                    </circle>
                    
                    <!-- Main skull symbol -->
                    <g transform="translate(60,60)" filter="url(#glow)">
                        <!-- Skull outline -->
                        <path d="M -15,-20 Q -20,-25 -15,-30 Q 0,-35 15,-30 Q 20,-25 15,-20 L 15,-5 Q 15,5 10,10 L 5,15 L -5,15 Q -15,5 -15,-5 Z" 
                              fill="url(#skullGradient)" stroke="#2d1810" stroke-width="1"/>
                        
                        <!-- Eye sockets with glow -->
                        <circle cx="-7" cy="-10" r="4" fill="#000">
                            <animate attributeName="fill" values="#000;#ff6b35;#000" dur="6s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="7" cy="-10" r="4" fill="#000">
                            <animate attributeName="fill" values="#000;#ff6b35;#000" dur="6s" begin="3s" repeatCount="indefinite"/>
                        </circle>
                        
                        <!-- Nose -->
                        <path d="M 0,-5 L -2,2 L 0,3 L 2,2 Z" fill="#2d1810"/>
                        
                        <!-- Teeth -->
                        <rect x="-6" y="5" width="2" height="4" fill="#e0e0e0"/>
                        <rect x="-3" y="5" width="2" height="5" fill="#e0e0e0"/>
                        <rect x="0" y="5" width="2" height="4" fill="#e0e0e0"/>
                        <rect x="3" y="5" width="2" height="5" fill="#e0e0e0"/>
                        
                        <!-- Cracks in skull -->
                        <path d="M -10,-15 L -5,-10" stroke="#2d1810" stroke-width="1" opacity="0.7"/>
                        <path d="M 8,-18 L 12,-12" stroke="#2d1810" stroke-width="1" opacity="0.7"/>
                    </g>
                    
                    <!-- Radioactive symbol overlay -->
                    <g transform="translate(85,35)" filter="url(#flicker)" opacity="0.6">
                        <circle cx="0" cy="0" r="2" fill="#39ff14"/>
                        <path d="M 0,-10 L -3,-6 L 3,-6 Z" fill="#39ff14"/>
                        <path d="M 8.66,5 L 5.66,8 L 5.66,2 Z" fill="#39ff14"/>
                        <path d="M -8.66,5 L -5.66,2 L -5.66,8 Z" fill="#39ff14"/>
                    </g>
                    
                    <!-- Title text with better typography -->
                    <text x="150" y="50" text-anchor="middle" class="logo-text-main" fill="url(#logoGradient)" filter="url(#glow)">
                        WASTELAND
                    </text>
                    <text x="150" y="75" text-anchor="middle" class="logo-text-sub" fill="url(#logoGradient)" filter="url(#glow)">
                        DOMINION
                    </text>
                    
                    <!-- Subtitle -->
                    <text x="150" y="95" text-anchor="middle" class="logo-subtitle" fill="#ffd23f" opacity="0.8">
                        Survival • Strategy • Multiplayer
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
                    Vítej ve světě <span class="highlight">po nukleární apokalypse</span>
                </h1>
                <p class="hero-subtitle">
                    Rok 2087. Velká válka zničila civilizaci během tří hodin. 
                    Z popela starého světa vznikl nový řád - brutální, bezohledný a nemilosrdný.
                    <br><br>
                    <strong>Ty jsi jeden z přeživších.</strong> Vybereš si cestu válečníka, obchodníka, 
                    technika nebo něco úplně jiného? Každé rozhodnutí může být tvé poslední.
                </p>
                
                <!-- Interactive story elements -->
                <div class="story-highlight">
                    <div class="story-box">
                        <h3>🏙️ 10 Postapokalyptických Měst</h3>
                        <p>Od bezpečného New Eden po nebezpečné Deadman's Cross</p>
                    </div>
                    <div class="story-box">
                        <h3>⚔️ 500+ Úkolů</h3>
                        <p>Každý úkol přináší risk, ale i šanci na vzácné poklady</p>
                    </div>
                    <div class="story-box">
                        <h3>🎒 1000+ Předmětů</h3>
                        <p>Od zarezlých trubek po high-tech plazmatické zbraně</p>
                    </div>
                </div>
                
                <!-- Auth Buttons -->
                <div class="auth-buttons">
                    <button class="btn btn-primary" onclick="showRegister()">
                        🚀 Vstoupit do Wastelandu
                    </button>
                    <button class="btn btn-secondary" onclick="showLogin()">
                        🔓 Máš už účet? Přihlaš se
                    </button>
                </div>
                
                <!-- Game preview -->
                <div class="game-preview">
                    <div class="preview-text">
                        <p><em>"V wastělandu není místo pro slabé. Každý den je boj o přežití. 
                        Ale pro ty, kteří jsou dost silní, čekají bohatství a sláva."</em></p>
                        <span>- Marcus 'Steelheart' Rodriguez, Legion Commander</span>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Stats Preview with animations -->
            <div class="stats-preview">
                <div class="stat-item" data-count="10">
                    <span class="stat-number">10</span>
                    <span class="stat-label">Postapokalyptických Měst</span>
                    <div class="stat-icon">🏙️</div>
                </div>
                <div class="stat-item" data-count="500">
                    <span class="stat-number">500+</span>
                    <span class="stat-label">Úkolů k Plnění</span>
                    <div class="stat-icon">📋</div>
                </div>
                <div class="stat-item" data-count="1000">
                    <span class="stat-number">1000+</span>
                    <span class="stat-label">Zbraní a Předmětů</span>
                    <div class="stat-icon">⚔️</div>
                </div>
                <div class="stat-item" data-count="100">
                    <span class="stat-number">∞</span>
                    <span class="stat-label">Možností Přežití</span>
                    <div class="stat-icon">🎯</div>
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
            <div class="modal-header">
                <h2>🚀 Vstoupit do Wastelandu</h2>
                <p>Vytvoř si účet a začni své dobrodružství v postapokalyptickém světě</p>
            </div>
            
            <form id="registerForm" action="/auth/register" method="POST">
                <div class="form-group">
                    <label for="reg_username">🎮 Uživatelské jméno</label>
                    <input type="text" id="reg_username" name="username" placeholder="Survivor001" required minlength="3" maxlength="30">
                    <span class="form-hint">3-30 znaků, pouze písmena, čísla a podtržítka</span>
                </div>
                
                <div class="form-group">
                    <label for="reg_email">📧 Email</label>
                    <input type="email" id="reg_email" name="email" placeholder="survivor@wasteland.com" required>
                    <span class="form-hint">Použijeme pro důležité oznámení o hře</span>
                </div>
                
                <div class="form-group">
                    <label for="reg_password">🔒 Heslo</label>
                    <input type="password" id="reg_password" name="password" placeholder="••••••••" required minlength="6">
                    <span class="form-hint">Minimálně 6 znaků</span>
                </div>
                
                <div class="form-group">
                    <label for="reg_password_confirm">🔒 Potvrdit heslo</label>
                    <input type="password" id="reg_password_confirm" name="password_confirm" placeholder="••••••••" required>
                </div>
                
                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terms" required>
                        <span class="checkmark"></span>
                        Souhlasím s <a href="/terms" target="_blank">podmínkami použití</a> a <a href="/privacy" target="_blank">zásadami ochrany osobních údajů</a>
                    </label>
                </div>
                
                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="newsletter">
                        <span class="checkmark"></span>
                        Chci dostávat novinky o hře a speciální nabídky
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    <span class="btn-icon">🚀</span>
                    Vytvořit Účet Survivora
                </button>
                
                <div class="form-footer">
                    <p>Už máš účet? <a href="#" onclick="closeModal('registerModal'); showModal('loginModal');">Přihlaš se zde</a></p>
                </div>
            </form>
            
            <div id="registerSuccess" class="success-message" style="display: none;">
                <div class="success-icon">🎉</div>
                <h3>Vítej ve Wastelandu!</h3>
                <p>Tvůj účet byl úspěšně vytvořen. Nyní si můžeš vytvořit svou postavu a začít hrát.</p>
                <button class="btn btn-primary" onclick="window.location.href='/game/character-setup'">
                    Pokračovat do hry
                </button>
            </div>
        </div>
    </div>

    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('loginModal')">&times;</span>
            <div class="modal-header">
                <h2>🔓 Návrat do Wastelandu</h2>
                <p>Přihlaš se a pokračuj ve svém dobrodružství</p>
            </div>
            
            <form id="loginForm" action="/auth/login" method="POST">
                <div class="form-group">
                    <label for="login_username">👤 Uživatelské jméno nebo email</label>
                    <input type="text" id="login_username" name="username" placeholder="Survivor001 nebo email" required>
                </div>
                
                <div class="form-group">
                    <label for="login_password">🔒 Heslo</label>
                    <input type="password" id="login_password" name="password" placeholder="••••••••" required>
                </div>
                
                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember">
                        <span class="checkmark"></span>
                        Zapamatovat si mě (7 dní)
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    <span class="btn-icon">🔓</span>
                    Vstoupit do Wastelandu
                </button>
                
                <div class="form-footer">
                    <p><a href="/auth/forgot-password">Zapomněl jsi heslo?</a></p>
                    <p>Nemáš účet? <a href="#" onclick="closeModal('loginModal'); showModal('registerModal');">Registruj se zde</a></p>
                </div>
            </form>
            
            <div id="loginSuccess" class="success-message" style="display: none;">
                <div class="success-icon">🎮</div>
                <h3>Vítej zpět, Survivore!</h3>
                <p>Přihlášení bylo úspěšné. Přesměrovávám tě do hry...</p>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>