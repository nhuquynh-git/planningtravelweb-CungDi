import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import { AuthProvider, useAuth } from "./contexts/AuthContext";
import Login from "./pages/Login";
import Register from "./pages/Register";
import ForgotPassword from "./pages/ForgotPassword";
import SetPassword from "./pages/SetPassword";
import Home from "./pages/Home";
import "./App.css";

function LoadingScreen() {
    return (
        <div className="app">
            <div className="loading-container">
                <div className="loading-spinner"></div>
                <p>Đang tải...</p>
            </div>
        </div>
    );
}

/**
 * Cần đăng nhập. Tài khoản Google/Facebook chưa đặt mật khẩu
 * sẽ bị chuyển sang /set-password.
 */
function ProtectedRoute({ children }) {
    const { isAuthenticated, loading, needsPasswordSetup } = useAuth();

    if (loading) return <LoadingScreen />;
    if (!isAuthenticated) return <Navigate to="/login" replace />;
    if (needsPasswordSetup) return <Navigate to="/set-password" replace />;

    return children;
}

/**
 * Trang công khai (login, quên mật khẩu). Đã đăng nhập thì chuyển đi.
 */
function PublicRoute({ children }) {
    const { isAuthenticated, loading, needsPasswordSetup } = useAuth();

    if (loading) return <LoadingScreen />;
    if (isAuthenticated) {
        return <Navigate to={needsPasswordSetup ? "/set-password" : "/"} replace />;
    }

    return children;
}

function AppRoutes() {
    return (
        <Routes>
            <Route path="/login" element={<PublicRoute><Login /></PublicRoute>} />

            {/*
              /register KHÔNG bọc PublicRoute: sau khi nhập đúng OTP, phiên đăng nhập
              được lưu ngay, nếu bọc thì sẽ bị đá về "/" trước khi thấy màn hình
              "Xác minh email thành công".
            */}
            <Route path="/register" element={<Register />} />

            <Route path="/forgot-password" element={<PublicRoute><ForgotPassword /></PublicRoute>} />

            {/* Tự kiểm tra đăng nhập / cờ needsPasswordSetup bên trong */}
            <Route path="/set-password" element={<SetPassword />} />

            <Route path="/" element={<ProtectedRoute><Home /></ProtectedRoute>} />

            <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
    );
}

function App() {
    return (
        <BrowserRouter>
            <AuthProvider>
                <AppRoutes />
            </AuthProvider>
        </BrowserRouter>
    );
}

export default App;
