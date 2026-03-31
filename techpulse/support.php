<?php
require_once 'config.php';
$pageTitle = 'Поддержать автора';

if (!$currentUser) redirect(SITE_URL . '/login.php');

$authorId = (int)($_GET['author_id'] ?? 0);
$stmt = $db->prepare("SELECT id, username, avatar, bio FROM users WHERE id = ?");
$stmt->execute([$authorId]);
$author = $stmt->fetch();

if (!$author || $author['id'] === $currentUser['id']) {
    setFlash('error', 'Автор не найден');
    redirect(SITE_URL);
}

// Уже поддержал
if (hasSupported($currentUser['id'], $authorId)) {
    setFlash('success', 'Вы уже поддерживаете этого автора!');
    redirect(SITE_URL . '/profile.php?id=' . $authorId);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float)($_POST['amount'] ?? 0);

    if ($amount < 50) {
        $errors[] = 'Минимальная сумма поддержки — 50 ₽';
    } elseif ($amount > 10000) {
        $errors[] = 'Максимальная сумма — 10 000 ₽';
    }

    if (empty($errors)) {
        // Создаём запись о поддержке
        $stmt = $db->prepare(
            "INSERT INTO supports (supporter_id, author_id, amount, status) VALUES (?, ?, ?, 'created')"
        );
        $stmt->execute([$currentUser['id'], $authorId, $amount]);
        $supportId = $db->lastInsertId();

        // Создаём платёж в ЮKassa
        $idempotenceKey = uniqid('', true);
        $paymentData = [
            'amount' => [
                'value' => number_format($amount, 2, '.', ''),
                'currency' => 'RUB'
            ],
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => SITE_URL . '/payment_return.php?support_id=' . $supportId
            ],
            'capture' => true,
            'description' => 'Поддержка автора ' . $author['username'] . ' на TechPulse',
            'metadata' => [
                'support_id' => $supportId
            ]
        ];

        $ch = curl_init('https://api.yookassa.ru/v3/payments');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($paymentData),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Idempotence-Key: ' . $idempotenceKey
    ],
    CURLOPT_USERPWD => YOKASSA_SHOP_ID . ':' . YOKASSA_SECRET_KEY,
    CURLOPT_SSL_VERIFYPEER => false,  // ← добавить только для локального теста
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

error_log("YooKassa HTTP: $httpCode, Response: $response, Curl error: $curlError");
error_log("YooKassa POST data: " . json_encode($paymentData));

$result = json_decode($response, true);

if ($httpCode === 200 && isset($result['id'])) {
    // успех...
} else {
    error_log("YooKassa error details: " . print_r($result, true));
    $errors[] = 'Ошибка создания платежа. Попробуйте позже.';
}
    }
}

require 'includes/header.php';
?>

<div class="container">
    <div class="support-wrapper">
        <div class="support-card">
            <div class="support-author">
                <img src="<?= avatarUrl($author['avatar']) ?>" alt="" class="avatar-lg">
                <h2><?= e($author['username']) ?></h2>
                <?php if ($author['bio']): ?>
                    <p class="text-muted"><?= e(mb_substr($author['bio'], 0, 100)) ?></p>
                <?php endif; ?>
            </div>

            <h1 class="support-title">Поддержать автора</h1>
            <p class="support-desc">
                После поддержки вы получите <strong>постоянный доступ</strong> ко всем премиум-статьям этого автора
            </p>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="support-form">
                <div class="form-group">
                    <label class="form-label">Выберите сумму</label>
                    <div class="amount-grid">
                        <button type="button" class="amount-btn" data-amount="100" onclick="setAmount(100)">100 ₽</button>
                        <button type="button" class="amount-btn" data-amount="300" onclick="setAmount(300)">300 ₽</button>
                        <button type="button" class="amount-btn" data-amount="500" onclick="setAmount(500)">500 ₽</button>
                        <button type="button" class="amount-btn" data-amount="1000" onclick="setAmount(1000)">1 000 ₽</button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="amount">Или введите свою сумму (от 50 ₽)</label>
                    <div class="input-with-suffix">
                        <input type="number" id="amount" name="amount" class="form-input"
                               min="50" max="10000" step="10" value="100" required>
                        <span class="input-suffix">₽</span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-full">
                    💳 Перейти к оплате
                </button>

                <p class="text-muted text-center text-sm mt-2">
                    Оплата через ЮKassa. Безопасно и надёжно.
                </p>
            </form>
        </div>
    </div>
</div>

<script>
function setAmount(val) {
    document.getElementById('amount').value = val;
    document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
    document.querySelector(`[data-amount="${val}"]`)?.classList.add('active');
}
</script>

<?php require 'includes/footer.php'; ?>