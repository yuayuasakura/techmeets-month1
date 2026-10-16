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

// 編集対象のユーザーを取得
$stmt = $conn->prepare("SELECT id, username, email, age FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();

// ユーザーが存在しない場合は一覧に戻す
if (!$user) {
    $conn->close();
    header('Location: index.php');
    exit;
}

// フォームの初期値は既存データ
$username = $user['username'];
$email    = $user['email'];
$age      = (string)$user['age'];

// フォームが送信されたとき（POSTリクエスト）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $age      = trim($_POST['age'] ?? '');

    $errors = validateUser($username, $email, $age);

    if (count($errors) === 0) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, age = ? WHERE id = ?");
        $ageInt = (int)$age;
        $stmt->bind_param("ssii", $username, $email, $ageInt, $id);

        // PHP 8.1以降のmysqliはSQLエラー時に例外を投げるので try/catch で受け取る
        try {
            $stmt->execute();
            $stmt->close();
            $conn->close();
            header('Location: index.php');
            exit;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                // 1062 = 重複エラー（他のユーザーが同じemailを使っている）
                $errors[] = 'このメールアドレスは既に他のユーザーが使用しています。';
            } else {
                $errors[] = '更新に失敗しました。';
            }
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
    <title>ユーザー編集</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>ユーザー編集</h1>
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
        <label>ユーザー名:
            <input type="text" name="username" value="<?php echo h($username); ?>">
        </label><br>
        <label>メール:
            <input type="email" name="email" value="<?php echo h($email); ?>">
        </label><br>
        <label>年齢:
            <input type="number" name="age" min="0" max="150" value="<?php echo h($age); ?>">
        </label><br>
        <button type="submit">更新する</button>
    </form>
</body>
</html>
