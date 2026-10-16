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
function validateUser($username, $email, $age) {
    $errors = [];

    if ($username === '') {
        $errors[] = 'ユーザー名を入力してください。';
    }

    if ($email === '') {
        $errors[] = 'メールアドレスを入力してください。';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'メールアドレスの形式が正しくありません。';
    }

    if ($age === '') {
        $errors[] = '年齢を入力してください。';
    } elseif (filter_var($age, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 150]]) === false) {
        $errors[] = '年齢は0〜150の整数で入力してください。';
    }

    return $errors;
}
?>
