import "./feedback.css";

export default function PermissionDenied({
    title = "Không có quyền truy cập",
    message = "Tài khoản hiện tại không được phép thực hiện thao tác này.",
}) {
    return (
        <section className="feedback-denied" role="alert">
            <h2 className="feedback-title">{title}</h2>
            <p className="feedback-body">{message}</p>
        </section>
    );
}
