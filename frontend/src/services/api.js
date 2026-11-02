const rawBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8001/api';
const API_BASE_URL = rawBaseUrl.replace(/\/+$/, '');

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

    const normalizedEndpoint = endpoint.startsWith('/') ? endpoint : `/${endpoint}`;

    let response;
    try {
        response = await fetch(`${API_BASE_URL}${normalizedEndpoint}`, {
            ...options,
            headers,
            // Include credentials for cookie-based auth
            credentials: 'include',
        });
    } catch (networkErr) {
        throw new Error('Không thể kết nối đến máy chủ backend tại ' + API_BASE_URL + ' (Failed to fetch). Vui lòng kiểm tra backend server.');
    }

    let data;
    const contentType = response.headers.get('content-type');
    if (contentType && contentType.includes('application/json')) {
        data = await response.json();
    } else {
        const text = await response.text();
        throw new Error(text || `Yêu cầu thất bại với mã trạng thái ${response.status}`);
    }

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
