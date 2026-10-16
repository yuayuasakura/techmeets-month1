<?php
require_once 'db.php';
$conn = getDBConnection();

// 全記事を新しい順に取得（同時刻の場合はidの大きい方を先に）
$stmt = $conn->prepare("SELECT id, title, author, created_at FROM posts ORDER BY created_at DESC, id DESC");
$stmt->execute();
$result = $stmt->get_result();
$posts  = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>記事管理システム</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>記事一覧</h1>
    <a href="create.php">新規投稿</a>

    <?php if (count($posts) === 0): ?>
    <p>記事はまだありません。</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>タイトル</th>
                <th>著者</th>
                <th>投稿日</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $post): ?>
            <tr>
                <td><?php echo h($post['id']); ?></td>
                <td><a href="show.php?id=<?php echo h($post['id']); ?>"><?php echo h($post['title']); ?></a></td>
                <td><?php echo h($post['author']); ?></td>
                <td><?php echo h($post['created_at']); ?></td>
                <td>
                    <a href="show.php?id=<?php echo h($post['id']); ?>">詳細</a>
                    <a href="edit.php?id=<?php echo h($post['id']); ?>">編集</a>
                    <form method="POST" action="delete.php" class="inline-form"
                          onsubmit="return confirm('本当に削除しますか？');">
                        <input type="hidden" name="id" value="<?php echo h($post['id']); ?>">
                        <button type="submit">削除</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</body>
</html>
