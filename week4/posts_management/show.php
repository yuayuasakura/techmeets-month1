<?php
require_once 'db.php';

// idは整数のみ受け付ける
$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

if ($id === false) {
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id, title, content, author, created_at, updated_at FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$post   = $result->fetch_assoc();
$stmt->close();
$conn->close();

// 記事が存在しない場合は一覧に戻す
if (!$post) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?php echo h($post['title']); ?> - 記事詳細</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <a href="index.php">← 一覧に戻る</a>

    <h1><?php echo h($post['title']); ?></h1>
    <p class="meta">
        著者: <?php echo h($post['author']); ?> ／
        投稿日: <?php echo h($post['created_at']); ?> ／
        更新日: <?php echo h($post['updated_at']); ?>
    </p>

    <!-- エスケープした後に改行を<br>に変換する -->
    <div class="content"><?php echo nl2br(h($post['content'])); ?></div>

    <div>
        <a href="edit.php?id=<?php echo h($post['id']); ?>">編集</a>
        <form method="POST" action="delete.php" class="inline-form"
              onsubmit="return confirm('本当に削除しますか？');">
            <input type="hidden" name="id" value="<?php echo h($post['id']); ?>">
            <button type="submit">削除</button>
        </form>
    </div>
</body>
</html>
