/**
 * CSRF Protection Helper
 * Automatically adds CSRF token to AJAX requests
 */

// Store CSRF token (set after login)
let csrfToken = null;

/**
 * Set CSRF token (call after login)
 */
function setCsrfToken(token) {
    csrfToken = token;
    localStorage.setItem('csrf_token', token);
}

/**
 * Get CSRF token
 */
function getCsrfToken() {
    if (!csrfToken) {
        csrfToken = localStorage.getItem('csrf_token');
    }
    return csrfToken;
}

/**
 * Clear CSRF token (call on logout)
 */
function clearCsrfToken() {
    csrfToken = null;
    localStorage.removeItem('csrf_token');
}

/**
 * Add CSRF token to fetch request headers
 */
function addCsrfHeader(headers = {}) {
    const token = getCsrfToken();
    if (token) {
        headers['X-CSRF-Token'] = token;
    }
    return headers;
}

/**
 * Add CSRF token to FormData
 */
function addCsrfToFormData(formData) {
    const token = getCsrfToken();
    if (token) {
        formData.append('csrf_token', token);
    }
    return formData;
}

/**
 * Get CSRF token as hidden input HTML
 */
function getCsrfInput() {
    const token = getCsrfToken();
    return `<input type="hidden" name="csrf_token" value="${token || ''}">`;
}

// Intercept apiClient to automatically add CSRF token
if (window.apiClient) {
    // Add request interceptor
    const originalRequest = window.apiClient.request;
    
    window.apiClient.request = function(config) {
        // Add CSRF token to headers for state-changing methods
        if (['post', 'put', 'delete', 'patch'].includes(config.method?.toLowerCase())) {
            config.headers = addCsrfHeader(config.headers || {});
        }
        
        return originalRequest.call(this, config);
    };
}

// Auto-set CSRF token from login response
document.addEventListener('DOMContentLoaded', function() {
    // Listen for login success
    document.addEventListener('login-success', function(event) {
        if (event.detail && event.detail.csrf_token) {
            setCsrfToken(event.detail.csrf_token);
        }
    });
    
    // Listen for logout
    document.addEventListener('logout', function() {
        clearCsrfToken();
    });
});
