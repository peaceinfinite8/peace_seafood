/**
 * Peace Seafood - API Client
 * Axios wrapper with JWT auth and error handling
 */

const API_BASE = '/peace_seafood/api';

const apiClient = axios.create({
    baseURL: API_BASE,
    timeout: 30000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Request interceptor - attach JWT token
apiClient.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token');
        if (token) {
            config.headers['Authorization'] = 'Bearer ' + token;
        }
        return config;
    },
    (error) => Promise.reject(error)
);

// Response interceptor - handle auth errors
apiClient.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/peace_seafood/login';
        }
        return Promise.reject(error);
    }
);

// Helper functions
window.apiClient = apiClient;

function formatRupiah(amount) {
    return 'Rp ' + (parseFloat(amount) || 0).toLocaleString('id-ID', { minimumFractionDigits: 0 });
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

function formatDateShort(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID');
}

function showToast(type, title, message) {
    if (window.iziToast) {
        iziToast[type]({ title, message, position: 'topRight', timeout: 3000 });
    }
}

window.formatRupiah  = formatRupiah;
window.formatDate    = formatDate;
window.formatDateShort = formatDateShort;
window.showToast     = showToast;
