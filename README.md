# 🎮 Wasteland Dominion

Post-apokalyptická strategická webová hra s multiplayerem.

## 🚀 Rychlý Start

1. **Požadavky:**
   - PHP 8.2+
   - MySQL 8.0+
   - Composer
   - Node.js (pro build tools)

2. **Instalace:**
   ```bash
   composer install
   npm install
   cp config/config.example.php config/config.php
   ```

3. **Databáze:**
   ```bash
   php database/migrate.php
   php database/seed.php
   ```

4. **Spuštění:**
   ```bash
   php -S localhost:8000 -t public
   ```

## 📁 Struktura Projektu

```
wasteland-dominion/
├── public/              # Webroot
├── src/                 # Core aplikace
├── templates/           # View templates
├── database/            # Migrations & seeds
├── config/              # Konfigurace
├── assets/              # Frontend assets
├── logs/                # Aplikační logy
└── tests/               # Unit testy
```

## 🎯 Herní Mechaniky

- **10 měst** s unikátními lokacemi
- **500+ úkolů** různých obtížností
- **1000+ předmětů** a zbraní
- **500+ nepřátel** a bossů
- **Multiplayer** guild systém
- **Real-time** souboje a komunikace

## 🔧 Technologie

- **Backend:** PHP 8.2, MySQL, Redis
- **Frontend:** Vanilla JS, SVG, CSS3
- **Real-time:** WebSockets
- **Security:** JWT, Rate limiting

## 📈 Roadmapa

Viz `docs/ROADMAP.md` pro detailní plán vývoje.

## 🤝 Přispívání

1. Fork projekt
2. Vytvoř feature branch
3. Commit změny
4. Push do branch
5. Vytvoř Pull Request

## 📄 Licence

MIT License - viz `LICENSE` soubor.