<?php
/**
 * Centralized Activity Logs - SaaS Owner Only
 * View all tenant activity logs from one place
 */

$pageTitle = 'Centralized Activity Logs';
$activeMenu = 'saas-logs';

// Include layout
ob_start();
?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 p-4 md:p-8" 
     x-data="centralizedLogs()" 
     x-init="init()">
    
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white flex items-center gap-3">
                    <i data-lucide="activity" class="w-8 h-8 text-cyan-500"></i>
                    Centralized Activity Logs
                </h1>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                    Monitor all tenant activities across the platform
                </p>
            </div>
            
            <button @click="exportLogs()" 
                    :disabled="isExporting"
                    class="px-4 py-2 bg-green-500 hover:bg-green-600 disabled:bg-gray-400 text-white rounded-lg flex items-center gap-2 transition-colors">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span x-text="isExporting ? 'Exporting...' : 'Export CSV'"></span>
            </button>
        </div>
    </div>
    
    <!-- Filters Card -->
    <div class="card p-6 mb-6">
        <div class="flex items-center gap-2 mb-4">
            <i data-lucide="filter" class="w-5 h-5 text-slate-600 dark:text-slate-400"></i>
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Filters</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Gudang Filter -->
            <div>
                <label class="form-label">Gudang</label>
                <select x-model="filters.id_gudang" 
                        @change="applyFilters()" 
                        class="form-input">
                    <option value="">Semua Gudang</option>
                    <template x-for="g in filterOptions.gudangs" :key="g.id">
                        <option :value="g.id" x-text="g.nama_gudang"></option>
                    </template>
                </select>
            </div>
            
            <!-- Date Range -->
            <div>
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" 
                       x-model="filters.start_date" 
                       @change="applyFilters()"
                       class="form-input">
            </div>
            
            <div>
                <label class="form-label">Tanggal Akhir</label>
                <input type="date" 
                       x-model="filters.end_date" 
                       @change="applyFilters()"
                       class="form-input">
            </div>
            
            <!-- Action Filter -->
            <div>
                <label class="form-label">Action</label>
                <select x-model="filters.action" 
                        @change="applyFilters()" 
                        class="form-input">
                    <option value="">Semua Action</option>
                    <template x-for="action in filterOptions.actions" :key="action">
                        <option :value="action" x-text="action"></option>
                    </template>
                </select>
            </div>
            
            <!-- Role Filter -->
            <div>
                <label class="form-label">Role</label>
                <select x-model="filters.role" 
                        @change="applyFilters()" 
                        class="form-input">
                    <option value="">Semua Role</option>
                    <template x-for="role in filterOptions.roles" :key="role.value">
                        <option :value="role.value" x-text="role.label"></option>
                    </template>
                </select>
            </div>
            
            <!-- Table Filter -->
            <div>
                <label class="form-label">Table</label>
                <select x-model="filters.table_name" 
                        @change="applyFilters()" 
                        class="form-input">
                    <option value="">Semua Table</option>
                    <template x-for="table in filterOptions.tables" :key="table">
                        <option :value="table" x-text="table"></option>
                    </template>
                </select>
            </div>
            
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="form-label">Search</label>
                <div class="relative">
                    <input type="text" 
                           x-model="filters.search" 
                           @keyup.enter="applyFilters()"
                           placeholder="Search by description, action, or user name..."
                           class="form-input pl-10">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-3 text-slate-400"></i>
                </div>
            </div>
            
        </div>
        
        <div class="flex items-center gap-2 mt-4">
            <button @click="applyFilters()" 
                    class="btn btn-primary">
                <i data-lucide="search" class="w-4 h-4"></i>
                Apply Filters
            </button>
            <button @click="resetFilters()" 
                    class="btn btn-secondary">
                <i data-lucide="x" class="w-4 h-4"></i>
                Reset
            </button>
        </div>
    </div>
    
    <!-- Stats Card -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase font-semibold">Total Logs</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white" x-text="pagination.total"></p>
                </div>
                <i data-lucide="database" class="w-8 h-8 text-blue-500"></i>
            </div>
        </div>
        
        <div class="card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase font-semibold">Current Page</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white" x-text="pagination.page + ' / ' + pagination.total_pages"></p>
                </div>
                <i data-lucide="layers" class="w-8 h-8 text-green-500"></i>
            </div>
        </div>
        
        <div class="card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase font-semibold">Per Page</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white" x-text="pagination.limit"></p>
                </div>
                <i data-lucide="file-text" class="w-8 h-8 text-purple-500"></i>
            </div>
        </div>
        
        <div class="card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase font-semibold">Showing</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white" x-text="logs.length"></p>
                </div>
                <i data-lucide="list" class="w-8 h-8 text-orange-500"></i>
            </div>
        </div>
    </div>
    
    <!-- Loading State -->
    <div x-show="isLoading" class="card p-12 text-center">
        <i data-lucide="loader-2" class="w-12 h-12 mx-auto mb-4 text-cyan-500 animate-spin"></i>
        <p class="text-slate-600 dark:text-slate-400">Loading logs...</p>
    </div>
    
    <!-- Logs Table -->
    <div x-show="!isLoading && logs.length > 0" class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Gudang</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Table</th>
                        <th>Description</th>
                        <th>IP</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="log in logs" :key="log.id">
                        <tr>
                            <td>
                                <div class="text-xs">
                                    <div class="font-semibold" x-text="log.timestamp_formatted"></div>
                                    <div class="text-slate-500" x-text="new Date(log.timestamp).toLocaleTimeString('id-ID')"></div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-info" x-text="log.nama_gudang || '-'"></span>
                            </td>
                            <td>
                                <div class="text-sm">
                                    <div class="font-semibold" x-text="log.nama_user || '-'"></div>
                                    <div class="text-xs text-slate-500" x-text="log.email_user || '-'"></div>
                                </div>
                            </td>
                            <td>
                                <span class="badge" 
                                      :class="{
                                          'badge-danger': log.role_user === 'saas_owner',
                                          'badge-warning': log.role_user === 'bos',
                                          'badge-success': log.role_user === 'admin',
                                          'badge-info': log.role_user === 'checker',
                                          'badge-gray': !['saas_owner', 'bos', 'admin', 'checker'].includes(log.role_user)
                                      }"
                                      x-text="log.role_user || '-'"></span>
                            </td>
                            <td>
                                <code class="text-xs bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded" 
                                      x-text="log.action"></code>
                            </td>
                            <td>
                                <span class="text-xs font-mono text-slate-600 dark:text-slate-400" 
                                      x-text="log.table_name || '-'"></span>
                            </td>
                            <td>
                                <div class="max-w-xs truncate text-sm" 
                                     :title="log.description"
                                     x-text="log.description || '-'"></div>
                            </td>
                            <td>
                                <span class="text-xs font-mono text-slate-500" 
                                      x-text="log.ip_address || '-'"></span>
                            </td>
                            <td>
                                <template x-if="log.ref_url">
                                    <a :href="log.ref_url" 
                                       target="_blank"
                                       class="text-cyan-500 hover:text-cyan-600 text-xs flex items-center gap-1">
                                        <i data-lucide="external-link" class="w-3 h-3"></i>
                                        View
                                    </a>
                                </template>
                                <template x-if="!log.ref_url">
                                    <span class="text-xs text-slate-400">-</span>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Empty State -->
    <div x-show="!isLoading && logs.length === 0" class="card p-12 text-center">
        <i data-lucide="inbox" class="w-16 h-16 mx-auto mb-4 text-slate-300 dark:text-slate-600"></i>
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">No Logs Found</h3>
        <p class="text-slate-600 dark:text-slate-400">
            Try adjusting your filters or date range
        </p>
    </div>
    
    <!-- Pagination -->
    <div x-show="!isLoading && logs.length > 0" class="card p-4 mt-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="text-sm text-slate-600 dark:text-slate-400">
                Showing <span class="font-semibold" x-text="((pagination.page - 1) * pagination.limit) + 1"></span> 
                to <span class="font-semibold" x-text="Math.min(pagination.page * pagination.limit, pagination.total)"></span>
                of <span class="font-semibold" x-text="pagination.total"></span> logs
            </div>
            
            <div class="flex items-center gap-2">
                <button @click="changePage(pagination.page - 1)" 
                        :disabled="!pagination.has_prev"
                        class="btn btn-secondary disabled:opacity-50 disabled:cursor-not-allowed">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    Previous
                </button>
                
                <div class="flex items-center gap-1">
                    <template x-for="page in visiblePages()" :key="page">
                        <button @click="changePage(page)" 
                                class="w-10 h-10 rounded-lg font-semibold transition-colors"
                                :class="page === pagination.page 
                                    ? 'bg-cyan-500 text-white' 
                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                                x-text="page"></button>
                    </template>
                </div>
                
                <button @click="changePage(pagination.page + 1)" 
                        :disabled="!pagination.has_next"
                        class="btn btn-secondary disabled:opacity-50 disabled:cursor-not-allowed">
                    Next
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>
    
