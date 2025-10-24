# 🚀 UPLOAD DO GITHUB - RoadToFame Repository

## ⚠️ DŮLEŽITÉ - PŘED ZAČÁTKEM

### 1. **Instalace Git** (pokud není nainstalován)
```
1. Stáhněte Git: https://git-scm.com/download/win
2. Nainstalujte s výchozími nastaveními
3. RESTARTUJTE PowerShell/CMD
4. Ověřte instalaci: git --version
```

### 2. **Konfigurace Git** (první použití)
```bash
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

---

## 🎯 POSTUPNÉ KROKY PRO UPLOAD

### **Krok 1: Inicializace**
```bash
cd "D:\projekty\wasteland-dominion"
git init
```

### **Krok 2: Přidání souborů**
```bash
git add .
```

### **Krok 3: První commit**
```bash
git commit -m "Complete Wasteland Dominion: Post-apocalyptic MMO

- Full PHP MVC game engine with 89 files
- Turn-based combat system with AI enemies  
- Complete economy & trading system
- Guild multiplayer features
- Quest system with multiple types
- Inventory & item management
- 8 explorable wasteland locations
- Admin panel with full management
- Webhosting-ready build (140KB package)
- Real-time WebSocket support
- ~15,000 lines of code
- Zero external dependencies for production"
```

### **Krok 4: Připojení k GitHub**
```bash
git remote add origin https://github.com/daker52/RoadToFame.git
```

### **Krok 5: Synchronizace s existujícím repo**
```bash
git pull origin main --allow-unrelated-histories
```
*Poznámka: Tento command může vyžadovat resolve conflicts pokud už repo obsahuje soubory*

### **Krok 6: Upload na GitHub**
```bash
git branch -M main
git push -u origin main
```

### **Krok 7: Vytvoření release**
```bash
git tag -a v1.0.0 -m "Wasteland Dominion v1.0.0: Complete MMO"
git push origin v1.0.0
```

---

## 🔧 MOŽNÉ PROBLÉMY A ŘEŠENÍ

### **Problem: Git command not found**
```
Řešení: Git není nainstalován nebo není v PATH
1. Stáhněte a nainstalujte Git
2. Restartujte PowerShell
3. Zkuste znovu
```

### **Problem: Repository already exists**
```bash
# Pokud už existuje .git složka:
rm -rf .git
git init
# Pokračujte kroky znovu
```

### **Problem: Merge conflicts při pull**
```bash
# Pokud jsou konflikty při pull:
git status
# Vyřešte konflikty ručně, pak:
git add .
git commit -m "Resolve merge conflicts"
git push
```

### **Problem: Permission denied (public key)**
```
Řešení: Použijte HTTPS místo SSH nebo nastavte SSH klíče
Repository URL: https://github.com/daker52/RoadToFame.git
```

---

## 📋 CHECKLIST

- [ ] Git nainstalován a funguje (`git --version`)
- [ ] User name a email nakonfigurován
- [ ] V správné složce (`D:\projekty\wasteland-dominion`)
- [ ] Všechny soubory připraveny (89 souborů)
- [ ] README.md aktualizován
- [ ] .gitignore správně nastaven
- [ ] Commit message připraven

---

## 🎊 VÝSLEDEK

Po dokončení budete mít:
- ✅ **Kompletní Wasteland Dominion** na GitHub
- ✅ **Profesionální dokumentaci** v README
- ✅ **Webhosting balíček** v dist/ složce
- ✅ **Release tag v1.0.0** pro easy download
- ✅ **Open source projekt** připraven pro community

---

**Repository URL po uploadu:**
`https://github.com/daker52/RoadToFame`

**Webhosting package download:**
`https://github.com/daker52/RoadToFame/releases/tag/v1.0.0`