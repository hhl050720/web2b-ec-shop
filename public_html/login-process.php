
<?php
session_start();

require_once __DIR__ . '/../includes/config.php';

// POST以外のアクセスはログイン画面へ戻す
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// 入力値を取得
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// 入力チェック
if ($email === '' || $password === '') {
    $_SESSION['login_error'] = 'メールアドレスまたはパスワードが正しくありません。';
    header('Location: login.php');
    exit;
}

try {
    // メールアドレスからユーザーを取得
    $sql = 'SELECT id, email, password, name, role, failed_count, locked_until
            FROM users
            WHERE email = :email';

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch();

    // ユーザーが存在しない場合も同じエラーメッセージにする
    if (!$user) {
        $_SESSION['login_error'] = 'メールアドレスまたはパスワードが正しくありません。';
        header('Location: login.php');
        exit;
    }

    // アカウントがロック中か確認
    if (
        $user['locked_until'] !== null &&
        strtotime($user['locked_until']) > time()
    ) {
        $_SESSION['login_error'] = 'メールアドレスまたはパスワードが正しくありません。';
        header('Location: login.php');
        exit;
    }

    // パスワードを確認（授業用の平文パスワード）
    if ($password !== $user['password']) {
        $failedCount = (int)$user['failed_count'] + 1;

        // 5回失敗したら5分間ロック
        if ($failedCount >= 5) {
            $sql = 'UPDATE users
                    SET failed_count = 5,
                        locked_until = DATE_ADD(NOW(), INTERVAL 5 MINUTE)
                    WHERE id = :id';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $user['id'], PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $sql = 'UPDATE users
                    SET failed_count = :failed_count
                    WHERE id = :id';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':failed_count', $failedCount, PDO::PARAM_INT);
            $stmt->bindValue(':id', $user['id'], PDO::PARAM_INT);
            $stmt->execute();
        }

        $_SESSION['login_error'] = 'メールアドレスまたはパスワードが正しくありません。';
        header('Location: login.php');
        exit;
    }

    // ログイン成功：失敗回数とロック時間をリセット
    $sql = 'UPDATE users
            SET failed_count = 0,
                locked_until = NULL
            WHERE id = :id';

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $user['id'], PDO::PARAM_INT);
    $stmt->execute();

    // セッションIDを再生成
    session_regenerate_id(true);

    // ログイン情報をセッションに保存
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];

    // マイページへ移動
    header('Location: mypage.php');
    exit;

} catch (PDOException $e) {
    // 開発中の簡易エラー表示
    die('ログイン処理でエラーが発生しました。');
}
