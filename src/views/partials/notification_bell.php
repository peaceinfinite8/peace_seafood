<?php
/**
 * Notification Bell Component
 * Real-time notification bell icon with dropdown
 * Standalone Alpine.js component
 */
?>

<style>
    /* Notification Bell Styles */
    .gpa-notif-bell-container {
        position: relative;
    }

    .gpa-notif-bell-btn {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: var(--bg-light);
        border: 1px solid var(--border-color);
        cursor: pointer;
        transition: all 0.2s;
    }

    .gpa-notif-bell-btn:hover {
        background: var(--color-primary-light);
        border-color: var(--color-primary);
    }

    .gpa-notif-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #EF4444;
        color: white;
        font-size: 10px;
        font-weight: 700;
        border-radius: 9px;
        border: 2px solid var(--bg-light);
    }

    .gpa-notif-dropdown {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        width: 380px;
        max-width: 90vw;
        background: var(--bg-light);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        z-index: 1000;
        overflow: hidden;
    }

    .gpa-notif-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .gpa-notif-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .gpa-notif-mark-all {
        font-size: 12px;
        color: var(--color-primary);
        cursor: pointer;
        font-weight: 600;
    }

    .gpa-notif-mark-all:hover {
        text-decoration: underline;
    }

    .gpa-notif-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .gpa-notif-item {
        padding: 12px 20px;
        border-bottom: 1px solid var(--border-color);
        cursor: pointer;
        transition: background 0.2s;
    }

    .gpa-notif-item:hover {
        background: var(--bg-gray);
    }

    .gpa-notif-item.unread {
        background: rgba(37, 99, 235, 0.04);
        border-left: 3px solid var(--color-primary);
    }

    .gpa-notif-item-header {
        display: flex;
        align-items: start;
        gap: 8px;
        margin-bottom: 4px;
    }

    .gpa-notif-item-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .gpa-notif-item-content {
        flex: 1;
        min-width: 0;
    }

    .gpa-notif-item-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .gpa-notif-item-message {
        font-size: 13px;
        color: var(--text-secondary);
        line-height: 1.4;
    }

    .gpa-notif-item-time {
        font-size: 11px;
        color: var(--text-secondary);
        margin-top: 4px;
    }

    .gpa-notif-empty {
        padding: 40px 20px;
        text-align: center;
        color: var(--text-secondary);
    }

    .gpa-notif-empty-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 12px;
        opacity: 0.4;
    }

    @media (max-width: 640px) {
        .gpa-notif-dropdown {
            width: 320px;
        }
    }
</style>

<div x-data="notificationBellComponent()" x-init="init()" class="gpa-notif-bell-container" x-cloak>
    <!-- Bell Button -->
    <button type="button" 
            @click="toggleDropdown()" 
            class="gpa-notif-bell-btn"
            :disabled="isImpersonating">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-primary)">
            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
        </svg>
        <span x-show="unreadCount > 0" class="gpa-notif-badge" x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
    </button>

    <!-- Dropdown -->
    <div x-show="dropdownOpen" 
         @click.away="dropdownOpen = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="gpa-notif-dropdown">
        
        <!-- Header -->
        <div class="gpa-notif-header">
            <div class="gpa-notif-title">Notifikasi</div>
            <div class="gpa-notif-mark-all" @click="markAllAsRead()" x-show="unreadCount > 0">
                Tandai semua dibaca
            </div>
        </div>

        <!-- Loading -->
        <div x-show="loading" class="gpa-notif-empty">
            <div class="animate-spin w-8 h-8 mx-auto border-4 rounded-full" style="border-color: var(--color-primary); border-top-color: transparent;"></div>
        </div>

        <!-- List -->
        <div x-show="!loading && notifications.length > 0" class="gpa-notif-list custom-scrollbar-thin">
            <template x-for="notif in notifications" :key="notif.id">
                <div class="gpa-notif-item" 
                     :class="notif.is_read == 0 ? 'unread' : ''"
                     @click="handleNotificationClick(notif)">
                    <div class="gpa-notif-item-header">
                        <div class="gpa-notif-item-icon" :style="'background: ' + getIconBg(notif.tipe)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :style="'color: ' + getIconColor(notif.tipe)" x-html="getIcon(notif.tipe)"></svg>
                        </div>
                        <div class="gpa-notif-item-content">
                            <div class="gpa-notif-item-title" x-text="notif.judul"></div>
                            <div class="gpa-notif-item-message" x-text="notif.pesan"></div>
                            <div class="gpa-notif-item-time" x-text="formatTime(notif.created_at)"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && notifications.length === 0" class="gpa-notif-empty">
            <svg class="gpa-notif-empty-icon" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
            </svg>
            <div style="font-size: 14px; font-weight: 600;">Tidak ada notifikasi</div>
        </div>
    </div>
