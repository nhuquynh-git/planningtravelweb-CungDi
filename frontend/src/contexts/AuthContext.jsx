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

  // Fetch current user helper
  const fetchCurrentUser = useCallback(async () => {
    try {
      const response = await api.get('/auth/me');
      if (response.status === 'success' && response.data) {
        setUser(response.data);
        localStorage.setItem(USER_KEY, JSON.stringify(response.data));
        return { success: true, user: response.data };
      }
    } catch (err) {
      console.warn('Không thể lấy thông tin người dùng:', err.message);
    }
    return { success: false };
  }, []);

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

  // Lưu phiên đăng nhập (chỉ gọi khi chắc chắn có token)
  const saveSession = useCallback((user, token) => {
    localStorage.setItem(TOKEN_KEY, token);
    localStorage.setItem(USER_KEY, JSON.stringify(user));
    setUser(user);
  }, []);

  // Register new user
  const register = useCallback(async (credentials) => {
    setError(null);
    try {
      const response = await api.post('/auth/register', credentials);

      if (response.status === 'success') {
        const data = response.data || {};

        // Cần xác nhận OTP: KHÔNG lưu token, KHÔNG set user
        if (data.status === 'need_verification') {
          return {
            success: false,
            needVerification: true,
            email: data.email,
            message: response.message,
            otpPreview: data.otp_preview,
          };
        }

        if (data.token && data.user) {
          saveSession(data.user, data.token);
          return { success: true, user: data.user, token: data.token };
        }
      }

      throw new Error(response.message || 'Đăng ký thất bại');
    } catch (err) {
      const message = err.message || 'Đăng ký thất bại. Vui lòng thử lại.';
      setError(message);
      return { success: false, error: message };
    }
  }, [saveSession]);

  // Login user
  const login = useCallback(async (credentials) => {
    setError(null);
    try {
      const response = await api.post('/auth/login', credentials);

      if (response.status === 'success') {
        const data = response.data || {};

        if (data.status === 'need_verification') {
          return {
            success: false,
            needVerification: true,
            email: data.email,
            message: response.message,
            otpPreview: data.otp_preview,
          };
        }

        if (data.token && data.user) {
          saveSession(data.user, data.token);
          return { success: true, user: data.user, token: data.token };
        }
      }

      throw new Error(response.message || 'Đăng nhập thất bại');
    } catch (err) {
      const message = err.message || 'Email hoặc mật khẩu không đúng';
      setError(message);
      return { success: false, error: message };
    }
  }, [saveSession]);

  const clearError = useCallback(() => setError(null), []);

  // Xác nhận OTP 6 số -> backend tạo tài khoản + trả token
  const verifyOtp = useCallback(async ({ email, otp }) => {
    try {
      const response = await api.post('/auth/verify-otp', { email, otp });
      const { user, token } = response.data || {};

      if (response.status !== 'success' || !user || !token) {
        throw new Error(response.message || 'Dữ liệu xác minh OTP không hợp lệ');
      }

      saveSession(user, token);
      return { success: true, user, token };
    } catch (err) {
      return { success: false, message: err.message || 'Xác minh OTP thất bại' };
    }
  }, [saveSession]);

  // Quên mật khẩu: gửi mã
  const forgotPassword = useCallback(async (email) => {
    try {
      const response = await api.post('/auth/forgot-password', { email });
      return { success: true, message: response.message, otpPreview: response.data?.otp_preview };
    } catch (err) {
      return { success: false, error: err.message || 'Không thể gửi mã' };
    }
  }, []);

  // Quên mật khẩu: đổi mật khẩu bằng mã
  const resetPassword = useCallback(async ({ email, otp, password }) => {
    try {
      const response = await api.post('/auth/reset-password', { email, otp, password });
      return { success: true, message: response.message };
    } catch (err) {
      return { success: false, error: err.message || 'Đặt lại mật khẩu thất bại' };
    }
  }, []);

  // Đặt mật khẩu lần đầu (tài khoản Google/Facebook)
  const setInitialPassword = useCallback(async (password) => {
    try {
      await api.post('/auth/set-password', { password });
      await fetchCurrentUser(); // cập nhật needs_password_setup = false
      return { success: true };
    } catch (err) {
      return { success: false, error: err.message || 'Thiết lập mật khẩu thất bại' };
    }
  }, [fetchCurrentUser]);

  // Gửi lại OTP
  const resendOtp = useCallback(async (email) => {
    try {
      const response = await api.post('/auth/resend-otp', { email });
      return {
        success: true,
        message: response.message,
        otpPreview: response.data?.otp_preview,
      };
    } catch (err) {
      return { success: false, error: err.message || 'Không thể gửi lại mã' };
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
    verifyOtp,     // mới
    resendOtp,     // mới
    forgotPassword,
    resetPassword,
    setInitialPassword,
    needsPasswordSetup: !!user?.needs_password_setup,
    logout,
    updateProfile,
    fetchCurrentUser,
    clearError,
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
