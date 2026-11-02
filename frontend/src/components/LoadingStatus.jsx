import "./feedback.css";

export default function LoadingStatus({
    label = "Đang tải…",
    busy = true,
}) {
    return (
        <div
            className="feedback-status"
            role="status"
            aria-live="polite"
            aria-busy={busy}
        >
            <p className="feedback-body">{label}</p>
        </div>
    );
}
