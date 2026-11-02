import { useEffect, useId, useRef } from "react";
import "./feedback.css";

export default function ValidationErrorSummary({
    title = "Có lỗi cần sửa",
    errors = [],
    moveFocus = true,
}) {
    const headingId = useId();
    const containerRef = useRef(null);
    const items = errors.filter((item) => item && item.message);

    useEffect(() => {
        if (moveFocus && items.length > 0) {
            containerRef.current?.focus();
        }
    }, [moveFocus, items.length]);

    if (items.length === 0) {
        return null;
    }

    return (
        <div
            ref={containerRef}
            className="feedback-errors"
            role="alert"
            tabIndex={-1}
            aria-labelledby={headingId}
        >
            <h2 className="feedback-title" id={headingId}>
                {title}
            </h2>
            <ul className="feedback-error-list">
                {items.map((item) => (
                    <li key={item.field ?? item.message}>
                        {item.field ? (
                            <a href={`#${item.field}`}>{item.message}</a>
                        ) : (
                            item.message
                        )}
                    </li>
                ))}
            </ul>
        </div>
    );
}
