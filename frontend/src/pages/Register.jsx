import { useState, useEffect, useRef } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { api } from '../services/api';
import beachBanner from '../assets/beach-banner.jpg';
import './Auth.css';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8001/api';

export default function Register() {
    const navigate = useNavigate();
    const location = useLocation();
    const { fetchCurrentUser, verifyOtp } = useAuth();

    // Steps: 'form' | 'otp' | 'success'
    const [step, setStep] = useState('form');

    // Registration Form State
    const [email, setEmail] = useState('');
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [showPassword, setShowPassword] = useState(false);

    // OTP State (6 digits)
    const [otpValues, setOtpValues] = useState(['', '', '', '', '', '']);
    const [otpTimer, setOtpTimer] = useState(60);
    const [canResend, setCanResend] = useState(false);
    const [otpError, setOtpError] = useState('');
    const [isOtpExpired, setIsOtpExpired] = useState(false);
    const [devOtpPreview, setDevOtpPreview] = useState(null);

    // General UI State
    const [loading, setLoading] = useState(false);
    const [formError, setFormError] = useState('');
    const [fieldErrors, setFieldErrors] = useState({});

    // References for the 6 OTP input boxes
    const otpInputRefs = useRef([]);

    // Check if redirected with ?step=otp&email=...
    useEffect(() => {
        const params = new URLSearchParams(location.search);
        const stepParam = params.get('step');
        const emailParam = params.get('email');
        if (stepParam === 'otp' && emailParam) {
            setEmail(emailParam);
            setStep('otp');
            setOtpTimer(60);
            setCanResend(false);
        }
    }, [location.search]);

    // Countdown Timer for OTP
    useEffect(() => {
        let interval = null;
        if (step === 'otp' && otpTimer > 0) {
            interval = setInterval(() => {
                setOtpTimer((prev) => {
                    if (prev <= 1) {
                        setCanResend(true);
                        return 0;
                    }
                    return prev - 1;
                });
            }, 1000);
        }
        return () => {
            if (interval) clearInterval(interval);
        };
    }, [step, otpTimer]);

    // Format seconds to mm:ss
    const formatTime = (seconds) => {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return `${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
    };

    // Form Validation
    const validateForm = () => {
        const errors = {};
        if (!email.trim()) {
            errors.email = 'Vui lòng nhập email';
        } else if (!/\S+@\S+\.\S+/.test(email)) {
            errors.email = 'Email không hợp lệ';
        }

        if (!username.trim()) {
            errors.username = 'Vui lòng nhập tên tài khoản';
        } else if (username.length < 3) {
            errors.username = 'Tên tài khoản phải có ít nhất 3 ký tự';
        } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            errors.username = 'Tên tài khoản chỉ được chứa chữ, số và dấu gạch dưới';
        }

        if (!password) {
            errors.password = 'Vui lòng nhập mật khẩu';
        } else if (password.length < 8) {
            errors.password = 'Mật khẩu phải có ít nhất 8 ký tự';
        }

        setFieldErrors(errors);
        return Object.keys(errors).length === 0;
    };

    // Step 1: Submit Register Form
    const handleRegisterSubmit = async (e) => {
        e.preventDefault();
        if (!validateForm()) return;

        setLoading(true);
        setFormError('');

        try {
            const resp = await api.post('/auth/register', {
                email: email.trim(),
                username: username.trim(),
                password,
            });

            setLoading(false);

            if (resp.status === 'success' || resp.data?.status === 'need_verification') {
                if (resp.data?.otp_preview) {
                    setDevOtpPreview(resp.data.otp_preview);
                }
                setStep('otp');
                setOtpTimer(60);
                setCanResend(false);
                setOtpValues(['', '', '', '', '', '']);
                setOtpError('');
                setIsOtpExpired(false);
                setTimeout(() => otpInputRefs.current[0]?.focus(), 100);
            } else {
                throw new Error(resp.message || 'Đăng ký thất bại');
            }
        } catch (err) {
            setLoading(false);
            setFormError(err.message || 'Đăng ký thất bại. Vui lòng thử lại.');
        }
    };

    // Step 2: Handle OTP Input Changes
    const handleOtpChange = (index, value) => {
        // Only accept numbers
        const cleaned = value.replace(/[^0-9]/g, '');
        if (!cleaned && value !== '') return;

        const newOtp = [...otpValues];

        // Handle Paste (e.g. 6 digits pasted into one input)
        if (cleaned.length > 1) {
            const pastedDigits = cleaned.slice(0, 6).split('');
            for (let i = 0; i < 6; i++) {
                newOtp[i] = pastedDigits[i] || '';
            }
            setOtpValues(newOtp);
            setOtpError('');
            const nextFocusIndex = Math.min(pastedDigits.length, 5);
            otpInputRefs.current[nextFocusIndex]?.focus();
            return;
        }

        newOtp[index] = cleaned;
        setOtpValues(newOtp);
        setOtpError('');

        // Auto advance to next box
        if (cleaned && index < 5) {
            otpInputRefs.current[index + 1]?.focus();
        }
    };

    const handleOtpKeyDown = (index, e) => {
        // Backspace handling: move focus back if current is empty
        if (e.key === 'Backspace' && !otpValues[index] && index > 0) {
            otpInputRefs.current[index - 1]?.focus();
        }
    };

    // Step 2: Submit OTP Verification
    const handleOtpSubmit = async (e) => {
        e.preventDefault();
        const fullOtp = otpValues.join('');
        if (fullOtp.length !== 6) return;

        setLoading(true);
        setOtpError('');
        setIsOtpExpired(false);

        try {
            const result = await verifyOtp({
                email,
                otp,
            });

            if (result?.success) {
                setStep("success");
            } else {
                setError(result?.message || "Xác minh OTP thất bại");
            }
        } catch (err) {
            setLoading(false);
            const msg = err.message || 'Mã xác nhận không đúng. Vui lòng kiểm tra lại.';
            setOtpError(msg);
            if (msg.includes('hết hạn')) {
                setIsOtpExpired(true);
            }
        }
    };

    // Step 2: Resend OTP
    const handleResendOtp = async () => {
        if (!canResend) return;

        setLoading(true);
        setOtpError('');
        setIsOtpExpired(false);

        try {
            const resp = await api.post('/auth/resend-otp', { email: email.trim() });
            setLoading(false);
            if (resp.data?.otp_preview) {
                setDevOtpPreview(resp.data.otp_preview);
            }
            setOtpTimer(60);
            setCanResend(false);
            setOtpValues(['', '', '', '', '', '']);
            otpInputRefs.current[0]?.focus();
        } catch (err) {
            setLoading(false);
            setOtpError(err.message || 'Không thể gửi lại mã xác nhận.');
        }
    };

    // Step 3: Complete Success & Enter App
    const handleContinueSuccess = async () => {
        await fetchCurrentUser();
        navigate('/', { replace: true });
    };

    // Direct Browser OAuth Redirect
    const handleSocialLogin = (provider) => {
        const providerKey = provider.toLowerCase();
        window.location.href = `${API_BASE_URL}/auth/oauth/${providerKey}`;
    };

    const isOtpComplete = otpValues.join('').length === 6;

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

                {/* Right Side: Form or Verification Step */}
                <div className="auth-form-side">
                    {/* ================= STEP 1: REGISTER FORM ================= */}
                    {step === 'form' && (
                        <>
                            {/* Circle Logo C */}
                            <div className="auth-logo-badge">C</div>

                            {formError && (
                                <div className="auth-alert-box auth-alert-error">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <line x1="12" y1="8" x2="12" y2="12" />
                                        <line x1="12" y1="16" x2="12.01" y2="16" />
                                    </svg>
                                    <span>{formError}</span>
                                </div>
                            )}

                            <form onSubmit={handleRegisterSubmit} noValidate>
                                {/* Email */}
                                <div className="auth-field">
                                    <label className="auth-label" htmlFor="regEmail">Email</label>
                                    <input
                                        type="email"
                                        id="regEmail"
                                        className={`auth-input ${fieldErrors.email ? 'auth-input-error' : ''}`}
                                        placeholder="abc@gmail.com"
                                        value={email}
                                        onChange={(e) => {
                                            setEmail(e.target.value);
                                            if (fieldErrors.email) setFieldErrors(prev => ({ ...prev, email: '' }));
                                            if (formError) setFormError('');
                                        }}
                                        disabled={loading}
                                    />
                                    {fieldErrors.email && (
                                        <span className="auth-field-error-msg">{fieldErrors.email}</span>
                                    )}
                                </div>

                                {/* Tên tài khoản */}
                                <div className="auth-field">
                                    <label className="auth-label" htmlFor="regUsername">Tên tài khoản</label>
                                    <input
                                        type="text"
                                        id="regUsername"
                                        className={`auth-input ${fieldErrors.username ? 'auth-input-error' : ''}`}
                                        placeholder="abc"
                                        value={username}
                                        onChange={(e) => {
                                            setUsername(e.target.value);
                                            if (fieldErrors.username) setFieldErrors(prev => ({ ...prev, username: '' }));
                                            if (formError) setFormError('');
                                        }}
                                        disabled={loading}
                                    />
                                    {fieldErrors.username && (
                                        <span className="auth-field-error-msg">{fieldErrors.username}</span>
                                    )}
                                </div>

                                {/* Mật khẩu */}
                                <div className="auth-field">
                                    <label className="auth-label" htmlFor="regPassword">Mật khẩu</label>
                                    <div className="auth-input-wrapper">
                                        <input
                                            type={showPassword ? 'text' : 'password'}
                                            id="regPassword"
                                            className={`auth-input ${fieldErrors.password ? 'auth-input-error' : ''}`}
                                            placeholder="••••••••••••••••"
                                            value={password}
                                            onChange={(e) => {
                                                setPassword(e.target.value);
                                                if (fieldErrors.password) setFieldErrors(prev => ({ ...prev, password: '' }));
                                                if (formError) setFormError('');
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

                                {/* Submit Button */}
                                <button
                                    type="submit"
                                    className="auth-btn-primary"
                                    style={{ marginTop: '12px' }}
                                    disabled={loading}
                                >
                                    {loading ? (
                                        <>
                                            <span className="auth-btn-spinner"></span>
                                            <span>Đang tạo tài khoản...</span>
                                        </>
                                    ) : (
                                        'Đăng kí'
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
                                    Đã có tài khoản? <Link to="/login">Đăng nhập</Link>
                                </p>
                            </form>
                        </>
                    )}

                    {/* ================= STEP 2: OTP VERIFICATION SCREEN ================= */}
                    {step === 'otp' && (
                        <>
                            {/* Circle Logo C */}
                            <div className="auth-logo-badge">C</div>

                            <h1 className="auth-title-center">Xác nhận email</h1>
                            <p className="auth-desc-center">
                                Chúng tôi đã gửi mã xác nhận gồm 6 chữ số tới<br />
                                <span className="auth-email-highlight">{email}</span>
                            </p>

                            {/* Dev Testing OTP Hint */}
                            {devOtpPreview && (
                                <div style={{ background: '#FEF3C7', border: '1px solid #F59E0B', color: '#92400E', padding: '6px 12px', borderRadius: '6px', fontSize: '12px', marginBottom: '14px', textAlign: 'center' }}>
                                    Mã OTP thử nghiệm: <strong>{devOtpPreview}</strong>
                                </div>
                            )}

                            <form onSubmit={handleOtpSubmit}>
                                {/* 6 OTP Input Boxes */}
                                <div className="auth-otp-grid">
                                    {otpValues.map((digit, idx) => (
                                        <input
                                            key={idx}
                                            ref={(el) => (otpInputRefs.current[idx] = el)}
                                            type="text"
                                            inputMode="numeric"
                                            maxLength={idx === 0 ? 6 : 1}
                                            className={`auth-otp-input ${otpError ? 'auth-otp-input-error' : ''}`}
                                            value={digit}
                                            onChange={(e) => handleOtpChange(idx, e.target.value)}
                                            onKeyDown={(e) => handleOtpKeyDown(idx, e)}
                                            disabled={loading}
                                            autoFocus={idx === 0}
                                        />
                                    ))}
                                </div>

                                {/* Error message if invalid or expired */}
                                {otpError && (
                                    <div className="auth-otp-error-msg">
                                        {otpError}
                                    </div>
                                )}

                                {/* Timer Countdown */}
                                <div className="auth-otp-timer">
                                    {otpTimer > 0 ? (
                                        <span>Gửi lại mã sau {formatTime(otpTimer)}</span>
                                    ) : (
                                        <span>Bạn có thể gửi lại mã</span>
                                    )}
                                </div>

                                {/* Xác nhận button */}
                                <button
                                    type="submit"
                                    className="auth-btn-primary"
                                    disabled={!isOtpComplete || loading}
                                >
                                    {loading ? (
                                        <>
                                            <span className="auth-btn-spinner"></span>
                                            <span>Đang xác nhận...</span>
                                        </>
                                    ) : (
                                        'Xác nhận'
                                    )}
                                </button>

                                {/* Resend row */}
                                <div className="auth-otp-resend-row">
                                    <span>Không nhận được mã? </span>
                                    <button
                                        type="button"
                                        className="auth-btn-link"
                                        onClick={handleResendOtp}
                                        disabled={!canResend || loading}
                                    >
                                        Gửi lại mã
                                    </button>
                                </div>

                                {/* Change email link */}
                                <div style={{ textAlign: 'center', marginTop: '14px' }}>
                                    <button
                                        type="button"
                                        className="auth-btn-link"
                                        style={{ fontSize: '13.5px' }}
                                        onClick={() => {
                                            setStep('form');
                                            setFormError('');
                                        }}
                                    >
                                        Đổi email
                                    </button>
                                </div>
                            </form>
                        </>
                    )}

                    {/* ================= STEP 3: SUCCESS SCREEN ================= */}
                    {step === 'success' && (
                        <div style={{ textAlign: 'center' }}>
                            {/* Green Circle Checkmark Icon */}
                            <div className="auth-success-icon-box">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </div>

                            <h1 className="auth-title-center" style={{ marginBottom: '12px' }}>
                                Xác minh email thành công
                            </h1>
                            <p className="auth-desc-center" style={{ maxWidth: '340px', margin: '0 auto 28px auto' }}>
                                Tài khoản của bạn đã được tạo.<br />
                                Hãy bắt đầu lên kế hoạch cho chuyến đi đầu tiên!
                            </p>

                            <button
                                type="button"
                                className="auth-btn-primary"
                                onClick={handleContinueSuccess}
                            >
                                Tiếp tục
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
