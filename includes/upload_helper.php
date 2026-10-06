<?php
/**
 * includes/upload_helper.php
 *
 * 画像アップロードの安全な受け取り方
 * 中級：サイズ制限 + 拡張子チェック
 * （上級のMIMEチェックは省略。実運用に最低限必要な保存処理は実装）
 *
 * @return array{ok:bool, filename:?string, error:?string}
 */
function handleImageUpload(array $file, string $destDir): array
{
    $allowedExt  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize     = 2 * 1024 * 1024; // 2MB

    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'filename' => null, 'error' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'filename' => null, 'error' => 'アップロードに失敗しました'];
    }

    // 中級：ファイルサイズチェック
    if ($file['size'] > $maxSize) {
        return ['ok' => false, 'filename' => null, 'error' => '画像は2MB以内にしてください'];
    }

    // 中級：拡張子チェック
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return ['ok' => false, 'filename' => null, 'error' => '許可されていないファイル形式です（jpg/png/gif/webpのみ）'];
    }

    // 保存処理（中級を動かすために必要。ファイル名はランダム化）
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $destPath = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['ok' => false, 'filename' => null, 'error' => 'ファイルの保存に失敗しました'];
    }

    return ['ok' => true, 'filename' => $filename, 'error' => null];
}
