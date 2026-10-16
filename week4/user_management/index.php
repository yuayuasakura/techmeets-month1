<?php
require_once 'db.php';
$conn = getDBConnection();

// 全ユーザーを取得（プリペアドステートメントを使用）
$stmt = $conn->prepare("SELECT id, username, email, age, created_at FROM users ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$users  = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザー管理システム</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>ユーザー一覧</h1>
    <a href="create.php">新規ユーザー登録</a>

    <?php if (count($users) === 0): ?>
    <p>登録されているユーザーはいません。</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>ユーザー名</th>
                <th>メール</th>
                <th>年齢</th>
                <th>登録日</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $row): ?>
            <tr>
                <td><?php echo h($row['id']); ?></td>
                <td><?php echo h($row['username']); ?></td>
                <td><?php echo h($row['email']); ?></td>
                <td><?php echo h($row['age']); ?></td>
                <td><?php echo h($row['created_at']); ?></td>
                <td>
                    <a href="edit.php?id=<?php echo h($row['id']); ?>">編集</a>
                    <!-- 削除はPOSTで送信し、送信前に確認ダイアログを表示する -->
                    <!-- ユーザー名はJSに直接埋め込まず data属性 経由で渡す（JS内でのXSSを防ぐため） -->
                    <form method="POST" action="delete.php" class="inline-form"
                          data-username="<?php echo h($row['username']); ?>"
                          onsubmit="return confirm(this.dataset.username + ' さんを本当に削除しますか？');">
                        <input type="hidden" name="id" value="<?php echo h($row['id']); ?>">
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
