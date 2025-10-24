# 🚀 WEBHOSTING DEPLOYMENT GUIDE

Tento dokument popisuje kroky pro nasazení Wasteland Dominion na standardní webhosting bez Node.js a Composer.

## 📋 POŽADAVKY WEBHOSTINGU

- **PHP 7.4+** (doporučeno PHP 8.0+)
- **MySQL 5.7+** nebo **MariaDB 10.2+**
- **Apache** s mod_rewrite nebo **Nginx**
- **Povolené PHP extensions**: PDO, PDO_MySQL, JSON, Session
- **Minimální disk space**: 50 MB
- **Možnost vytvořit databázi**

## 🔨 PŘÍPRAVA LOKÁLNĚ

### 1. Spuštění Build Scriptu
```bash
cd wasteland-dominion
php build-for-hosting.php
```

Build script vytvoří:
- `/build/` - Připravená aplikace
- `/dist/wasteland-dominion.zip` - Balíček pro upload

### 2. Optimalizace Template souborů
```bash
php optimize-templates.php
```

## 📤 NAHRÁNÍ NA WEBHOSTING

### 1. Upload souborů
1. Stáhněte `dist/wasteland-dominion.zip`
2. Rozbalte obsah do root složky webhostingu
3. Ujistěte se, že `public/` je document root

### 2. Nastavení Document Root
V administraci webhostingu nastavte document root na:
```
/public
```

Pokud toto není možné, přesuňte obsah `public/` do root složky.

## 🗃️ INSTALACE DATABÁZE

### 1. Automatická instalace
1. Otevřete `https://vase-domena.cz/install.php`
2. Vyplňte údaje k databázi:
   - **Database Host**: Většinou `localhost`
   - **Database Name**: Název vaší databáze
   - **Username**: Uživatelské jméno k databázi
   - **Password**: Heslo k databázi
3. Klikněte na "🚀 Instalovat Databázi"
4. **DŮLEŽITÉ**: Po instalaci smažte `install.php`

### 2. Manuální instalace (alternativa)
Pokud automatická instalace nefunguje:

1. Vytvořte databázi v administraci webhostingu
2. Importujte SQL soubory v tomto pořadí:
   ```
   database/migrations/001_create_users_tables.sql
   database/migrations/002_create_world_tables.sql
   database/migrations/003_create_character_tables.sql
   database/migrations/004_create_quest_tables.sql
   database/migrations/005_create_items_tables.sql
   database/migrations/006_create_combat_tables.sql
   database/migrations/007_create_admin_tables.sql
   database/migrations/008_create_multiplayer_tables.sql
   ```
3. Spusťte seeder: `php database/seed.php`

## ⚙️ KONFIGURACE

### 1. Úprava config.php
Upravte `config/config.php`:

```php
return [
    "database" => [
        "host" => "localhost",        // Váš DB host
        "name" => "vase_databaze",    // Název databáze
        "user" => "db_username",      // DB uživatel
        "pass" => "db_password",      // DB heslo
        "charset" => "utf8mb4"
    ],
    
    "app" => [
        "name" => "Wasteland Dominion",
        "env" => "production",
        "debug" => false,             // VŽDY false na produkci!
        "url" => "https://vase-domena.cz",
        "timezone" => "Europe/Prague"
    ]
];
```

### 2. Nastavení oprávnění
Nastavte oprávnění složek (pokud je to možné):
```
chmod 755 public/uploads/
chmod 644 config/config.php
```

## 🔒 BEZPEČNOST

### 1. Ochrana citlivých souborů
Přidejte do `.htaccess`:
```apache
<Files "config.php">
    Require all denied
</Files>

<Files "*.sql">
    Require all denied
</Files>
```

### 2. Změna výchozích přístupů
**DŮLEŽITÉ**: Změňte výchozí admin účet!

1. Přihlaste se jako: `admin@admin.com` / `admin123`
2. Jděte do administrace
3. Změňte email a heslo
4. Vytvořte nové admin účty
5. Smažte výchozí účet

## 🚀 SPUŠTĚNÍ

1. Otevřete `https://vase-domena.cz`
2. Vytvořte si účet nebo se přihlaste
3. Začněte hrát!

## 🐛 ŘEŠENÍ PROBLÉMŮ

### Chyba 500 - Internal Server Error
- Zkontrolujte oprávnění souborů
- Ověřte správnost `config/config.php`
- Zkontrolujte error logy webhostingu

### Chyba připojení k databázi
- Ověřte údaje v `config/config.php`
- Zkontrolujte, zda databáze existuje
- Ověřte, zda má uživatel oprávnění k databázi

### Nefungují CSS/JS
- Ověřte, zda je document root nastaven na `/public`
- Zkontrolujte cesty k souborům v `templates/base.php`

### 404 chyby na stránkách
- Ověřte, zda je povolený mod_rewrite
- Zkontrolujte `.htaccess` soubor

## 📞 PODPORA

Pokud máte problémy s nasazením:

1. Zkontrolujte error logy webhostingu
2. Ověřte všechny konfigurace
3. Ujistěte se, že webhosting splňuje požadavky

## 📈 VÝKON

Pro optimální výkon:
- Povolte **OPcache** (pokud je k dispozici)
- Nastavte **MySQL query cache**
- Použijte **Gzip kompresi**
- Nastavte **správné cache headers** pro statické soubory

Aplikace je optimalizovaná pro standardní webhosting a měla by fungovat rychle i na sdíleném hostingu s dostatkem návštěvníků současně.

---

**Úspěšné nasazení! Váš post-apokalyptický svět je nyní online! 🎮**