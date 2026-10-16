<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>コメント投稿</title>
</head>
<body>

<h1>コメント投稿フォーム</h1>

<form method="POST">
  <label>名前:</label>
  <input type="text" name="name"><br>
  <label>コメント:</label>
  <textarea name="comment"></textarea><br>
  <button type="submit">投稿する</button>
</form>

<?php
// XSS対策: HTMLとして出力する値は必ずエスケープする
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 未送信でもエラーにならないようにし、前後の空白（全角スペース含む）を取り除く
    $name    = preg_replace('/\A[\s　]+|[\s　]+\z/u', '', $_POST["name"] ?? "");
    $comment = preg_replace('/\A[\s　]+|[\s　]+\z/u', '', $_POST["comment"] ?? "");

    $errors = [];
    if ($name === "") {
        $errors[] = "名前を入力してください。";
    }
    if ($comment === "") {
        $errors[] = "コメントを入力してください。";
    }

    if (count($errors) > 0) {
        foreach ($errors as $error) {
            echo "<p>" . h($error) . "</p>";
        }
    } else {
        echo "<p>" . h($name) . "さんのコメント:</p>";
        echo "<p>" . nl2br(h($comment)) . "</p>";
    }
}
?>

</body>
</html>
