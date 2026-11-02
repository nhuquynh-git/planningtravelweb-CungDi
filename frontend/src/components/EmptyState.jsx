import "./feedback.css";

export default function EmptyState({
    title = "Chưa có dữ liệu",
    message,
    action = null,
}) {
    return (
        <section className="feedback-empty">
            <h2 className="feedback-title">{title}</h2>
            {message ? <p className="feedback-body">{message}</p> : null}
            {action ? <div className="feedback-action">{action}</div> : null}
        </section>
    );
}
