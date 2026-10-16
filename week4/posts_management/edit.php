<?php
require_once 'db.php';

// idは整数のみ受け付ける（POST時はhiddenの値、GET時はURLの値）
$id = filter_var($_POST['id'] ?? $_GET['id'] ?? '', FILTER_VALIDATE_INT);
$errors = [];

if ($id === false) {
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();

// 編集対象の記事を取得
$stmt = $conn->prepare("SELECT id, title, content, author FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$post   = $result->fetch_assoc();
$stmt->close();

// 記事が存在しない場合は一覧に戻す
if (!$post) {
    $conn->close();
    header('Location: index.php');
    exit;
}

// フォームの初期値は既存データ
$title   = $post['title'];
$content = $post['content'];
$author  = $post['author'];

// フォームが送信されたとき（POSTリクエスト）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author  = trim($_POST['author'] ?? '');

    $errors = validatePost($title, $content, $author);

    if (count($errors) === 0) {
        $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, author = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $content, $author, $id);

        try {
            $stmt->execute();
            $stmt->close();
            $conn->close();
            // 更新後は詳細ページに移動する
            header('Location: show.php?id=' . $id);
            exit;
        } catch (mysqli_sql_exception $e) {
            $errors[] = '更新に失敗しました。';
            $stmt->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>記事編集</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>記事編集</h1>
    <a href="index.php">← 一覧に戻る</a>

    <?php if (count($errors) > 0): ?>
    <ul class="error">
        <?php foreach ($errors as $error): ?>
        <li><?php echo h($error); ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <!-- value には「POSTで送られた値」か「DBから取得した現在の値」を表示する -->
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo h($id); ?>">
        <label>タイトル:<br>
            <input type="text" name="title" maxlength="200" value="<?php echo h($title); ?>">
        </label><br>
        <label>著者:<br>
            <input type="text" name="author" maxlength="50" value="<?php echo h($author); ?>">
        </label><br>
        <label>本文:<br>
            <textarea name="content" rows="10"><?php echo h($content); ?></textarea>
        </label><br>
        <button type="submit">更新する</button>
    </form>
</body>
</html>
