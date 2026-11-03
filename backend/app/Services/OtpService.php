<?php

namespace App\Services;

/**
 * Quản lý mã OTP 6 số gửi qua email (đăng ký + quên mật khẩu).
 * - Mã được băm trước khi lưu, hết hạn sau 5 phút, tối đa 5 lần nhập sai.
 * - Với purpose=register, payload chứa username + password_hash để tạo user SAU khi OTP đúng.
 */
class OtpService
{
    public const PURPOSE_REGISTER = 'register';
    public const PURPOSE_RESET    = 'reset';

    private const TTL_SECONDS      = 300;
    private const RESEND_COOLDOWN  = 60;
    private const MAX_ATTEMPTS     = 5;

    /**
     * Tạo OTP mới, lưu DB và gửi email. Trả về mã (chỉ dùng để hiển thị ở môi trường dev).
     */
    public function issue(string $email, string $purpose, ?array $payload = null): string
    {
        $email = strtolower(trim($email));
        $db    = db_connect();
        $now   = time();

        // Dọn bản ghi cũ quá 1 ngày
        $db->query('DELETE FROM email_otps WHERE expires_at < ?', [date('Y-m-d H:i:s', $now - 86400)]);

        $row = $db->table('email_otps')->where(['email' => $email, 'purpose' => $purpose])->get()->getRowArray();

        if ($row !== null) {
            $wait = self::RESEND_COOLDOWN - ($now - strtotime($row['last_sent_at']));
            if ($wait > 0) {
                throw new \RuntimeException("Vui lòng đợi {$wait} giây rồi thử gửi lại mã");
            }
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $data = [
            'email'        => $email,
            'purpose'      => $purpose,
            'code_hash'    => password_hash($code, PASSWORD_DEFAULT),
            'payload'      => $payload !== null ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null,
            'attempts'     => 0,
            'expires_at'   => date('Y-m-d H:i:s', $now + self::TTL_SECONDS),
            'last_sent_at' => date('Y-m-d H:i:s', $now),
        ];

        if ($row === null) {
            $data['created_at'] = date('Y-m-d H:i:s', $now);
            $db->table('email_otps')->insert($data);
        } else {
            $db->table('email_otps')->where('id', $row['id'])->update($data);
        }

        $sent = $this->sendMail($email, $purpose, $code);

        if (! $sent && ENVIRONMENT === 'production') {
            $db->table('email_otps')->where(['email' => $email, 'purpose' => $purpose])->delete();
            throw new \RuntimeException('Không thể gửi email lúc này. Vui lòng thử lại sau.');
        }

        return $code;
    }

    /**
     * Gửi lại mã cho phiên đã có (giữ nguyên payload đăng ký).
     */
    public function resend(string $email, string $purpose): string
    {
        $email = strtolower(trim($email));
        $row   = db_connect()->table('email_otps')->where(['email' => $email, 'purpose' => $purpose])->get()->getRowArray();

        if ($row === null) {
            throw new \RuntimeException('Phiên xác nhận không còn hiệu lực. Vui lòng đăng ký lại.');
        }

        $payload = $row['payload'] ? json_decode($row['payload'], true) : null;

        return $this->issue($email, $purpose, $payload);
    }

    /**
     * Kiểm tra OTP. Đúng -> xoá bản ghi và trả payload. Sai -> ném RuntimeException.
     */
    public function verify(string $email, string $purpose, string $code): array
    {
        $email = strtolower(trim($email));
        $code  = trim($code);
        $db    = db_connect();

        $row = $db->table('email_otps')->where(['email' => $email, 'purpose' => $purpose])->get()->getRowArray();

        if ($row === null) {
            throw new \RuntimeException('Mã xác nhận không tồn tại hoặc đã được sử dụng. Vui lòng gửi lại mã.');
        }

        if (strtotime($row['expires_at']) < time()) {
            throw new \RuntimeException('Mã xác nhận đã hết hạn. Vui lòng bấm Gửi lại mã.');
        }

        if ((int) $row['attempts'] >= self::MAX_ATTEMPTS) {
            $db->table('email_otps')->where('id', $row['id'])->delete();
            throw new \RuntimeException('Bạn đã nhập sai quá nhiều lần. Vui lòng yêu cầu mã mới.');
        }

        if (! password_verify($code, $row['code_hash'])) {
            $db->query('UPDATE email_otps SET attempts = attempts + 1 WHERE id = ?', [$row['id']]);
            throw new \RuntimeException('Mã xác nhận không đúng. Vui lòng kiểm tra lại.');
        }

        $db->table('email_otps')->where('id', $row['id'])->delete();

        return $row['payload'] ? (json_decode($row['payload'], true) ?: []) : [];
    }

    protected function sendMail(string $email, string $purpose, string $code): bool
    {
        // Luôn ghi log để test local (writable/logs)
        log_message('info', ">>> OTP [{$purpose}] for [{$email}]: {$code} (5 phút) <<<");

        try {
            $cfg  = config('Email');
            $mail = service('email', null, false);

            $fromEmail = $cfg->fromEmail ?: $cfg->SMTPUser;
            if (empty($fromEmail)) {
                log_message('error', 'Email chưa cấu hình: thiếu email.fromEmail / email.SMTPUser trong .env');
                return false;
            }

            $mail->setFrom($fromEmail, $cfg->fromName ?: 'Cùng Đi');
            $mail->setTo($email);

            if ($purpose === self::PURPOSE_RESET) {
                $mail->setSubject('Mã đặt lại mật khẩu Cùng Đi: ' . $code);
                $intro = 'Mã đặt lại mật khẩu của bạn trên Cùng Đi Travel Planner là';
            } else {
                $mail->setSubject('Mã xác thực tài khoản Cùng Đi: ' . $code);
                $intro = 'Mã xác thực tài khoản của bạn trên Cùng Đi Travel Planner là';
            }

            $mail->setMessage(
                "Xin chào,\n\n{$intro}: {$code}\n" .
                "Mã có hiệu lực trong 5 phút. Không chia sẻ mã này cho bất kỳ ai.\n\n" .
                "Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email."
            );

            if (! $mail->send(false)) {
                log_message('error', 'Gửi OTP thất bại: ' . $mail->printDebugger(['headers']));
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            log_message('error', 'Lỗi gửi email OTP: ' . $e->getMessage());
            return false;
        }
    }
}
