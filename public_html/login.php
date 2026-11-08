
<?php
session_start();

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン</title>
</head>
<body>
    <h1>ログイン</h1>

    <?php if ($error !== ''): ?>
        <p style="color: red;">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form action="login-process.php" method="POST">
        <div>
            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div>
            <label for="password">パスワード</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit">ログイン</button>
    </form>
</body>
</html>
