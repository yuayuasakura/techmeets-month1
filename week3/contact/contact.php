<?php
// XSS対策：出力時に必ずエスケープする
function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// 入力項目の定義（表示名・最大文字数）
const FIELDS = [
    'name'    => ['label' => 'お名前',         'max' => 50],
    'email'   => ['label' => 'メールアドレス', 'max' => 255],
    'subject' => ['label' => '件名',           'max' => 100],
    'message' => ['label' => 'メッセージ',     'max' => 1000],
];

// POSTされた値を取得し、前後の空白を除去する
function getInput($key)
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value)) {
        return '';
    }
    // 改行コードを統一（ブラウザは改行をCRLFで送るため、文字数がずれないようにする）
    $value = str_replace(["\r\n", "\r"], "\n", $value);
    return trim($value);
}

// 必須チェック
function validateRequired($value, $label)
{
    if ($value === '') {
        return "{$label}を入力してください";
    }
    return null;
}

// 文字数チェック（日本語対応のためmb_strlenを使用）
function validateMaxLength($value, $label, $max)
{
    if (mb_strlen($value, 'UTF-8') > $max) {
        return "{$label}は{$max}文字以内で入力してください";
    }
    return null;
}

// メールアドレスの形式チェック
function validateEmail($value)
{
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        return 'メールアドレスの形式が正しくありません';
    }
    return null;
}

// 全項目をチェックし、項目ごとのエラーメッセージを返す（1項目につき最初のエラーのみ）
function validateContact($input)
{
    $errors = [];
    foreach (FIELDS as $key => $field) {
        $value = $input[$key];
        $error = validateRequired($value, $field['label'])
            ?? validateMaxLength($value, $field['label'], $field['max']);

        if ($error === null && $key === 'email') {
            $error = validateEmail($value);
        }
        if ($error !== null) {
            $errors[$key] = $error;
        }
    }
    return $errors;
}

$input = array_fill_keys(array_keys(FIELDS), '');
$errors = [];
$isConfirm = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (FIELDS as $key => $field) {
        $input[$key] = getInput($key);
    }

    // 確認画面の「戻って修正する」の場合は、検証せずに入力値をフォームに戻す
    if (($_POST['action'] ?? '') !== 'back') {
        $errors = validateContact($input);
        // エラーがなければ確認画面を表示
        $isConfirm = empty($errors);
    }
}

// エラーのある入力欄にクラスを付ける
function errorClass($key, $errors)
{
    return isset($errors[$key]) ? ' class="has-error"' : '';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お問い合わせフォーム</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 0 16px; }
        label { display: block; margin-top: 16px; font-weight: bold; }
        input[type="text"], input[type="email"], textarea { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; }
        textarea { height: 150px; }
        .has-error { border: 2px solid red !important; background: #fff5f5; }
        .error { color: red; font-size: 0.9em; margin: 4px 0 0; }
        .buttons { margin-top: 24px; }
        button { padding: 8px 24px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; vertical-align: top; }
        th { width: 30%; background: #f5f5f5; }
    </style>
</head>
<body>

<?php if ($isConfirm): ?>
    <h1>入力内容の確認</h1>
    <table>
        <tr><th>お名前</th><td><?php echo h($input['name']); ?></td></tr>
        <tr><th>メールアドレス</th><td><?php echo h($input['email']); ?></td></tr>
        <tr><th>件名</th><td><?php echo h($input['subject']); ?></td></tr>
        <tr><th>メッセージ</th><td><?php echo nl2br(h($input['message'])); ?></td></tr>
    </table>

    <!-- 修正用：入力値をhiddenで送ってフォームに戻る -->
    <form action="contact.php" method="post" class="buttons">
        <?php foreach ($input as $key => $value): ?>
            <input type="hidden" name="<?php echo h($key); ?>" value="<?php echo h($value); ?>">
        <?php endforeach; ?>
        <button type="submit" name="action" value="back">戻って修正する</button>
    </form>

<?php else: ?>
    <h1>お問い合わせフォーム</h1>
    <form action="contact.php" method="post" novalidate>
        <label for="name">お名前</label>
        <input type="text" id="name" name="name" maxlength="<?php echo FIELDS['name']['max']; ?>"
               value="<?php echo h($input['name']); ?>"<?php echo errorClass('name', $errors); ?>>
        <?php if (isset($errors['name'])): ?>
            <p class="error"><?php echo h($errors['name']); ?></p>
        <?php endif; ?>

        <label for="email">メールアドレス</label>
        <input type="email" id="email" name="email" maxlength="<?php echo FIELDS['email']['max']; ?>"
               value="<?php echo h($input['email']); ?>"<?php echo errorClass('email', $errors); ?>>
        <?php if (isset($errors['email'])): ?>
            <p class="error"><?php echo h($errors['email']); ?></p>
        <?php endif; ?>

        <label for="subject">件名</label>
        <input type="text" id="subject" name="subject" maxlength="<?php echo FIELDS['subject']['max']; ?>"
               value="<?php echo h($input['subject']); ?>"<?php echo errorClass('subject', $errors); ?>>
        <?php if (isset($errors['subject'])): ?>
            <p class="error"><?php echo h($errors['subject']); ?></p>
        <?php endif; ?>

        <label for="message">メッセージ</label>
        <textarea id="message" name="message" maxlength="<?php echo FIELDS['message']['max']; ?>"<?php echo errorClass('message', $errors); ?>><?php echo h($input['message']); ?></textarea>
        <?php if (isset($errors['message'])): ?>
            <p class="error"><?php echo h($errors['message']); ?></p>
        <?php endif; ?>

        <div class="buttons">
            <button type="submit">確認する</button>
        </div>
    </form>
<?php endif; ?>

</body>
</html>
