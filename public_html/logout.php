
<?php
session_start();

// セッション情報を削除
$_SESSION = [];

// セッションを破棄
session_destroy();

// ログイン画面へ戻す
header('Location: login.php');
exit;
