
<?php
session_start();

// ログインしていない場合はログイン画面へ戻す
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userName = $_SESSION['user_name'] ?? 'ユーザー';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>マイページ</title>
</head>
<body>
    <h1>マイページ</h1>

    <p>
        <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?> さん、
        ログインしました。
    </p>

    <p>ログインに成功しました。</p>

    <a href="logout.php">ログアウト</a>
</body>
</html>
