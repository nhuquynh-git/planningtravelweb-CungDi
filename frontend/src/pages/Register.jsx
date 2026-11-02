import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import './Auth.css';

/**
 * Register Page - Figma Design
 */
export default function Register() {
    const navigate = useNavigate();
    const { register, error, clearError } = useAuth();
    
    const [formData, setFormData] = useState({
        email: '',
        password: '',
        confirmPassword: '',
    });
    const [loading, setLoading] = useState(false);
    const [validationErrors, setValidationErrors] = useState({});
    const [agreeTerms, setAgreeTerms] = useState(false);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
        
        if (validationErrors[name]) {
            setValidationErrors(prev => ({ ...prev, [name]: '' }));
        }
        
        if (error) clearError();
    };

    const validate = () => {
        const errors = {};
        
        if (!formData.email) {
            errors.email = 'Vui lòng nhập email';
        } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
            errors.email = 'Email không hợp lệ';
        } else if (formData.email.length > 254) {
            errors.email = 'Email quá dài';
        }
        
        if (!formData.password) {
            errors.password = 'Vui lòng nhập mật khẩu';
        } else if (formData.password.length < 8) {
            errors.password = 'Mật khẩu phải có ít nhất 8 ký tự';
        } else if (formData.password.length > 72) {
            errors.password = 'Mật khẩu quá dài';
        }
        
        if (!formData.confirmPassword) {
            errors.confirmPassword = 'Vui lòng xác nhận mật khẩu';
        } else if (formData.password !== formData.confirmPassword) {
            errors.confirmPassword = 'Mật khẩu không khớp';
        }

        if (!agreeTerms) {
            errors.terms = 'Bạn cần đồng ý với điều khoản';
        }
        
        setValidationErrors(errors);
        return Object.keys(errors).length === 0;
    };

    const getPasswordStrength = () => {
        if (!formData.password) return 0;
        let strength = 0;
        if (formData.password.length >= 8) strength++;
        if (formData.password.length >= 12) strength++;
        if (/[A-Z]/.test(formData.password)) strength++;
        if (/[0-9]/.test(formData.password)) strength++;
        if (/[^A-Za-z0-9]/.test(formData.password)) strength++;
        return Math.min(strength, 3);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (!validate()) return;
        
        setLoading(true);
        
        const result = await register({
            email: formData.email,
            password: formData.password,
        });
        
        setLoading(false);
        
        if (result.success) {
            navigate('/', { replace: true });
        }
    };

    const handleSocialLogin = (provider) => {
        // TODO: Implement Google/Facebook OAuth
        console.log(`Register with ${provider}`);
        alert(`Tính năng đăng ký bằng ${provider} đang được phát triển!`);
    };

    const passwordStrength = getPasswordStrength();

    return (
        <div className="auth-page">
            <div className="auth-container">
                {/* Logo Header */}
                <div className="auth-logo-header">
                    <div className="auth-logo">
                        <div className="auth-logo-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                        </div>
                        <span className="auth-logo-text">Cùng Đi</span>
                    </div>
                </div>

                <div className="auth-card">
                    <div className="auth-header">
                        <h1 className="auth-title">Tạo tài khoản</h1>
                        <p className="auth-subtitle">Đăng ký để bắt đầu lập kế hoạch du lịch</p>
                    </div>

                    <form className="auth-form" onSubmit={handleSubmit}>
                        {error && (
                            <div className="auth-message auth-message-error">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                                <span>{error}</span>
                            </div>
                        )}

                        <div className="form-group">
                            <label htmlFor="email" className="form-label">Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                className={`form-input ${validationErrors.email ? 'form-input-error' : ''}`}
                                placeholder="email@example.com"
                                value={formData.email}
                                onChange={handleChange}
                                autoComplete="email"
                                disabled={loading}
                            />
                            {validationErrors.email && (
                                <span className="form-error">{validationErrors.email}</span>
                            )}
                        </div>

                        <div className="form-group">
                            <label htmlFor="password" className="form-label">Mật khẩu</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                className={`form-input ${validationErrors.password ? 'form-input-error' : ''}`}
                                placeholder="Tạo mật khẩu (ít nhất 8 ký tự)"
                                value={formData.password}
                                onChange={handleChange}
                                autoComplete="new-password"
                                disabled={loading}
                            />
                            {formData.password && (
                                <div className="password-strength">
                                    <div className={`password-strength-bar ${passwordStrength >= 1 ? (passwordStrength === 1 ? 'weak' : passwordStrength === 2 ? 'medium' : 'strong') : ''}`}></div>
                                    <div className={`password-strength-bar ${passwordStrength >= 2 ? (passwordStrength === 2 ? 'medium' : 'strong') : ''}`}></div>
                                    <div className={`password-strength-bar ${passwordStrength >= 3 ? 'strong' : ''}`}></div>
                                </div>
                            )}
                            {validationErrors.password && (
                                <span className="form-error">{validationErrors.password}</span>
                            )}
                        </div>

                        <div className="form-group">
                            <label htmlFor="confirmPassword" className="form-label">Xác nhận mật khẩu</label>
                            <input
                                type="password"
                                id="confirmPassword"
                                name="confirmPassword"
                                className={`form-input ${validationErrors.confirmPassword ? 'form-input-error' : ''}`}
                                placeholder="Nhập lại mật khẩu"
                                value={formData.confirmPassword}
                                onChange={handleChange}
                                autoComplete="new-password"
                                disabled={loading}
                            />
                            {validationErrors.confirmPassword && (
                                <span className="form-error">{validationErrors.confirmPassword}</span>
                            )}
                        </div>

                        <label className="form-checkbox">
                            <input
                                type="checkbox"
                                checked={agreeTerms}
                                onChange={(e) => setAgreeTerms(e.target.checked)}
                                disabled={loading}
                            />
                            <span>Tôi đồng ý với <a href="#" className="auth-footer-link">Điều khoản</a> và <a href="#" className="auth-footer-link">Chính sách bảo mật</a></span>
                        </label>
                        {validationErrors.terms && (
                            <span className="form-error">{validationErrors.terms}</span>
                        )}

                        <button 
                            type="submit" 
                            className="auth-button auth-button-primary"
                            disabled={loading}
                        >
                            {loading ? (
                                <>
                                    <span className="auth-spinner"></span>
                                    <span>Đang đăng ký...</span>
                                </>
                            ) : (
                                'Tạo tài khoản'
                            )}
                        </button>

                        <div className="auth-divider">
                            <div className="auth-divider-line"></div>
                            <span className="auth-divider-text">hoặc</span>
                            <div className="auth-divider-line"></div>
                        </div>

                        <div className="auth-social">
                            <button 
                                type="button"
                                className="auth-social-button"
                                onClick={() => handleSocialLogin('Google')}
                            >
                                <svg viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                                Google
                            </button>
                            <button 
                                type="button"
                                className="auth-social-button"
                                onClick={() => handleSocialLogin('Facebook')}
                            >
                                <svg viewBox="0 0 24 24" fill="#1877F2">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                                Facebook
                            </button>
                        </div>
                    </form>

                    <div className="auth-footer">
                        <p>Đã có tài khoản? <Link to="/login" className="auth-footer-link">Đăng nhập ngay</Link></p>
                    </div>
                </div>

                <div className="auth-back">
                    <Link to="/" className="auth-back-link">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        <span>Quay về trang chủ</span>
                    </Link>
                </div>
            </div>
        </div>
    );
}