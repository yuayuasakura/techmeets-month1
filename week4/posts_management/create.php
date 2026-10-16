<?php
require_once 'db.php';

$errors  = [];
$title   = '';
$content = '';
$author  = '';

// フォームが送信されたとき（POSTリクエスト）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author  = trim($_POST['author'] ?? '');

    $errors = validatePost($title, $content, $author);

    if (count($errors) === 0) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO posts (title, content, author) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $content, $author);

        // PHP 8.1以降のmysqliはSQLエラー時に例外を投げるので try/catch で受け取る
        try {
            $stmt->execute();
            $stmt->close();
            $conn->close();
            // 投稿成功したら一覧ページに移動する
            header('Location: index.php');
            exit;
        } catch (mysqli_sql_exception $e) {
            $errors[] = '投稿に失敗しました。';
            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新規投稿</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>新規投稿</h1>
    <a href="index.php">← 一覧に戻る</a>

    <?php if (count($errors) > 0): ?>
    <ul class="error">
        <?php foreach ($errors as $error): ?>
        <li><?php echo h($error); ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <form method="POST">
        <label>タイトル:<br>
            <input type="text" name="title" maxlength="200" value="<?php echo h($title); ?>">
        </label><br>
        <label>著者:<br>
            <input type="text" name="author" maxlength="50" value="<?php echo h($author); ?>">
        </label><br>
        <label>本文:<br>
            <textarea name="content" rows="10"><?php echo h($content); ?></textarea>
        </label><br>
        <button type="submit">投稿する</button>
    </form>
</body>
</html>
