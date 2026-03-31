<?php
class Auth {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Регистрация
     */
    public function register(string $username, string $email, string $password): array {
        // Валидация
        if (empty($username) || empty($email) || empty($password)) {
            return ['success' => false, 'error' => 'Заполните все поля'];
        }
        if (strlen($username) < 3 || strlen($username) > 50) {
            return ['success' => false, 'error' => 'Имя пользователя: от 3 до 50 символов'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Некорректный email'];
        }
        if (strlen($password) < 6) {
            return ['success' => false, 'error' => 'Пароль должен быть не менее 6 символов'];
        }

        // Проверка уникальности
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Пользователь с таким именем или email уже существует'];
        }

        // Создание пользователя
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $verificationToken = bin2hex(random_bytes(32));

        $stmt = $this->db->prepare(
            "INSERT INTO users (username, email, password, verification_token) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$username, $email, $hashedPassword, $verificationToken]);

        // Отправка письма подтверждения
        $verifyUrl = SITE_URL . '/verify.php?token=' . $verificationToken;
        $mailer = new Mailer();
        $mailer->send(
            $email,
            'Подтверждение регистрации на ' . SITE_NAME,
            "Здравствуйте, $username!\n\n"
            . "Для подтверждения регистрации перейдите по ссылке:\n$verifyUrl\n\n"
            . "Если вы не регистрировались — проигнорируйте это письмо."
        );

        return ['success' => true, 'message' => 'Регистрация успешна! Проверьте email для подтверждения.'];
    }

    /**
     * Подтверждение email
     */
    public function verify(string $token): bool {
        $stmt = $this->db->prepare(
            "UPDATE users SET is_verified = 1, verification_token = NULL WHERE verification_token = ? AND is_verified = 0"
        );
        $stmt->execute([$token]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Авторизация
     */
    public function login(string $email, string $password, bool $remember = false): array {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'error' => 'Заполните все поля'];
        }

        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'error' => 'Неверный email или пароль'];
        }

        if (!$user['is_verified']) {
            return ['success' => false, 'error' => 'Аккаунт не подтверждён. Проверьте email.'];
        }

        // Установка сессии
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['avatar'] = $user['avatar'];

        // Запомнить меня
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $stmt = $this->db->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->execute([$token, $user['id']]);
            setcookie('remember_token', $token, time() + (86400 * REMEMBER_DAYS), '/', '', false, true);
        }

        return ['success' => true];
    }

    /**
     * Выход
     */
    public function logout(): void {
        if (isset($_SESSION['user_id'])) {
            $stmt = $this->db->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
        }
        session_destroy();
        setcookie('remember_token', '', time() - 3600, '/');
    }

    /**
     * Запрос на восстановление пароля
     */
    public function requestPasswordReset(string $email): array {
        if (empty($email)) {
            return ['success' => false, 'error' => 'Введите email'];
        }

        $stmt = $this->db->prepare("SELECT id, username FROM users WHERE email = ? AND is_verified = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'error' => 'Пользователь с таким email не найден'];
        }

        // Генерация 6-значного кода
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', strtotime('+' . RESET_CODE_LIFETIME . ' minutes'));

        $stmt = $this->db->prepare("UPDATE users SET reset_code = ?, reset_code_expires = ? WHERE id = ?");
        $stmt->execute([$code, $expires, $user['id']]);

        // Отправка письма
        $mailer = new Mailer();
        $mailer->send(
            $email,
            'Восстановление пароля — ' . SITE_NAME,
            "Здравствуйте, {$user['username']}!\n\n"
            . "Код для восстановления пароля: $code\n"
            . "Код действителен " . RESET_CODE_LIFETIME . " минут.\n\n"
            . "Если вы не запрашивали сброс пароля — проигнорируйте это письмо."
        );

        return ['success' => true, 'message' => 'Код отправлен на ваш email'];
    }

    /**
     * Сброс пароля
     */
    public function resetPassword(string $email, string $code, string $newPassword): array {
        if (empty($code) || empty($newPassword)) {
            return ['success' => false, 'error' => 'Заполните все поля'];
        }
        if (strlen($newPassword) < 6) {
            return ['success' => false, 'error' => 'Пароль должен быть не менее 6 символов'];
        }

        $stmt = $this->db->prepare(
            "SELECT id FROM users WHERE email = ? AND reset_code = ? AND reset_code_expires > NOW()"
        );
        $stmt->execute([$email, $code]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'error' => 'Неверный или просроченный код'];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare(
            "UPDATE users SET password = ?, reset_code = NULL, reset_code_expires = NULL WHERE id = ?"
        );
        $stmt->execute([$hashedPassword, $user['id']]);

        return ['success' => true, 'message' => 'Пароль успешно изменён'];
    }
}