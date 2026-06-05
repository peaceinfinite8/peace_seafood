<?php
/**
 * Grace Period Banner Component
 * Shows warning banner when subscription is about to expire
 * This is a standalone Alpine.js component (not nested)
 */
?>

<style>
    /* Grace Period Alert Banner Styles */
    .gpa-banner {
        position: relative;
        width: 100%;
        border-radius: 8px;
        padding: 12px 20px;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        transition: all 0.3s ease;
    }

    .gpa-banner-yellow {
        background: rgba(245, 158, 11, 0.15);
        border-left: 4px solid #F59E0B;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }

    .gpa-banner-red {
        background: rgba(239, 68, 68, 0.15);
        border-left: 4px solid #EF4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .gpa-banner-content {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .gpa-banner-icon {
        flex-shrink: 0;
        width: 24px;
        height: 24px;
    }

    .gpa-banner-text {
        flex: 1;
    }

    .gpa-banner-title {
        font-weight: 600;
        font-size: 0.875rem;
        line-height: 1.4;
    }

    .gpa-banner-yellow .gpa-banner-title {
        color: #D97706;
    }

    .gpa-banner-red .gpa-banner-title {
        color: #DC2626;
    }

    .gpa-banner-action {
        flex-shrink: 0;
    }

    .gpa-btn-whatsapp {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #25D366;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .gpa-btn-whatsapp:hover {
        background: #128C7E;
        transform: translateY(-1px);
    }

    .gpa-btn-whatsapp i {
        width: 18px;
        height: 18px;
    }

    @media (max-width: 768px) {
        .gpa-banner {
            flex-direction: column;
            align-items: flex-start;
            padding: 1rem;
        }

        .gpa-banner-content {
            width: 100%;
        }

        .gpa-banner-action {
            width: 100%;
        }

        .gpa-btn-whatsapp {
            width: 100%;
            justify-content: center;
        }
    }

    /* Alpine.js transitions */
    .gpa-fade-slide-enter {
        opacity: 0;
        transform: translateY(-10px);
    }

    .gpa-fade-slide-enter-active {
        transition: all 0.3s ease-out;
    }

    .gpa-fade-slide-enter-to {
        opacity: 1;
        transform: translateY(0);
    }
</style>

<div x-data="graceBannerComponent()" x-init="init()" x-show="show" x-cloak x-transition:enter="gpa-fade-slide-enter gpa-fade-slide-enter-active" x-transition:enter-end="gpa-fade-slide-enter-to">
    <div class="gpa-banner" :class="color === 'red' ? 'gpa-banner-red' : 'gpa-banner-yellow'">
        <div class="gpa-banner-content">
            <div class="gpa-banner-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :style="'color: ' + (color === 'red' ? '#EF4444' : '#F59E0B')">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
            </div>
            <div class="gpa-banner-text">
                <div class="gpa-banner-title" x-text="message"></div>
            </div>
        </div>
        <div class="gpa-banner-action">
            <a :href="'https://wa.me/' + whatsapp + '?text=Halo,%20saya%20ingin%20perpanjang%20masa%20aktif%20gudang'" 
               target="_blank" 
               class="gpa-btn-whatsapp">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
                <span>Hubungi via WA</span>
            </a>
        </div>
    </div>
</div>

<script>
    function graceBannerComponent() {
        return {
            show: false,
            color: 'yellow',
            days: 0,
            message: '',
            whatsapp: '628123456789',

            async init() {
                // Only show banner if user is logged in and has gudang
                const user = this.$store?.auth?.user;
                if (!user || !user.id_gudang || user.role === 'saas_owner') {
                    this.show = false;
                    return;
                }

                try {
                    const response = await apiClient.get('/api/grace-period/banner');
                    if (response.data.success && response.data.data.show) {
                        const data = response.data.data;
                        this.show = true;
                        this.color = data.color;
                        this.days = data.days;
                        this.message = data.message;
                        this.whatsapp = data.whatsapp || '628123456789';
                    }
                } catch (error) {
                    console.error('Failed to load grace banner:', error);
                    this.show = false;
                }
            }
        };
    }
</script>

