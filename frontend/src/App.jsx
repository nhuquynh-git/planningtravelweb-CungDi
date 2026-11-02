
import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import { AuthProvider, useAuth } from "./contexts/AuthContext";
import Login from "./pages/Login";
import Register from "./pages/Register";
import Home from "./pages/Home";
import "./App.css";

/**
 * Protected Route Component
 * Redirects to login if user is not authenticated
 */
function ProtectedRoute({ children }) {
    const { isAuthenticated, loading } = useAuth();

    if (loading) {
        return (
            <div className="app">
                <div className="loading-container">
                    <div className="loading-spinner"></div>
                    <p>Đang tải...</p>
                </div>
            </div>
        );
    }

    if (!isAuthenticated) {
        return <Navigate to="/login" replace />;
    }

    return children;
}

/**
 * Public Route Component
 * Redirects to home if user is already authenticated
 */
function PublicRoute({ children }) {
    const { isAuthenticated, loading } = useAuth();

    if (loading) {
        return (
            <div className="app">
                <div className="loading-container">
                    <div className="loading-spinner"></div>
                    <p>Đang tải...</p>
                </div>
            </div>
        );
    }

    if (isAuthenticated) {
        return <Navigate to="/" replace />;
    }

    return children;
}

/**
 * Main App Routes
 */
function AppRoutes() {
    return (
        <Routes>
            {/* Public Routes */}
            <Route 
                path="/login" 
                element={
                    <PublicRoute>
                        <Login />
                    </PublicRoute>
                } 
            />
            <Route 
                path="/register" 
                element={
                    <PublicRoute>
                        <Register />
                    </PublicRoute>
                } 
            />
            
            {/* Protected Routes */}
            <Route 
                path="/" 
                element={
                    <ProtectedRoute>
                        <Home />
                    </ProtectedRoute>
                } 
            />
            
            {/* Fallback */}
            <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
    );
}

/**
 * Root App Component
 */
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
