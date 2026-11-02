# Cùng Đi – Travel Planner

Website giúp lên kế hoạch du lịch và quản lý chi tiêu cá nhân hoặc nhóm.

Stack hiện tại: React 19 + Vite 8 (JavaScript), CodeIgniter 4 + Shield (PHP 8.2+), MySQL 8.4, Docker Compose. API dùng tiền tố `/api`. Không thêm framework hay vai trò mới.

Hướng dẫn kiểm tra chi tiết: [`.specify/specs/001-travel-planner-product/quickstart.md`](.specify/specs/001-travel-planner-product/quickstart.md).  
Quy trình nhóm: [`docs/development/team-workflow.md`](docs/development/team-workflow.md).

## Khởi động local

### Điều kiện

- Docker Desktop (Compose)
- Node.js/npm theo `frontend/package-lock.json`
- Composer 2 và PHP 8.2+ nếu chạy test backend trên máy host

### 1. Sao chép biến môi trường

Từ thư mục gốc repo, PowerShell:

```powershell
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
if (-not (Test-Path backend\.env)) { Copy-Item backend\.env.example backend\.env }
if (-not (Test-Path frontend\.env)) { Copy-Item frontend\.env.example frontend\.env }
```

Đối chiếu mật khẩu MySQL giữa `.env` gốc và `backend\.env`. **Không commit** file `.env`. **Không ghi đè** file môi trường đã có.

### 2. Cài dependency

```powershell
Push-Location backend
composer install
Pop-Location

Push-Location frontend
npm ci
Pop-Location
```

### 3. Docker (API + MySQL)

```powershell
docker compose up --build -d
docker compose ps
```

- API: `http://localhost:8001`
- MySQL host: cổng `3308`
- Frontend **không** nằm trong Compose; chạy Vite ở bước 5

**Không dùng** `docker compose down -v`. Cờ `-v` xóa volume MySQL và mất dữ liệu local.

### 4. Migration (database local)

Chỉ chạy khi `backend/.env` trỏ đúng database phát triển local:

```powershell
docker compose exec -T php php spark migrate -n "CodeIgniter\Shield"
docker compose exec -T php php spark migrate -n App
```

`database/init.sql` chỉ tạo bảng kiểm tra kết nối. Schema nghiệp vụ đến từ migration CodeIgniter.

### 5. Frontend

Terminal khác:

```powershell
Push-Location frontend
npm run dev
```

Mở URL Vite (thường `http://localhost:5173`). Dừng Vite bằng `Ctrl+C`. Dừng Docker **không** xóa volume:

```powershell
docker compose down
```

## Kiểm tra nhanh

API/MySQL:

```powershell
$response = Invoke-RestMethod http://localhost:8001/api/health
$response.status
$response.data.backend
$response.data.database
```

Kỳ vọng: `success`, `connected`, `connected`.

Frontend:

```powershell
Push-Location frontend
npm run lint
npm run build
Pop-Location
```

Backend (PHPUnit trên host; database **test** cô lập, xem `backend/README.md`):

```powershell
Push-Location backend
composer test
Pop-Location
```

Hiện API nghiệp vụ chưa có; màn hình React đang kiểm tra health. Không triển khai AWS từ các lệnh trên.
