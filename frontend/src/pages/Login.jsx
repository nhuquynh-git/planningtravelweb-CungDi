import { useState, useEffect } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import beachBanner from '../assets/beach-banner.jpg';
import './Auth.css';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8001/api';

export default function Login() {
    const navigate = useNavigate();
    const location = useLocation();
    const { login, error, clearError, fetchCurrentUser } = useAuth();

    const [loginInput, setLoginInput] = useState('');
    const [password, setPassword] = useState('');
    const [showPassword, setShowPassword] = useState(false);
    const [rememberMe, setRememberMe] = useState(true);

    const [loading, setLoading] = useState(false);
    const [oauthError, setOauthError] = useState(null);
    const [fieldErrors, setFieldErrors] = useState({});

    const from = location.state?.from?.pathname || '/';

    // Catch OAuth callback params redirected from Backend
    useEffect(() => {
        const searchParams = new URLSearchParams(location.search);
        const token = searchParams.get('token');
        const status = searchParams.get('status');
        const errParam = searchParams.get('error');

        if (errParam) {
            setOauthError(decodeURIComponent(errParam));
            window.history.replaceState({}, document.title, location.pathname);
            return;
        }

        if (status === 'oauth_success' && token) {
            setLoading(true);
            localStorage.setItem('auth_token', token);

            fetchCurrentUser().then((res) => {
                setLoading(false);
                if (res.success) {
                    // Lần đầu đăng nhập Google/Facebook -> bắt đặt mật khẩu
                    navigate(res.user?.needs_password_setup ? '/set-password' : from, { replace: true });
                } else {
                    setOauthError('Lấy thông tin tài khoản thất bại sau khi đăng nhập');
                }
            });

            window.history.replaceState({}, document.title, location.pathname);
        }
    }, [location.search, location.pathname, from, navigate, fetchCurrentUser]);

    const validate = () => {
        const errors = {};
        if (!loginInput.trim()) {
            errors.login = 'Vui lòng nhập tên tài khoản hoặc email';
        }
        if (!password) {
            errors.password = 'Vui lòng nhập mật khẩu';
        }
        setFieldErrors(errors);
        return Object.keys(errors).length === 0;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!validate()) return;

        setLoading(true);
        setOauthError(null);
        if (error) clearError();

        const result = await login({
            login: loginInput.trim(),
            password,
            remember_me: rememberMe,
        });

        setLoading(false);

        // Tài khoản chưa xác nhận email -> sang bước nhập OTP
        if (result.needVerification) {
            const targetEmail = result.email || loginInput.trim();
            navigate(`/register?step=otp&email=${encodeURIComponent(targetEmail)}`, { replace: true });
            return;
        }

        if (result.success) {
            navigate(from, { replace: true });
        }
    };

    // Direct browser redirect for OAuth (ensures top-level session cookies for CSRF)
    const handleSocialLogin = (provider) => {
        const providerKey = provider.toLowerCase();
        window.location.href = `${API_BASE_URL}/auth/oauth/${providerKey}`;
    };

    const displayError = oauthError || error;

    return (
        <div className="auth-page-wrapper">
            <div className="auth-split-card">
                {/* Left Side: Beach Landscape Banner */}
                <div
                    className="auth-banner-side"
                    style={{ backgroundImage: `url(${beachBanner})` }}
                >
                    <div className="auth-banner-overlay-text">
                        <h2>Cùng Đi</h2>
                        <p>Lên kế hoạch du lịch thông minh & quản lý chi tiêu nhóm dễ dàng.</p>
                    </div>
                </div>

                {/* Right Side: Login Form (Figma Exact Layout) */}
                <div className="auth-form-side">
                    {/* Circle Logo C */}
                    <div className="auth-logo-badge">C</div>

                    {displayError && (
                        <div className="auth-alert-box auth-alert-error">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                            <span>{displayError}</span>
                        </div>
                    )}

                    <form onSubmit={handleSubmit} noValidate>
                        {/* Tên tài khoản */}
                        <div className="auth-field">
                            <label className="auth-label" htmlFor="loginInput">Tên tài khoản</label>
                            <input
                                type="text"
                                id="loginInput"
                                className={`auth-input ${fieldErrors.login ? 'auth-input-error' : ''}`}
                                placeholder="abc"
                                value={loginInput}
                                onChange={(e) => {
                                    setLoginInput(e.target.value);
                                    if (fieldErrors.login) setFieldErrors(prev => ({ ...prev, login: '' }));
                                    if (displayError) { setOauthError(null); clearError(); }
                                }}
                                disabled={loading}
                                autoFocus
                            />
                            {fieldErrors.login && (
                                <span className="auth-field-error-msg">{fieldErrors.login}</span>
                            )}
                        </div>

                        {/* Mật khẩu */}
                        <div className="auth-field">
                            <label className="auth-label" htmlFor="password">Mật khẩu</label>
                            <div className="auth-input-wrapper">
                                <input
                                    type={showPassword ? 'text' : 'password'}
                                    id="password"
                                    className={`auth-input ${fieldErrors.password ? 'auth-input-error' : ''}`}
                                    placeholder="••••••••••••••••"
                                    value={password}
                                    onChange={(e) => {
                                        setPassword(e.target.value);
                                        if (fieldErrors.password) setFieldErrors(prev => ({ ...prev, password: '' }));
                                        if (displayError) { setOauthError(null); clearError(); }
                                    }}
                                    disabled={loading}
                                />
                                <button
                                    type="button"
                                    className="auth-input-eye-btn"
                                    onClick={() => setShowPassword(!showPassword)}
                                    title={showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'}
                                    tabIndex={-1}
                                >
                                    {showPassword ? (
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                            <line x1="1" y1="1" x2="23" y2="23" />
                                        </svg>
                                    ) : (
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    )}
                                </button>
                            </div>
                            {fieldErrors.password && (
                                <span className="auth-field-error-msg">{fieldErrors.password}</span>
                            )}
                        </div>

                        {/* Ghi nhớ tôi & Quên mật khẩu */}
                        <div className="auth-meta-row">
                            <label className="auth-checkbox-label">
                                <input
                                    type="checkbox"
                                    checked={rememberMe}
                                    onChange={(e) => setRememberMe(e.target.checked)}
                                    disabled={loading}
                                />
                                <span>Ghi nhớ tôi</span>
                            </label>
                            <Link to="/forgot-password" className="auth-link-forgot">
                                Quên mật khẩu
                            </Link>
                        </div>

                        {/* Submit Button */}
                        <button
                            type="submit"
                            className="auth-btn-primary"
                            disabled={loading}
                        >
                            {loading ? (
                                <>
                                    <span className="auth-btn-spinner"></span>
                                    <span>Đang đăng nhập...</span>
                                </>
                            ) : (
                                'Đăng nhập'
                            )}
                        </button>

                        {/* Divider */}
                        <div className="auth-divider-box">
                            <span>hoặc</span>
                        </div>

                        {/* Social Buttons */}
                        <div className="auth-social-row">
                            <button
                                type="button"
                                className="auth-social-btn"
                                onClick={() => handleSocialLogin('Google')}
                                disabled={loading}
                            >
                                <svg viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                                </svg>
                                <span>Google</span>
                            </button>

                            <button
                                type="button"
                                className="auth-social-btn"
                                onClick={() => handleSocialLogin('Facebook')}
                                disabled={loading}
                            >
                                <svg viewBox="0 0 24 24" fill="#1877F2">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                </svg>
                                <span>Facebook</span>
                            </button>
                        </div>

                        {/* Footer Prompt */}
                        <p className="auth-footer-prompt">
                            Chưa có tài khoản? <Link to="/register">Đăng kí</Link>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    );
}
