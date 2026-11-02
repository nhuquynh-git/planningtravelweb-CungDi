# Team workflow

Tài liệu này mô tả cách bốn workstream phối hợp trên cùng một repo. Không gán tên thành viên; nhóm tự map workstream với người phụ trách.

## Workstream

| Workstream | Phạm vi chính | File dễ xung đột |
|---|---|---|
| API, Authentication & Security | Hợp đồng API, Shield, CORS/CSRF, recovery email | `backend/app/Config/*`, `Routes.php`, `Auth*` |
| Plan, Itinerary & Frontend | UI dùng chung, Plan/member/itinerary, Guest nav | `frontend/src/**`, Plan controllers |
| Database, Expenses & Reporting | Migration, chi phí, tổng hợp, test DB | `backend/app/Database/Migrations/**`, expense services |
| Community, Admin & Academic docs | Admin, minigame, UML, báo cáo | Admin/minigame routes, `docs/**` |

## Nhánh theo task

1. Lấy `main` mới nhất trước khi bắt đầu.
2. Tạo nhánh `task/T0xx-short-name` (một task Spec Kit, hoặc một nhóm `[P]` không đụng cùng file).
3. Không trộn quyết định DEFERRED (Q-014, Q-021, …) vào nhánh implementation.
4. Không sửa `docs/database/ERD.png` cho đến khi T004/T061 được duyệt.

## Pull request

- Một PR = một task ID (hoặc vài task `[P]` độc lập file).
- Mô tả: task ID, hành vi đã đổi, cách kiểm tra, file schema/API đã đụng.
- Reviewer tối thiểu một người ngoài workstream nếu thay `Routes.php`, migration, hoặc hợp đồng API.
- Không merge khi test liên quan fail, hoặc khi PR tự điền quyết định còn BLOCKED.

## Phối hợp API và schema

- API: cập nhật `.specify/specs/001-travel-planner-product/contracts/api-contract.md` trong cùng PR với route/controller.
- Schema: migration mới, không sửa migration đã áp dụng local nếu đã có dữ liệu; ghi rõ lệnh `spark migrate`.
- Envelope JSON giữ `status`, `message`, `data`, optional `errors`.
- Không commit `.env`, mật khẩu, token. Không `docker compose down -v`.

## Handoff bốn workstream

Khi chuyển việc, ghi một khối theo mẫu:

```text
Task: T0xx
Workstream: (API | Plan-FE | Database-Expense | Community-Admin)
Done: ...
Not done: ...
Shared files touched: ...
Blocked on: (decision ID or none)
How to verify: ...
```

Dán khối này vào mô tả PR hoặc issue. Người nhận không được giả định quyết định chưa ghi trong spec/plan.