</div>

<script>
function centralizedLogs() {
    return {
        logs: [],
        isLoading: false,
        isExporting: false,
        filters: {
            id_gudang: '',
            start_date: '',
            end_date: '',
            action: '',
            role: '',
            table_name: '',
            search: ''
        },
        filterOptions: {
            gudangs: [],
            actions: [],
            tables: [],
            roles: []
        },
        pagination: {
            page: 1,
            limit: 50,
            total: 0,
            total_pages: 0,
            has_prev: false,
            has_next: false
        },
        
        async init() {
            await this.loadFilterOptions();
            await this.loadLogs();
            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
            });
        },
        
        async loadFilterOptions() {
            try {
                const res = await apiClient.get('/saas/logs/filters');
                this.filterOptions = res.data;
            } catch (e) {
                console.error('Failed to load filter options:', e);
            }
        },
        
        async loadLogs() {
            this.isLoading = true;
            try {
                const params = new URLSearchParams({
                    page: this.pagination.page,
                    limit: this.pagination.limit,
                    ...this.filters
                });
                
                // Remove empty filters
                for (let [key, value] of params.entries()) {
                    if (!value) params.delete(key);
                }
                
                const res = await apiClient.get('/saas/logs?' + params.toString());
                this.logs = res.data.logs || [];
                this.pagination = res.data.pagination;
                
                this.$nextTick(() => {
                    if (window.lucide) lucide.createIcons();
                });
            } catch (e) {
                console.error('Failed to load logs:', e);
                iziToast.error({
                    title: 'Error',
                    message: 'Failed to load logs',
                    position: 'topRight'
                });
            } finally {
                this.isLoading = false;
            }
        },
        
        applyFilters() {
            this.pagination.page = 1; // Reset to page 1
            this.loadLogs();
        },
        
        resetFilters() {
            this.filters = {
                id_gudang: '',
                start_date: '',
                end_date: '',
                action: '',
                role: '',
                table_name: '',
                search: ''
            };
            this.pagination.page = 1;
            this.loadLogs();
        },
        
        changePage(page) {
            if (page < 1 || page > this.pagination.total_pages) return;
            this.pagination.page = page;
            this.loadLogs();
        },
        
        visiblePages() {
            const current = this.pagination.page;
            const total = this.pagination.total_pages;
            const pages = [];
            
            // Show 5 pages around current
            let start = Math.max(1, current - 2);
            let end = Math.min(total, current + 2);
            
            // Adjust if near start or end
            if (current <= 3) {
                end = Math.min(5, total);
            }
            if (current >= total - 2) {
                start = Math.max(1, total - 4);
            }
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            
            return pages;
        },
        
        async exportLogs() {
            this.isExporting = true;
            try {
                const params = new URLSearchParams(this.filters);
                
                // Remove empty filters
                for (let [key, value] of params.entries()) {
                    if (!value) params.delete(key);
                }
                
                const url = '/peace_seafood/api/saas/logs/export?' + params.toString();
                window.location.href = url;
                
                setTimeout(() => {
                    this.isExporting = false;
                }, 2000);
            } catch (e) {
                console.error('Export failed:', e);
                iziToast.error({
                    title: 'Error',
                    message: 'Failed to export logs',
                    position: 'topRight'
                });
                this.isExporting = false;
            }
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include BASE_PATH . '/src/views/layouts/app.php';
?>
