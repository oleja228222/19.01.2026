    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-inner">
                <div class="footer-brand">
                    <span class="logo-icon">⚡</span>
                    <span class="logo-text"><?= SITE_NAME ?></span>
                    <p class="footer-desc">Платформа для IT-статей и новостей технологий</p>
                </div>
                <div class="footer-links">
                    <h4>Категории</h4>
                    <?php
                    $catStmt = $db->query("SELECT name, slug FROM categories LIMIT 4");
                    while ($cat = $catStmt->fetch()):
                    ?>
                        <a href="<?= SITE_URL ?>/?category=<?= $cat['slug'] ?>"><?= e($cat['name']) ?></a>
                    <?php endwhile; ?>
                </div>
                <div class="footer-links">
                    <h4>Навигация</h4>
                    <a href="<?= SITE_URL ?>">Главная</a>
                    <a href="<?= SITE_URL ?>/register.php">Регистрация</a>
                    <a href="<?= SITE_URL ?>/login.php">Вход</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Все права защищены.</p>
            </div>
        </div>
    </footer>

    <script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>