import { useState } from 'react';
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

export default function ForgotPassword() {
    const { forgotPassword, resetPassword } = useAuth();

    const [step, setStep] = useState('email'); // email | reset | done
    const [email, setEmail] = useState('');
    const [otp, setOtp] = useState('');
    const [password, setPassword] = useState('');
    const [confirm, setConfirm] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [info, setInfo] = useState('');
    const [devOtp, setDevOtp] = useState(null);

    const sendCode = async (e) => {
        e?.preventDefault();
        if (!/\S+@\S+\.\S+/.test(email)) {
            setError('Email không hợp lệ');
            return;
        }
        setLoading(true);
        setError('');
        const res = await forgotPassword(email.trim());
        setLoading(false);
        if (!res.success) {
            setError(res.error);
            return;
        }
        setInfo(res.message);
        setDevOtp(res.otpPreview || null);
        setStep('reset');
    };

    const submitReset = async (e) => {
        e.preventDefault();
        if (!/^\d{6}$/.test(otp)) return setError('Mã xác nhận gồm 6 chữ số');
        if (password.length < 8) return setError('Mật khẩu phải có ít nhất 8 ký tự');
        if (password !== confirm) return setError('Mật khẩu nhập lại không khớp');

        setLoading(true);
        setError('');
        const res = await resetPassword({ email: email.trim(), otp, password });
        setLoading(false);
        if (res.success) setStep('done');
        else setError(res.error);
    };

    const errorBox = error && (
        <div className="auth-alert-box auth-alert-error"><span>{error}</span></div>
    );

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

    if (step === 'reset') {
        return (
            <Shell>
                <h1 className="auth-title-center">Đặt lại mật khẩu</h1>
                <p className="auth-desc-center">{info}</p>
                {devOtp && (
                    <div style={{ background: '#FEF3C7', color: '#92400E', padding: '6px 12px', borderRadius: 6, fontSize: 12, marginBottom: 14, textAlign: 'center' }}>
                        Mã OTP thử nghiệm: <strong>{devOtp}</strong>
                    </div>
                )}
                {errorBox}
                <form onSubmit={submitReset} noValidate>
                    <div className="auth-field">
                        <label className="auth-label" htmlFor="fpOtp">Mã xác nhận</label>
                        <input id="fpOtp" className="auth-input" inputMode="numeric" maxLength={6}
                            placeholder="6 chữ số" value={otp} disabled={loading}
                            onChange={(e) => { setOtp(e.target.value.replace(/\D/g, '')); setError(''); }} />
                    </div>
                    <div className="auth-field">
                        <label className="auth-label" htmlFor="fpPass">Mật khẩu mới</label>
                        <input id="fpPass" type="password" className="auth-input" value={password}
                            disabled={loading} onChange={(e) => { setPassword(e.target.value); setError(''); }} />
                    </div>
                    <div className="auth-field">
                        <label className="auth-label" htmlFor="fpConfirm">Nhập lại mật khẩu</label>
                        <input id="fpConfirm" type="password" className="auth-input" value={confirm}
                            disabled={loading} onChange={(e) => { setConfirm(e.target.value); setError(''); }} />
                    </div>
                    <button type="submit" className="auth-btn-primary" disabled={loading}>
                        {loading ? 'Đang xử lý...' : 'Đặt lại mật khẩu'}
                    </button>
                    <div style={{ textAlign: 'center', marginTop: 14 }}>
                        <button type="button" className="auth-btn-link" disabled={loading} onClick={sendCode}>
                            Gửi lại mã
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
