import { useEffect, useState } from "react";
import { getHealth } from "../services/healthService";
import { useAuth } from "../contexts/AuthContext";
import { LoadingStatus } from "../components";
import "./Home.css";

/**
 * Home Page - Authenticated User Dashboard
 * Displays user info and system status
 */
export default function Home() {
    const { user, logout } = useAuth();
    const [health, setHealth] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");

    useEffect(() => {
        const checkBackend = async () => {
            try {
                const result = await getHealth();

                if (result.status !== "success") {
                    throw new Error(result.message || "API không hoạt động");
                }

                setHealth(result.data);
            } catch (err) {
                setError(err.message || "Không thể kết nối Backend");
            } finally {
                setLoading(false);
            }
        };

        checkBackend();
    }, []);

    const handleLogout = async () => {
        await logout();
    };

    return (
        <div className="home-page">
            <header className="home-header">
                <div className="home-logo">
                    <h1>Cùng Đi</h1>
                    <span className="home-tagline">Travel Planner</span>
                </div>
                <div className="home-user">
                    <span className="user-email">{user?.email}</span>
                    <button className="logout-button" onClick={handleLogout}>
                        Đăng xuất
                    </button>
                </div>
            </header>

            <main className="home-content">
                <div className="welcome-card">
                    <h2>Chào mừng, {user?.username || user?.email?.split('@')[0]}!</h2>
                    <p>Bạn đã đăng nhập thành công vào hệ thống Cùng Đi Travel Planner.</p>
                    
                    <div className="user-info">
                        <div className="info-row">
                            <span className="info-label">Email:</span>
                            <span className="info-value">{user?.email}</span>
                        </div>
                        <div className="info-row">
                            <span className="info-label">Vai trò:</span>
                            <span className="info-value">{user?.groups?.join(', ') || 'user'}</span>
                        </div>
                        {user?.profile?.display_name && (
                            <div className="info-row">
                                <span className="info-label">Tên hiển thị:</span>
                                <span className="info-value">{user.profile.display_name}</span>
                            </div>
                        )}
                    </div>
                </div>

                <div className="system-status">
                    <h3>Trạng thái hệ thống</h3>
                    
                    {loading && <LoadingStatus label="Đang kiểm tra kết nối..." />}

                    {error && (
                        <div className="status-error">
                            <p>Backend: Không kết nối được</p>
                            <p>{error}</p>
                        </div>
                    )}

                    {!loading && !error && health && (
                        <div className="status-grid">
                            <div className="status-item">
                                <span className="status-label">Backend</span>
                                <span className={`status-value ${health.status === "success" ? "status-ok" : "status-error"}`}>
                                    {health.status === "success" ? "Hoạt động" : "Không hoạt động"}
                                </span>
                            </div>
                            <div className="status-item">
                                <span className="status-label">MySQL</span>
                                <span className={`status-value ${health.database === "connected" ? "status-ok" : "status-error"}`}>
                                    {health.database === "connected" ? "Đã kết nối" : "Chưa kết nối"}
                                </span>
                            </div>
                        </div>
                    )}
                </div>

                <div className="quick-actions">
                    <h3>Chức năng</h3>
                    <div className="action-grid">
                        <div className="action-card">
                            <div className="action-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                </svg>
                            </div>
                            <h4>Kế hoạch</h4>
                            <p>Quản lý kế hoạch du lịch của bạn</p>
                        </div>
                        <div className="action-card">
                            <div className="action-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                </svg>
                            </div>
                            <h4>Chi phí</h4>
                            <p>Theo dõi và chia sẻ chi phí</p>
                        </div>
                        <div className="action-card">
                            <div className="action-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            </div>
                            <h4>Minigame</h4>
                            <p>Khám phá trò chơi cho chuyến đi</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    );
}