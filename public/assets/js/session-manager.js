/**
 * Session Manager
 * Handles session timeout, auto-refresh, and warnings
 */

class SessionManager {
    constructor(options = {}) {
        this.sessionTimeout = options.sessionTimeout || 30 * 60 * 1000; // 30 minutes in milliseconds
        this.warningTime = options.warningTime || 5 * 60 * 1000; // 5 minutes before timeout
        this.checkInterval = options.checkInterval || 60 * 1000; // Check every minute
        this.autoRefresh = options.autoRefresh !== false; // Auto refresh by default
        this.refreshThreshold = options.refreshThreshold || 10 * 60 * 1000; // Refresh when 10 min remaining
        
        this.lastActivity = Date.now();
        this.warningShown = false;
        this.checkTimer = null;
        this.warningModal = null;
        
        this.init();
    }

    init() {
        // Track user activity
        this.trackActivity();
        
        // Start checking session
        this.startChecking();
        
        // Create warning modal
        this.createWarningModal();
    }

    trackActivity() {
        const events = ['mousedown', 'keydown', 'scroll', 'touchstart', 'click'];
        
        events.forEach(event => {
            document.addEventListener(event, () => {
                this.updateActivity();
            }, true);
        });
    }

    updateActivity() {
        this.lastActivity = Date.now();
        
        // Hide warning if shown
        if (this.warningShown) {
            this.hideWarning();
        }
        
        // Auto refresh if enabled and close to expiration
        if (this.autoRefresh) {
            this.checkAndRefresh();
        }
    }

    startChecking() {
        this.checkTimer = setInterval(() => {
            this.checkSession();
        }, this.checkInterval);
    }

    stopChecking() {
        if (this.checkTimer) {
            clearInterval(this.checkTimer);
            this.checkTimer = null;
        }
    }

    checkSession() {
        const elapsed = Date.now() - this.lastActivity;
        const remaining = this.sessionTimeout - elapsed;

        console.log(`[Session] Remaining: ${Math.floor(remaining / 1000)}s`);

        // Session expired
        if (remaining <= 0) {
            this.handleExpiration();
            return;
        }

        // Show warning
        if (remaining <= this.warningTime && !this.warningShown) {
            this.showWarning(remaining);
        }

        // Update warning countdown
        if (this.warningShown) {
            this.updateWarningCountdown(remaining);
        }
    }

    async checkAndRefresh() {
        const elapsed = Date.now() - this.lastActivity;
        const remaining = this.sessionTimeout - elapsed;

        // Refresh if less than threshold remaining
        if (remaining <= this.refreshThreshold && remaining > this.warningTime) {
            try {
                await this.refreshSession();
                console.log('[Session] Auto-refreshed successfully');
            } catch (error) {
                console.error('[Session] Auto-refresh failed:', error);
            }
        }
    }

    async refreshSession() {
        try {
            const response = await fetch('/api/auth/refresh', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include', // Include cookies
            });

            if (!response.ok) {
                throw new Error('Refresh failed');
            }

            const data = await response.json();
            
            // Reset activity timer
            this.lastActivity = Date.now();
            
            // Hide warning if shown
            if (this.warningShown) {
                this.hideWarning();
            }

            return data;
        } catch (error) {
            console.error('[Session] Refresh error:', error);
            throw error;
        }
    }

    handleExpiration() {
        this.stopChecking();
        
        // Show expiration message
        alert('Session Anda telah berakhir. Silakan login kembali.');
        
        // Redirect to login
        window.location.href = '/login';
    }

    createWarningModal() {
        const modal = document.createElement('div');
        modal.id = 'session-warning-modal';
        modal.style.cssText = `
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            justify-content: center;
            align-items: center;
        `;

        modal.innerHTML = `
            <div style="
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                max-width: 400px;
                text-align: center;
            ">
                <div style="font-size: 48px; margin-bottom: 20px;">⏰</div>
                <h3 style="margin: 0 0 15px 0; color: #333;">Session Akan Berakhir</h3>
                <p style="margin: 0 0 20px 0; color: #666;">
                    Session Anda akan berakhir dalam <strong id="session-countdown">5:00</strong>
                </p>
                <p style="margin: 0 0 20px 0; color: #999; font-size: 14px;">
                    Klik tombol di bawah untuk memperpanjang session
                </p>
                <button id="session-extend-btn" style="
                    background: #007bff;
                    color: white;
                    border: none;
                    padding: 12px 30px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 16px;
                    font-weight: 500;
                ">
                    Perpanjang Session
                </button>
            </div>
        `;

        document.body.appendChild(modal);
        this.warningModal = modal;

        // Add click handler for extend button
        const extendBtn = modal.querySelector('#session-extend-btn');
        extendBtn.addEventListener('click', async () => {
            extendBtn.disabled = true;
            extendBtn.textContent = 'Memproses...';
            
            try {
                await this.refreshSession();
                this.hideWarning();
                
                // Show success message
                this.showToast('Session berhasil diperpanjang', 'success');
            } catch (error) {
                this.showToast('Gagal memperpanjang session', 'error');
                extendBtn.disabled = false;
                extendBtn.textContent = 'Perpanjang Session';
            }
        });
    }

    showWarning(remainingMs) {
        if (this.warningModal) {
            this.warningModal.style.display = 'flex';
            this.warningShown = true;
            this.updateWarningCountdown(remainingMs);
        }
    }

    hideWarning() {
        if (this.warningModal) {
            this.warningModal.style.display = 'none';
            this.warningShown = false;
        }
    }

    updateWarningCountdown(remainingMs) {
        const countdown = this.warningModal?.querySelector('#session-countdown');
        if (countdown) {
            const minutes = Math.floor(remainingMs / 60000);
            const seconds = Math.floor((remainingMs % 60000) / 1000);
            countdown.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007bff'};
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 10001;
            animation: slideIn 0.3s ease-out;
        `;
        toast.textContent = message;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    destroy() {
        this.stopChecking();
        if (this.warningModal) {
            this.warningModal.remove();
        }
    }

    // Public API
    getRemainingTime() {
        const elapsed = Date.now() - this.lastActivity;
        return Math.max(0, this.sessionTimeout - elapsed);
    }

    getRemainingMinutes() {
        return Math.floor(this.getRemainingTime() / 60000);
    }

    async extendSession() {
        return await this.refreshSession();
    }
}

// Add CSS animations
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
`;
document.head.appendChild(style);

// Export for use in other scripts
window.SessionManager = SessionManager;

// Auto-initialize if not in login page
if (!window.location.pathname.includes('/login')) {
    document.addEventListener('DOMContentLoaded', () => {
        window.sessionManager = new SessionManager({
            sessionTimeout: 30 * 60 * 1000,  // 30 minutes
            warningTime: 5 * 60 * 1000,      // 5 minutes warning
            autoRefresh: true,                // Auto refresh enabled
            refreshThreshold: 10 * 60 * 1000, // Refresh when 10 min remaining
        });
        
        console.log('[Session] Session manager initialized');
    });
}

