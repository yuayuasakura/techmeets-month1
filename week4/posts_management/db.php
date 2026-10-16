<?php
// データベース接続情報
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'myapp_db');
define('DB_USER', 'root');
define('DB_PASS', 'root');

// データベース接続
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("接続失敗: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

// XSS対策：HTMLに出力する値をエスケープする
function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// 入力チェック：エラーメッセージの配列を返す（空ならエラーなし）
function validatePost($title, $content, $author) {
    $errors = [];

    if ($title === '') {
        $errors[] = 'タイトルを入力してください。';
    } elseif (mb_strlen($title) > 200) {
        $errors[] = 'タイトルは200文字以内で入力してください。';
    }

    if ($content === '') {
        $errors[] = '本文を入力してください。';
    }

    if ($author === '') {
        $errors[] = '著者名を入力してください。';
    } elseif (mb_strlen($author) > 50) {
        $errors[] = '著者名は50文字以内で入力してください。';
    }

    return $errors;
}
?>
