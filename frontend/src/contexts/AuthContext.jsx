import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import { api } from '../services/api';

const AuthContext = createContext(null);

// Token storage key
const TOKEN_KEY = 'auth_token';
const USER_KEY = 'auth_user';

/**
 * Auth Provider Component
 * Manages authentication state across the application
 */
export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Load user from storage on mount
  useEffect(() => {
    const initializeAuth = async () => {
      const token = localStorage.getItem(TOKEN_KEY);
      const storedUser = localStorage.getItem(USER_KEY);

      if (token && storedUser) {
        try {
          // Verify token by fetching current user
          const response = await api.get('/auth/me');
          if (response.status === 'success') {
            setUser(response.data);
            // Update stored user data
            localStorage.setItem(USER_KEY, JSON.stringify(response.data));
          } else {
            // Token invalid, clear storage
            localStorage.removeItem(TOKEN_KEY);
            localStorage.removeItem(USER_KEY);
          }
        } catch (err) {
          // Token invalid or network error, clear storage
          localStorage.removeItem(TOKEN_KEY);
          localStorage.removeItem(USER_KEY);
        }
      }

      setLoading(false);
    };

    initializeAuth();
  }, []);

  // Register new user
  const register = useCallback(async (credentials) => {
    setError(null);
    try {
      const response = await api.post('/auth/register', credentials);
      
      if (response.status === 'success') {
        const { user, token } = response.data;
        
        // Store token and user
        localStorage.setItem(TOKEN_KEY, token);
        localStorage.setItem(USER_KEY, JSON.stringify(user));
        setUser(user);
        
        return { success: true, user, token };
      }
      
      throw new Error(response.message || 'Đăng ký thất bại');
    } catch (err) {
      const message = err.message || 'Đăng ký thất bại. Vui lòng thử lại.';
      setError(message);
      return { success: false, error: message };
    }
  }, []);

  // Login user
  const login = useCallback(async (credentials) => {
    setError(null);
    try {
      const response = await api.post('/auth/login', credentials);
      
      if (response.status === 'success') {
        const { user, token } = response.data;
        
        // Store token and user
        localStorage.setItem(TOKEN_KEY, token);
        localStorage.setItem(USER_KEY, JSON.stringify(user));
        setUser(user);
        
        return { success: true, user, token };
      }
      
      throw new Error(response.message || 'Đăng nhập thất bại');
    } catch (err) {
      const message = err.message || 'Email hoặc mật khẩu không đúng';
      setError(message);
      return { success: false, error: message };
    }
  }, []);

  // Logout user
  const logout = useCallback(async () => {
    try {
      await api.post('/auth/logout');
    } catch (err) {
      // Continue with logout even if API fails
      console.error('Logout API error:', err);
    } finally {
      // Clear storage and state
      localStorage.removeItem(TOKEN_KEY);
      localStorage.removeItem(USER_KEY);
      setUser(null);
    }
  }, []);

  // Update user profile
  const updateProfile = useCallback(async (data) => {
    setError(null);
    try {
      const response = await api.put('/auth/profile', data);
      
      if (response.status === 'success') {
        // Update local user state with new profile
        const updatedUser = {
          ...user,
          profile: response.data.profile,
        };
        setUser(updatedUser);
        localStorage.setItem(USER_KEY, JSON.stringify(updatedUser));
        
        return { success: true };
      }
      
      throw new Error(response.message || 'Cập nhật thất bại');
    } catch (err) {
      const message = err.message || 'Cập nhật hồ sơ thất bại';
      setError(message);
      return { success: false, error: message };
    }
  }, [user]);

  // Check if user has specific role
  const hasRole = useCallback((role) => {
    if (!user || !user.groups) return false;
    return user.groups.includes(role);
  }, [user]);

  // Check if user has specific permission
  const hasPermission = useCallback((permission) => {
    if (!user || !user.permissions) return false;
    return user.permissions.includes(permission) || user.permissions.includes('*');
  }, [user]);

  const value = {
    user,
    loading,
    error,
    isAuthenticated: !!user,
    isAdmin: hasRole('admin'),
    isUser: hasRole('user'),
    hasRole,
    hasPermission,
    register,
    login,
    logout,
    updateProfile,
    clearError: () => setError(null),
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
}

/**
 * Hook to access auth context
 */
export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}

/**
 * Hook to require authentication
 * Redirects to login if not authenticated
 */
export function useRequireAuth() {
  const { isAuthenticated, loading } = useAuth();
  
  if (loading) {
    return { isAuthenticated: false, loading: true };
  }
  
  return { isAuthenticated, loading: false };
}