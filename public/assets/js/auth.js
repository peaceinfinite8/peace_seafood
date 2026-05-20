/**
 * Peace Seafood - Auth Module
 * Handles login, logout, and session management
 */

const Auth = (() => {
  const TOKEN_KEY = 'token';
  const USER_KEY = 'user';

  /**
   * Login user
   */
  async function login(username, password) {
    const data = await ApiClient.post('/auth/login', { email: username, password });
    if (data.token) {
      localStorage.setItem(TOKEN_KEY, data.token);
      localStorage.setItem(USER_KEY, JSON.stringify(data.user));
    }
    return data;
  }

  /**
   * Logout user
   */
  async function logout() {
    try {
      await ApiClient.post('/auth/logout');
    } finally {
      localStorage.removeItem(TOKEN_KEY);
      localStorage.removeItem(USER_KEY);
      window.location.href = '/peace_seafood/login';
    }
  }

  /**
   * Get current user
   */
  function getUser() {
    const user = localStorage.getItem(USER_KEY);
    return user ? JSON.parse(user) : null;
  }

  /**
   * Get token
   */
  function getToken() {
    return localStorage.getItem(TOKEN_KEY);
  }

  /**
   * Check if authenticated
   */
  function isAuthenticated() {
    return !!getToken();
  }

  /**
   * Check if user has role
   */
  function hasRole(role) {
    const user = getUser();
    return user && user.role === role;
  }

  /**
   * Initialize auth check on page load
   */
  function init() {
    if (!isAuthenticated() && !window.location.pathname.includes('/login')) {
      window.location.href = '/peace_seafood/login';
    }
  }

  return { login, logout, getUser, getToken, isAuthenticated, hasRole, init };
})();

// Auto-init
document.addEventListener('DOMContentLoaded', () => Auth.init());
