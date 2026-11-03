import { useState } from 'react';
import { Navigate, useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import beachBanner from '../assets/beach-banner.jpg';
import './Auth.css';

/**
 * Hiện sau lần đầu đăng nhập bằng Google/Facebook để đặt mật khẩu.
 */
export default function SetPassword() {
    const navigate = useNavigate();
    const { user, loading: authLoading, needsPasswordSetup, setInitialPassword } = useAuth();

    const [password, setPassword] = useState('');
    const [confirm, setConfirm] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    if (authLoading) return null;
    if (!user) return <Navigate to="/login" replace />;
    if (!needsPasswordSetup) return <Navigate to="/" replace />;

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (password.length < 8) return setError('Mật khẩu phải có ít nhất 8 ký tự');
        if (password !== confirm) return setError('Mật khẩu nhập lại không khớp');

        setLoading(true);
        setError('');
        const res = await setInitialPassword(password);
        setLoading(false);

        if (res.success) navigate('/', { replace: true });
        else setError(res.error);
    };

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
                    <h1 className="auth-title-center">Tạo mật khẩu</h1>
                    <p className="auth-desc-center">
                        Bạn vừa đăng nhập lần đầu bằng Google/Facebook.<br />
                        Hãy đặt mật khẩu để có thể đăng nhập bằng email <strong>{user.email}</strong> sau này.
                    </p>

                    {error && <div className="auth-alert-box auth-alert-error"><span>{error}</span></div>}

                    <form onSubmit={handleSubmit} noValidate>
                        <div className="auth-field">
                            <label className="auth-label" htmlFor="spPass">Mật khẩu</label>
                            <input id="spPass" type="password" className="auth-input" value={password}
                                disabled={loading} autoFocus
                                onChange={(e) => { setPassword(e.target.value); setError(''); }} />
                        </div>
                        <div className="auth-field">
                            <label className="auth-label" htmlFor="spConfirm">Nhập lại mật khẩu</label>
                            <input id="spConfirm" type="password" className="auth-input" value={confirm}
                                disabled={loading}
                                onChange={(e) => { setConfirm(e.target.value); setError(''); }} />
                        </div>
                        <button type="submit" className="auth-btn-primary" disabled={loading}>
                            {loading ? 'Đang lưu...' : 'Lưu mật khẩu'}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    );
}
