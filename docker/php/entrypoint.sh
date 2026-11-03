#!/bin/sh
# Tự động chuẩn bị backend mỗi khi container php khởi động:
#   1) composer install nếu chưa có vendor/
#   2) đảm bảo thư mục writable ghi được
#   3) php spark migrate (kèm tạo admin mặc định qua migration SeedDefaultAdmin)
# Tắt bước migrate bằng cách đặt AUTO_MIGRATE=false trong .env

set -u
cd /var/www/html || exit 1

if [ ! -f vendor/autoload.php ] && [ -f composer.json ]; then
    echo "[entrypoint] Chưa có vendor/, chạy composer install..."
    composer install --no-interaction --prefer-dist || echo "[entrypoint] composer install thất bại"
fi

mkdir -p writable/cache writable/logs writable/session writable/uploads
chown -R www-data:www-data writable 2>/dev/null || true

if [ "${AUTO_MIGRATE:-true}" = "true" ] && [ -f spark ]; then
    echo "[entrypoint] Chạy migrate..."
    tries=0
    # Chạy bằng www-data để file log/cache tạo ra Apache vẫn ghi được
    until runuser -u www-data -- php spark migrate; do
        tries=$((tries + 1))
        if [ "$tries" -ge 15 ]; then
            echo "[entrypoint] migrate vẫn lỗi sau 15 lần thử, tiếp tục khởi động Apache (xem log phía trên)."
            break
        fi
        echo "[entrypoint] DB chưa sẵn sàng hoặc migrate lỗi, thử lại ($tries/15) sau 3 giây..."
        sleep 3
    done
fi

exec docker-php-entrypoint "$@"
