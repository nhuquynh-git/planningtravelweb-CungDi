
import { useEffect, useState } from "react";
import "./App.css";

function App() {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");

    useEffect(() => {
        fetch("http://localhost:8001")
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Không thể kết nối API");
                }

                return response.json();
            })
            .then((result) => {
                if (!result.success) {
                    throw new Error(result.message);
                }

                setData(result.data);
            })
            .catch((err) => {
                setError(err.message);
            })
            .finally(() => {
                setLoading(false);
            });
    }, []);

    return (
        <div className="app">
            <h1>Travel Planner</h1>
            <h2>React + PHP + MySQL</h2>

            {loading && <p>Đang tải dữ liệu...</p>}

            {error && <p className="error">{error}</p>}

            {!loading && !error && (
                <div>
                    <h3>Dữ liệu từ MySQL</h3>

                    {data.map((item) => (
                        <div className="data-item" key={item.id}>
                            <p>ID: {item.id}</p>
                            <p>Message: {item.message}</p>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}

export default App;
