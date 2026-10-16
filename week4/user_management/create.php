<?php
require_once 'db.php';

$errors   = [];
$username = '';
$email    = '';
$age      = '';

// フォームが送信されたとき（POSTリクエスト）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $age      = trim($_POST['age'] ?? '');

    $errors = validateUser($username, $email, $age);

    if (count($errors) === 0) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO users (username, email, age) VALUES (?, ?, ?)");
        $ageInt = (int)$age;
        $stmt->bind_param("ssi", $username, $email, $ageInt);

        // PHP 8.1以降のmysqliはSQLエラー時に例外を投げるので try/catch で受け取る
        try {
            $stmt->execute();
            $stmt->close();
            $conn->close();
            // 登録成功したら一覧ページに移動する
            header('Location: index.php');
            exit;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                // 1062 = 重複エラー（emailにUNIQUE制約がある）
                $errors[] = 'このメールアドレスは既に登録されています。';
            } else {
                $errors[] = '登録に失敗しました。';
            }
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
    <title>ユーザー登録</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>新規ユーザー登録</h1>
    <a href="index.php">← 一覧に戻る</a>

    <?php if (count($errors) > 0): ?>
    <ul class="error">
        <?php foreach ($errors as $error): ?>
        <li><?php echo h($error); ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <form method="POST">
        <label>ユーザー名:
            <input type="text" name="username" value="<?php echo h($username); ?>">
        </label><br>
        <label>メール:
            <input type="email" name="email" value="<?php echo h($email); ?>">
        </label><br>
        <label>年齢:
            <input type="number" name="age" min="0" max="150" value="<?php echo h($age); ?>">
        </label><br>
        <button type="submit">登録する</button>
    </form>
</body>
</html>
