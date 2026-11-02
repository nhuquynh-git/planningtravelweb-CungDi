
import { useEffect, useState } from "react";
import { getHealth } from "./services/healthService";
import "./App.css";

function App() {
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

    return (
        <div className="app">
            <h1>Travel Planner</h1>
            <h2>React + CodeIgniter 4 + MySQL</h2>

            {loading && <p>Đang kiểm tra kết nối...</p>}

            {error && (
                <div className="error">
                    <p>Backend: Không kết nối được</p>
                    <p>{error}</p>
                </div>
            )}

            {!loading && !error && health && (
                <div className="data-item">
                    <h3>Trạng thái hệ thống</h3>

                    <p>
                        Backend: {health.status === "success"
                            ? "Đang hoạt động"
                            : "Không hoạt động"}
                    </p>

                    <p>Message: {result.message}</p>

                    <p>
                        MySQL: {health.database === "connected"
                            ? "Đã kết nối"
                            : "Chưa kết nối"}
                    </p>
                </div>
            )}
        </div>
    );
}

export default App;
