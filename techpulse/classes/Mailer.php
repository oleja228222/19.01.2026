<?php

// Если без Composer — подключаем вручную
if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    require_once __DIR__ . '/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/SMTP.php';
    require_once __DIR__ . '/PHPMailer/Exception.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    /**
     * Отправка email через SMTP
     */
    public function send(string $to, string $subject, string $body): bool
    {
        // === Всегда логируем (для отладки) ===
        $this->log($to, $subject, $body);

        $mail = new PHPMailer(true); // true = бросает исключения

        try {
            // ── Настройки SMTP ──
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL (порт 465)
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            // ── Отправитель и получатель ──
            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            $mail->addAddress($to);

            // ── Содержимое письма ──
            $mail->isHTML(false);      // отправляем как plain text
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();

            $this->log($to, '[OK] Письмо отправлено', '');
            return true;

        } catch (Exception $e) {
            // Логируем ошибку
            $this->log($to, '[ОШИБКА]', $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Логирование в файл (для отладки)
     */
    private function log(string $to, string $subject, string $body): void
    {
        $logDir = __DIR__ . '/../logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $logContent = "[$timestamp]\nTO: $to\nSUBJECT: $subject\n$body\n---\n\n";
        file_put_contents($logDir . 'mail.log', $logContent, FILE_APPEND);
    }
}