</div>

<script>
    function notificationBellComponent() {
        return {
            dropdownOpen: false,
            unreadCount: 0,
            notifications: [],
            loading: false,
            pollingInterval: null,
            isImpersonating: false,

            async init() {
                // Check if user is in impersonation mode
                const user = this.$store?.auth?.user;
                if (!user) return;

                // Disable polling if SaaS Owner is impersonating
                if (user.role === 'saas_owner' && sessionStorage.getItem('impersonating') === 'true') {
                    this.isImpersonating = true;
                    return;
                }

                // Initial load
                await this.fetchUnreadCount();

                // Start polling every 60 seconds
                this.pollingInterval = setInterval(() => {
                    this.fetchUnreadCount();
                }, 60000);
            },

            destroy() {
                if (this.pollingInterval) {
                    clearInterval(this.pollingInterval);
                }
            },

            async fetchUnreadCount() {
                try {
                    const response = await apiClient.get('/api/notifications/unread-count');
                    if (response.data.success) {
                        this.unreadCount = response.data.data.count || 0;
                    }
                } catch (error) {
                    console.error('Failed to fetch unread count:', error);
                }
            },

            async toggleDropdown() {
                this.dropdownOpen = !this.dropdownOpen;
                
                if (this.dropdownOpen && this.notifications.length === 0) {
                    await this.loadNotifications();
                }
            },

            async loadNotifications() {
                this.loading = true;
                try {
                    const response = await apiClient.get('/api/notifications');
                    if (response.data.success) {
                        this.notifications = response.data.data || [];
                    }
                } catch (error) {
                    console.error('Failed to load notifications:', error);
                } finally {
                    this.loading = false;
                }
            },

            async handleNotificationClick(notif) {
                // Mark as read if unread
                if (notif.is_read == 0) {
                    try {
                        await apiClient.post(`/api/notifications/${notif.id}/read`);
                        notif.is_read = 1;
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    } catch (error) {
                        console.error('Failed to mark as read:', error);
                    }
                }

                // Navigate to action URL if exists
                if (notif.action_url) {
                    window.location.href = notif.action_url;
                }

                this.dropdownOpen = false;
            },

            async markAllAsRead() {
                try {
                    await apiClient.post('/api/notifications/mark-all-read');
                    this.notifications = this.notifications.map(n => ({ ...n, is_read: 1 }));
                    this.unreadCount = 0;
                    iziToast.success({ title: 'Sukses', message: 'Semua notifikasi ditandai sudah dibaca' });
                } catch (error) {
                    console.error('Failed to mark all as read:', error);
                    iziToast.error({ title: 'Error', message: 'Gagal menandai notifikasi' });
                }
            },

            getIconBg(tipe) {
                const colors = {
                    'grace_period': 'rgba(245, 158, 11, 0.1)',
                    'payment': 'rgba(16, 185, 129, 0.1)',
                    'retur': 'rgba(239, 68, 68, 0.1)',
                    'stok': 'rgba(37, 99, 235, 0.1)',
                    'user': 'rgba(139, 92, 246, 0.1)',
                    'default': 'rgba(100, 116, 139, 0.1)'
                };
                return colors[tipe] || colors.default;
            },

            getIconColor(tipe) {
                const colors = {
                    'grace_period': '#F59E0B',
                    'payment': '#10B981',
                    'retur': '#EF4444',
                    'stok': '#2563EB',
                    'user': '#8B5CF6',
                    'default': '#64748B'
                };
                return colors[tipe] || colors.default;
            },

            getIcon(tipe) {
                const icons = {
                    'grace_period': '<path d="M12 2v20m0-20l6 6m-6-6L6 8"/><circle cx="12" cy="12" r="10"/>',
                    'payment': '<path d="M12 2v20m0-20l6 6m-6-6L6 8"/><path d="M12 22l6-6m-6 6l-6-6"/>',
                    'retur': '<path d="M3 12a9 9 0 1 0 18 0 9 9 0 1 0-18 0"/><path d="M12 8v8m-4-4h8"/>',
                    'stok': '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/>',
                    'user': '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
                    'default': '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'
                };
                return icons[tipe] || icons.default;
            },

            formatTime(timestamp) {
                const date = new Date(timestamp);
                const now = new Date();
                const diff = Math.floor((now - date) / 1000); // seconds

                if (diff < 60) return 'Baru saja';
                if (diff < 3600) return Math.floor(diff / 60) + ' menit lalu';
                if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
                if (diff < 604800) return Math.floor(diff / 86400) + ' hari lalu';
                
                return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            }
        };
    }
</script>

