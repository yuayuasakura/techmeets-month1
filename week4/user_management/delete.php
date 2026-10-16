<?php
require_once 'db.php';

// 削除はPOSTのみ受け付ける（URLを開いただけで削除されないようにする）
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

// idが不正な場合は一覧に戻す
if ($id === false) {
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();
$conn->close();

// 削除後は一覧ページに移動
header('Location: index.php');
exit;
?>
