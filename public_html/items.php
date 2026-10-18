<?php
/**
 * items.php ― 商品一覧・キーワード検索・カテゴリ絞り込み・ページネーション
 *
 * 初級：一覧表示 → すでに動きます
 * 中級：LIKE検索・カテゴリ絞り込み・0件時の表示 → 実装済み
 * 上級：検索条件を保ったままのページネーション → TODOのまま
 */
require_once __DIR__ . '/../includes/config.php';

// ---- ① 検索条件の取得 ----------------------------------------------------
$keyword  = trim($_GET['keyword'] ?? '');
$category = trim($_GET['category'] ?? '');

// 上級用（未実装のまま最低限動く値を入れておく）
$page    = 1;
$perPage = 9;

// 並び替えの選択肢
$sortOptions = [
    'new'        => 'created_at DESC',
    'price_asc'  => 'price ASC',
    'price_desc' => 'price DESC',
];

$sort    = $_GET['sort'] ?? 'new';
$orderBy = $sortOptions[$sort] ?? $sortOptions['new'];

// ---- ② WHERE句を動的に組み立てる（必ずプレースホルダを使うこと）------------
$conditions = [];
$params     = [];

// 中級：キーワード検索（LIKE）。% と _ をエスケープしてから渡す
if ($keyword !== '') {
    $conditions[] = 'name LIKE :keyword';
    // LIKEのワイルドカード文字をエスケープ
    $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $keyword);
    $params[':keyword'] = '%' . $escaped . '%';
}

// 中級：カテゴリ絞り込み
if ($category !== '') {
    $conditions[] = 'category = :category';
    $params[':category'] = $category;
}

$whereSql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

// ---- ③ 該当件数（上級用・簡易）--------------------------------------------
$totalCount = 0;
$totalPages = 1;
$offset     = 0;

// ---- ④ 商品一覧を取得する ------------------------------------------------
$sql = "SELECT id, name, price, image, stock, category
        FROM items
        {$whereSql}
        ORDER BY {$orderBy}
        LIMIT {$perPage} OFFSET {$offset}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

$categories = $pdo->query('SELECT DISTINCT category FROM items WHERE category IS NOT NULL ORDER BY category')
                   ->fetchAll(PDO::FETCH_COLUMN);

// 上級用（未実装）
function buildQuery(array $overrides = []): string
{
    return '';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商品一覧 ― Tsuchi-to-Hi</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header><a href="index.php">Tsuchi-to-Hi</a></header>
    <main>
        <h1>商品一覧</h1>
        
<form class="search-form" method="get" action="items.php">
    <input type="text" name="keyword" placeholder="商品名で検索"
           value="<?= htmlspecialchars($keyword) ?>">

    <select name="category">
        <option value="">すべてのカテゴリ</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>" <?= $c === $category ? 'selected' : '' ?>>
                <?= htmlspecialchars($c) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="sort">
        <option value="new" <?= $sort === 'new' ? 'selected' : '' ?>>新着順</option>
        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>価格が安い順</option>
        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>価格が高い順</option>
    </select>

    <button type="submit">検索</button>
</form>


        <?php if (empty($items)): ?>
            <p class="empty-state">
                <?php if ($keyword !== '' || $category !== ''): ?>
                    条件に一致する商品が見つかりませんでした。検索条件を変えてみてください。
                <?php else: ?>
                    まだ商品が登録されていません。
                <?php endif; ?>
            </p>
        <?php else: ?>
            <div class="item-grid">
                <?php foreach ($items as $item): ?>
                    <a class="item-card" href="item.php?id=<?= (int) $item['id'] ?>" style="text-decoration:none; color:inherit;">
                        <img src="<?= $item['image'] ? 'uploads/items/' . htmlspecialchars($item['image']) : 'https://placehold.co/220x140' ?>" alt="">
                        <div class="name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="price">¥<?= number_format($item['price']) ?></div>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="pagination">
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
