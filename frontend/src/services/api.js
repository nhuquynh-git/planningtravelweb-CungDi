
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

// Token storage key
const TOKEN_KEY = 'auth_token';

/**
 * Get stored token
 */
function getToken() {
    return localStorage.getItem(TOKEN_KEY);
}

/**
 * Make API request with authentication
 */
async function request(endpoint, options = {}) {
    const token = getToken();
    
    const headers = {
        Accept: "application/json",
        "Content-Type": "application/json",
        ...options.headers,
    };

    // Add authorization header if token exists
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
        ...options,
        headers,
        // Include credentials for cookie-based auth
        credentials: 'include',
    });

    const data = await response.json();

    if (!response.ok) {
        // Handle 401 Unauthorized
        if (response.status === 401) {
            localStorage.removeItem(TOKEN_KEY);
            localStorage.removeItem('auth_user');
        }
        throw new Error(data.message || "API request failed");
    }

    return data;
}

export const api = {
    get: (endpoint) => request(endpoint),

    post: (endpoint, body) =>
        request(endpoint, {
            method: "POST",
            body: JSON.stringify(body),
        }),

    put: (endpoint, body) =>
        request(endpoint, {
            method: "PUT",
            body: JSON.stringify(body),
        }),

    delete: (endpoint) =>
        request(endpoint, {
            method: "DELETE",
        }),
};
