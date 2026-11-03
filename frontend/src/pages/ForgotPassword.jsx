import { useState, useEffect, useRef } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import beachBanner from '../assets/beach-banner.jpg';
import './Auth.css';

function Shell({ children }) {
    return (
        <div className="auth-page-wrapper">
            <div className="auth-split-card">
                <div className="auth-banner-side" style={{ backgroundImage: `url(${beachBanner})` }}>
                    <div className="auth-banner-overlay-text">
                        <h2>Cùng Đi</h2>
                        <p>Lên kế hoạch du lịch thông minh & quản lý chi tiêu nhóm dễ dàng.</p>
                    </div>
                </div>
                <div className="auth-form-side">
                    <div className="auth-logo-badge">C</div>
                    {children}
                </div>
            </div>
        </div>
    );
}

const EMPTY_OTP = ['', '', '', '', '', ''];

/**
 * Quên mật khẩu, 4 bước:
 *  email -> otp (chỉ nhập mã) -> password (mật khẩu mới + xác nhận) -> done
 */
export default function ForgotPassword() {
    const { forgotPassword, verifyResetOtp, resetPassword } = useAuth();

    const [step, setStep] = useState('email');
    const [email, setEmail] = useState('');
    const [otpValues, setOtpValues] = useState(EMPTY_OTP);
    const [verifiedOtp, setVerifiedOtp] = useState('');
    const [password, setPassword] = useState('');
    const [confirm, setConfirm] = useState('');

    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [devOtp, setDevOtp] = useState(null);
    const [timer, setTimer] = useState(60);

    const otpRefs = useRef([]);

    // Đếm ngược cho "Gửi lại mã"
    useEffect(() => {
        if (step !== 'otp' || timer <= 0) return undefined;
        const id = setTimeout(() => setTimer((t) => t - 1), 1000);
        return () => clearTimeout(id);
    }, [step, timer]);

    const fmt = (s) => `0${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`;

    // ---- Bước 1: gửi mã ----
    const sendCode = async (e) => {
        e?.preventDefault();
        if (!/\S+@\S+\.\S+/.test(email)) return setError('Email không hợp lệ');

        setLoading(true);
        setError('');
        const res = await forgotPassword(email.trim());
        setLoading(false);

        if (!res.success) return setError(res.error);

        setDevOtp(res.otpPreview || null);
        setOtpValues(EMPTY_OTP);
        setTimer(60);
        setStep('otp');
        setTimeout(() => otpRefs.current[0]?.focus(), 100);
    };

    // ---- Bước 2: nhập mã ----
    const handleOtpChange = (index, value) => {
        const cleaned = value.replace(/[^0-9]/g, '');
        if (!cleaned && value !== '') return;
        const next = [...otpValues];

        if (cleaned.length > 1) {
            const digits = cleaned.slice(0, 6).split('');
            for (let i = 0; i < 6; i++) next[i] = digits[i] || '';
            setOtpValues(next);
            setError('');
            otpRefs.current[Math.min(digits.length, 5)]?.focus();
            return;
        }

        next[index] = cleaned;
        setOtpValues(next);
        setError('');
        if (cleaned && index < 5) otpRefs.current[index + 1]?.focus();
    };

    const handleOtpKeyDown = (index, e) => {
        if (e.key === 'Backspace' && !otpValues[index] && index > 0) {
            otpRefs.current[index - 1]?.focus();
        }
    };

    const submitOtp = async (e) => {
        e.preventDefault();
        const code = otpValues.join('');
        if (code.length !== 6) return;

        setLoading(true);
        setError('');
        const res = await verifyResetOtp({ email: email.trim(), otp: code });
        setLoading(false);

        if (!res.success) return setError(res.error);

        setVerifiedOtp(code);
        setStep('password');
    };

    // ---- Bước 3: mật khẩu mới ----
    const submitPassword = async (e) => {
        e.preventDefault();
        if (password.length < 8) return setError('Mật khẩu phải có ít nhất 8 ký tự');
        if (password !== confirm) return setError('Mật khẩu nhập lại không khớp');

        setLoading(true);
        setError('');
        const res = await resetPassword({ email: email.trim(), otp: verifiedOtp, password });
        setLoading(false);

        if (res.success) {
            setStep('done');
        } else if (/mã xác nhận|hết hạn|quá nhiều/i.test(res.error || '')) {
            // Mã không còn hiệu lực -> quay lại bước nhập mã
            setError(res.error);
            setOtpValues(EMPTY_OTP);
            setStep('otp');
        } else {
            setError(res.error);
        }
    };

    const errorBox = error && (
        <div className="auth-alert-box auth-alert-error"><span>{error}</span></div>
    );

    // ---------- Render ----------
    if (step === 'done') {
        return (
            <Shell>
                <div style={{ textAlign: 'center' }}>
                    <h1 className="auth-title-center">Đặt lại mật khẩu thành công</h1>
                    <p className="auth-desc-center">Bạn có thể đăng nhập bằng mật khẩu mới.</p>
                    <Link to="/login" className="auth-btn-primary" style={{ display: 'block', textAlign: 'center' }}>
                        Đăng nhập
                    </Link>
                </div>
            </Shell>
        );
    }

    if (step === 'password') {
        return (
            <Shell>
                <h1 className="auth-title-center">Tạo mật khẩu mới</h1>
                <p className="auth-desc-center">Mã xác nhận hợp lệ. Hãy nhập mật khẩu mới cho tài khoản.</p>
                {errorBox}
                <form onSubmit={submitPassword} noValidate>
                    <div className="auth-field">
                        <label className="auth-label" htmlFor="fpPass">Mật khẩu mới</label>
                        <input id="fpPass" type="password" className="auth-input" value={password}
                            disabled={loading} autoFocus
                            onChange={(e) => { setPassword(e.target.value); setError(''); }} />
                    </div>
                    <div className="auth-field">
                        <label className="auth-label" htmlFor="fpConfirm">Nhập lại mật khẩu</label>
                        <input id="fpConfirm" type="password" className="auth-input" value={confirm}
                            disabled={loading}
                            onChange={(e) => { setConfirm(e.target.value); setError(''); }} />
                    </div>
                    <button type="submit" className="auth-btn-primary" disabled={loading}>
                        {loading ? 'Đang xử lý...' : 'Đặt lại mật khẩu'}
                    </button>
                </form>
            </Shell>
        );
    }

    if (step === 'otp') {
        const complete = otpValues.join('').length === 6;
        return (
            <Shell>
                <h1 className="auth-title-center">Nhập mã xác nhận</h1>
                <p className="auth-desc-center">
                    Nếu email tồn tại trong hệ thống, mã 6 chữ số đã được gửi tới<br />
                    <span className="auth-email-highlight">{email}</span>
                </p>
                {devOtp && (
                    <div style={{ background: '#FEF3C7', color: '#92400E', padding: '6px 12px', borderRadius: 6, fontSize: 12, marginBottom: 14, textAlign: 'center' }}>
                        Mã OTP thử nghiệm: <strong>{devOtp}</strong>
                    </div>
                )}
                <form onSubmit={submitOtp}>
                    <div className="auth-otp-grid">
                        {otpValues.map((digit, idx) => (
                            <input
                                key={idx}
                                ref={(el) => (otpRefs.current[idx] = el)}
                                type="text"
                                inputMode="numeric"
                                maxLength={idx === 0 ? 6 : 1}
                                className={`auth-otp-input ${error ? 'auth-otp-input-error' : ''}`}
                                value={digit}
                                onChange={(e) => handleOtpChange(idx, e.target.value)}
                                onKeyDown={(e) => handleOtpKeyDown(idx, e)}
                                disabled={loading}
                                autoFocus={idx === 0}
                            />
                        ))}
                    </div>
                    {error && <div className="auth-otp-error-msg">{error}</div>}

                    <div className="auth-otp-timer">
                        {timer > 0 ? <span>Gửi lại mã sau {fmt(timer)}</span> : <span>Bạn có thể gửi lại mã</span>}
                    </div>

                    <button type="submit" className="auth-btn-primary" disabled={!complete || loading}>
                        {loading ? 'Đang xác nhận...' : 'Xác nhận'}
                    </button>

                    <div className="auth-otp-resend-row">
                        <span>Không nhận được mã? </span>
                        <button type="button" className="auth-btn-link" disabled={timer > 0 || loading} onClick={sendCode}>
                            Gửi lại mã
                        </button>
                    </div>
                    <div style={{ textAlign: 'center', marginTop: 14 }}>
                        <button type="button" className="auth-btn-link" style={{ fontSize: '13.5px' }}
                            onClick={() => { setStep('email'); setError(''); }}>
                            Đổi email
                        </button>
                    </div>
                </form>
            </Shell>
        );
    }

    return (
        <Shell>
            <h1 className="auth-title-center">Quên mật khẩu</h1>
            <p className="auth-desc-center">Nhập email đã đăng ký, chúng tôi sẽ gửi mã xác nhận 6 số.</p>
            {errorBox}
            <form onSubmit={sendCode} noValidate>
                <div className="auth-field">
                    <label className="auth-label" htmlFor="fpEmail">Email</label>
                    <input id="fpEmail" type="email" className="auth-input" placeholder="abc@gmail.com"
                        value={email} disabled={loading} autoFocus
                        onChange={(e) => { setEmail(e.target.value); setError(''); }} />
                </div>
                <button type="submit" className="auth-btn-primary" disabled={loading}>
                    {loading ? 'Đang gửi...' : 'Gửi mã xác nhận'}
                </button>
                <p className="auth-footer-prompt"><Link to="/login">Quay lại đăng nhập</Link></p>
            </form>
        </Shell>
    );
}
