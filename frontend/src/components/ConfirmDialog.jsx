import { useEffect, useId, useRef } from "react";
import "./feedback.css";

function getFocusable(root) {
    if (!root) {
        return [];
    }

    return Array.from(
        root.querySelectorAll(
            'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
        ),
    );
}

export default function ConfirmDialog({
    open,
    title,
    message,
    confirmLabel = "Xác nhận",
    cancelLabel = "Hủy",
    danger = false,
    onConfirm,
    onCancel,
}) {
    const titleId = useId();
    const messageId = useId();
    const panelRef = useRef(null);
    const previousFocusRef = useRef(null);

    useEffect(() => {
        if (!open) {
            return undefined;
        }

        previousFocusRef.current = document.activeElement;
        const panel = panelRef.current;
        const focusable = getFocusable(panel);
        (focusable[0] ?? panel)?.focus();

        function onKeyDown(event) {
            if (event.key === "Escape") {
                event.preventDefault();
                onCancel();
                return;
            }

            if (event.key !== "Tab") {
                return;
            }

            const nodes = getFocusable(panel);
            if (nodes.length === 0) {
                event.preventDefault();
                panel?.focus();
                return;
            }

            const first = nodes[0];
            const last = nodes[nodes.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }

        document.addEventListener("keydown", onKeyDown);

        return () => {
            document.removeEventListener("keydown", onKeyDown);
            if (
                previousFocusRef.current instanceof HTMLElement
                && previousFocusRef.current.isConnected
            ) {
                previousFocusRef.current.focus();
            }
        };
    }, [open, onCancel]);

    if (!open) {
        return null;
    }

    return (
        <div className="feedback-dialog-root">
            <div
                className="feedback-dialog-backdrop"
                onClick={onCancel}
            />
            <div
                ref={panelRef}
                className="feedback-dialog-panel"
                role="dialog"
                aria-modal="true"
                aria-labelledby={titleId}
                aria-describedby={messageId}
                tabIndex={-1}
            >
                <h2 className="feedback-title" id={titleId}>
                    {title}
                </h2>
                <p className="feedback-body" id={messageId}>
                    {message}
                </p>
                <div className="feedback-dialog-actions">
                    <button
                        type="button"
                        className="feedback-button"
                        onClick={onCancel}
                    >
                        {cancelLabel}
                    </button>
                    <button
                        type="button"
                        className={
                            danger
                                ? "feedback-button feedback-button-danger"
                                : "feedback-button feedback-button-primary"
                        }
                        onClick={onConfirm}
                    >
                        {confirmLabel}
                    </button>
                </div>
            </div>
        </div>
    );
}
