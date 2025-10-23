// Wasteland Dominion - Main JavaScript

class WastelandGame {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.setupAnimations();
        this.initWebSocket();
    }

    bindEvents() {
        // Modal events
        window.showRegister = () => this.showModal('registerModal');
        window.showLogin = () => this.showModal('loginModal');
        window.closeModal = (modalId) => this.closeModal(modalId);

        // Form events
        document.getElementById('registerForm')?.addEventListener('submit', this.handleRegister.bind(this));
        document.getElementById('loginForm')?.addEventListener('submit', this.handleLogin.bind(this));

        // Close modal on outside click
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeModal(e.target.id);
            }
        });

        // Smooth scrolling for nav links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    setupAnimations() {
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe cards and sections
        document.querySelectorAll('.story-card, .feature-card, .news-item').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });

        // Typing animation for hero title
        this.typeWriter();
    }

    typeWriter() {
        const heroTitle = document.querySelector('.hero-title');
        if (!heroTitle) return;

        const text = heroTitle.innerHTML;
        heroTitle.innerHTML = '';
        let i = 0;

        const typeInterval = setInterval(() => {
            if (i < text.length) {
                heroTitle.innerHTML += text.charAt(i);
                i++;
            } else {
                clearInterval(typeInterval);
            }
        }, 50);
    }

    showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Focus first input
            const firstInput = modal.querySelector('input');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
        }
    }

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    async handleRegister(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        // Basic validation
        const password = formData.get('password');
        const passwordConfirm = formData.get('password_confirm');

        if (password !== passwordConfirm) {
            this.showNotification('Hesla se neshodují!', 'error');
            return;
        }

        if (password.length < 6) {
            this.showNotification('Heslo musí mít alespoň 6 znaků!', 'error');
            return;
        }

        this.showLoading(form);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Registrace úspěšná! Vítej ve Wastelandu!', 'success');
                this.closeModal('registerModal');
                
                // Redirect after short delay
                setTimeout(() => {
                    window.location.href = 'game/dashboard.php';
                }, 2000);
            } else {
                this.showNotification(result.message || 'Chyba při registraci', 'error');
            }
        } catch (error) {
            this.showNotification('Chyba sítě. Zkus to znovu.', 'error');
        } finally {
            this.hideLoading(form);
        }
    }

    async handleLogin(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        this.showLoading(form);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Přihlášení úspěšné! Vítej zpět!', 'success');
                this.closeModal('loginModal');
                
                // Redirect
                setTimeout(() => {
                    window.location.href = 'game/dashboard.php';
                }, 1500);
            } else {
                this.showNotification(result.message || 'Chyba při přihlášení', 'error');
            }
        } catch (error) {
            this.showNotification('Chyba sítě. Zkus to znovu.', 'error');
        } finally {
            this.hideLoading(form);
        }
    }

    showLoading(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '⏳ Načítám...';
            submitBtn.classList.add('loading');
        }
    }

    hideLoading(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('loading');
            
            // Restore original text
            if (form.id === 'registerForm') {
                submitBtn.innerHTML = 'Vytvořit Účet';
            } else if (form.id === 'loginForm') {
                submitBtn.innerHTML = 'Přihlásit se';
            }
        }
    }

    showNotification(message, type = 'info') {
        // Remove existing notifications
        document.querySelectorAll('.notification').forEach(n => n.remove());

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <span>${message}</span>
            <button onclick="this.parentElement.remove()">&times;</button>
        `;

        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            font-weight: 600;
            z-index: 10000;
            max-width: 400px;
            animation: slideIn 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        `;

        // Type-specific styles
        switch (type) {
            case 'success':
                notification.style.background = 'linear-gradient(45deg, #4CAF50, #45a049)';
                break;
            case 'error':
                notification.style.background = 'linear-gradient(45deg, #f44336, #da190b)';
                break;
            case 'warning':
                notification.style.background = 'linear-gradient(45deg, #ff9800, #e68900)';
                break;
            default:
                notification.style.background = 'linear-gradient(45deg, #2196F3, #1976D2)';
        }

        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }

    initWebSocket() {
        // WebSocket connection for real-time updates
        if (window.location.pathname.includes('game/')) {
            this.connectWebSocket();
        }
    }

    connectWebSocket() {
        const wsHost = window.location.hostname;
        const wsPort = 8080;
        
        try {
            this.ws = new WebSocket(`ws://${wsHost}:${wsPort}`);
            
            this.ws.onopen = () => {
                console.log('WebSocket connected');
            };
            
            this.ws.onmessage = (event) => {
                this.handleWebSocketMessage(JSON.parse(event.data));
            };
            
            this.ws.onclose = () => {
                console.log('WebSocket disconnected');
                // Attempt reconnection after 5 seconds
                setTimeout(() => this.connectWebSocket(), 5000);
            };
            
            this.ws.onerror = (error) => {
                console.error('WebSocket error:', error);
            };
        } catch (error) {
            console.warn('WebSocket not available:', error);
        }
    }

    handleWebSocketMessage(data) {
        switch (data.type) {
            case 'quest.timer.update':
                this.updateQuestTimer(data.questId, data.timeRemaining);
                break;
            case 'combat.state.change':
                this.updateCombatState(data.combatId, data.state);
                break;
            case 'player.location.change':
                this.updatePlayerLocation(data.playerId, data.location);
                break;
            case 'server.announcement':
                this.showNotification(data.message, 'info');
                break;
            default:
                console.log('Unknown WebSocket message:', data);
        }
    }

    updateQuestTimer(questId, timeRemaining) {
        const timerElement = document.querySelector(`[data-quest-id="${questId}"] .timer`);
        if (timerElement) {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }
    }

    updateCombatState(combatId, state) {
        const combatElement = document.querySelector(`[data-combat-id="${combatId}"]`);
        if (combatElement) {
            // Update combat UI based on state
            console.log(`Combat ${combatId} state:`, state);
        }
    }

    updatePlayerLocation(playerId, location) {
        // Update UI to show player movement
        console.log(`Player ${playerId} moved to:`, location);
    }

    // Utility functions
    formatNumber(num) {
        return new Intl.NumberFormat('cs-CZ').format(num);
    }

    formatTime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        
        if (hours > 0) {
            return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }
        return `${minutes}:${secs.toString().padStart(2, '0')}`;
    }

    // Local storage helpers
    saveToStorage(key, value) {
        localStorage.setItem(`wd_${key}`, JSON.stringify(value));
    }

    loadFromStorage(key) {
        const item = localStorage.getItem(`wd_${key}`);
        return item ? JSON.parse(item) : null;
    }
}

// CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .notification button {
        background: none;
        border: none;
        color: white;
        font-size: 18px;
        cursor: pointer;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .notification button:hover {
        opacity: 0.7;
    }
`;
document.head.appendChild(style);

// Initialize the game when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.wastelandGame = new WastelandGame();
});

// Export for modules if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = WastelandGame;
}