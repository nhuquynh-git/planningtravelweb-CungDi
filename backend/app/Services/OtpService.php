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
     * Kiểm tra OTP. Đúng -> xoá bản ghi (nếu $consume) và trả payload. Sai -> ném RuntimeException.
     */
    public function verify(string $email, string $purpose, string $code, bool $consume = true): array
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

        if ($consume) {
            $db->table('email_otps')->where('id', $row['id'])->delete();
        }

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
                $subject = 'Mã đặt lại mật khẩu Cùng Đi: ' . $code;
                $title   = 'Đặt lại mật khẩu';
                $intro   = 'Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn. Nhập mã bên dưới để tiếp tục.';
                $note    = 'Nếu bạn không yêu cầu, hãy bỏ qua email này, mật khẩu của bạn sẽ không thay đổi.';
            } else {
                $subject = 'Mã xác thực tài khoản Cùng Đi: ' . $code;
                $title   = 'Xác nhận email của bạn';
                $intro   = 'Cảm ơn bạn đã đăng ký Cùng Đi. Nhập mã bên dưới để hoàn tất tạo tài khoản.';
                $note    = 'Nếu bạn không đăng ký tài khoản, hãy bỏ qua email này.';
            }

            $mail->setSubject($subject);
            $mail->setMailType('html');
            $mail->setMessage($this->buildHtml($title, $intro, $code, $note));
            // Bản chữ thuần cho ứng dụng mail không hiển thị được HTML
            $mail->setAltMessage(
                "{$title}\n\n{$intro}\n\nMã xác nhận: {$code} (hiệu lực trong 5 phút)\n\n" .
                "Không chia sẻ mã này cho bất kỳ ai.\n{$note}\n\n" .
                "--\nĐây là email tự động từ Cùng Đi, vui lòng không trả lời email này."
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

    /**
     * Giao diện email OTP (HTML email: dùng bảng + CSS inline để hiển thị đúng ở Gmail/Outlook).
     */
    protected function buildHtml(string $title, string $intro, string $code, string $note): string
    {
        $title = esc($title);
        $intro = esc($intro);
        $note  = esc($note);
        $code  = esc($code);

        return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light only">
<title>{$title}</title>
</head>
<body style="margin:0;padding:0;background-color:#EEF4F7;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:#EEF4F7;">Mã của bạn: {$code}. Có hiệu lực trong 5 phút.</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#EEF4F7" style="background-color:#EEF4F7;">
<tr><td align="center" style="padding:28px 12px;">

<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;border-radius:18px;overflow:hidden;background-color:#FFFFFF;box-shadow:0 6px 24px rgba(11,47,74,0.14);font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

  <!-- Header -->
  <tr><td bgcolor="#0E4D6E" style="background-color:#0E4D6E;padding:18px 28px;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
      <td width="38" height="38" align="center" valign="middle" bgcolor="#FDBA3B" style="width:38px;height:38px;background-color:#FDBA3B;border-radius:19px;font-size:21px;font-weight:700;line-height:38px;color:#0E4D6E;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">C</td>
      <td style="padding-left:12px;font-size:21px;font-weight:700;color:#FFFFFF;letter-spacing:0.2px;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">Cùng Đi</td>
    </tr></table>
  </td></tr>

  <!-- Hero -->
  <tr><td bgcolor="#1E8B91" style="background-color:#1E8B91;background-image:linear-gradient(135deg,#1E8B91 0%,#3FA9C4 100%);padding:30px 28px 32px 28px;">
    <div style="font-size:26px;line-height:1.25;font-weight:700;color:#FFFFFF;">{$title}</div>
    <div style="margin-top:10px;font-size:15px;line-height:1.6;color:#E3F4F5;">{$intro}</div>
  </td></tr>

  <!-- Body -->
  <tr><td style="padding:30px 28px 8px 28px;">
    <div style="font-size:13px;color:#6B8494;text-align:center;margin-bottom:12px;">Mã xác nhận của bạn</div>

    <table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
      <tr><td align="center" bgcolor="#EAF6F7" style="background-color:#EAF6F7;border:1px solid #BFE3E5;border-radius:16px;padding:18px 26px 18px 36px;font-size:38px;line-height:1;font-weight:700;letter-spacing:10px;color:#14707A;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">{$code}</td></tr>
    </table>

    <table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0" style="margin:18px auto 0 auto;">
      <tr><td bgcolor="#FFF3D6" style="background-color:#FFF3D6;border-radius:999px;padding:7px 16px;font-size:13px;font-weight:600;color:#8A5A00;">Hiệu lực trong 5 phút</td></tr>
    </table>
  </td></tr>

  <tr><td style="padding:22px 28px 30px 28px;">
    <div style="border-top:1px solid #E3ECF1;padding-top:20px;font-size:14px;line-height:1.7;color:#12394F;">
      <p style="margin:0 0 10px 0;">Vì lý do bảo mật, <strong>không chia sẻ mã này cho bất kỳ ai</strong>, kể cả nhân viên Cùng Đi.</p>
      <p style="margin:0;color:#6B8494;">{$note}</p>
    </div>
  </td></tr>

  <!-- Footer -->
  <tr><td bgcolor="#0B2F4A" align="center" style="background-color:#0B2F4A;padding:20px 28px;">
    <div style="font-size:13px;font-weight:600;color:#FFFFFF;">© Cùng Đi</div>
    <div style="margin-top:6px;font-size:12px;line-height:1.6;color:#9FB6C5;">Lên kế hoạch du lịch thông minh &amp; quản lý chi tiêu nhóm dễ dàng.<br>Đây là email tự động, vui lòng không trả lời email này.</div>
  </td></tr>

</table>

</td></tr>
</table>
</body>
</html>

HTML;
    }
}
