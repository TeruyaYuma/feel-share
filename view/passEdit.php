<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード変更　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

$userData = getUserData($_SESSION['user_id']);
debug('ユーザー情報'. print_r($userData,true));

if(!empty($_POST)) {
    debug('POSTされました');

    $pass_old = $_POST['pass_old'];
    $pass_new = $_POST['pass_new'];
    $pass_new_re = $_POST['pass_new_re'];

    validRequire($pass_old, 'pass_old');
    validRequire($pass_new, 'pass_new');
    validRequire($pass_new_re, 'pass_new_re');

    if(empty($err_msg)) {
        debug('未入力チェックOK');

        validHalf($pass_old,'pass_old');
        validMinLen($pass_old,'pass_old');

        validHalf($pass_new,'pass_new');
        validMinLen($pass_new,'pass_new');

        validHalf($pass_new_re,'pass_new_re');
        validMinLen($pass_new_re,'pass_new_re');

        if(!password_verify($pass_old,$userData['password'])) {
            $err_msg = MSG10;
        }

        if($pass_old === $pass_new) {
            $err_msg = MSG11;
        }

        validMatch($pass_new, $pass_new_re, 'pass_new');

        if(empty($err_msg)) {
            debug('エラー無し');

            try {

                $dbh = dbConnect();

                $sql = 'UPDATE users SET password = :pass WHERE id = :u_id';
                $data = array(':u_id' => $userData['id'], ':pass' => password_hash($pass_new, PASSWORD_DEFAULT));

                $stmt = querypost($dbh, $sql, $data);
                
                if($stmt) {
                    $userName = (!empty($userData['last_name'])? $userData['last_name'] : '');
                    $from = 'test@icloud.com';
                    $to = $userData['email'];
                    $subject = 'メール変更通知';
                    $comment = <<<EOT
{$userName} さん
パスワードが変更されました。
EOT;
                    
                    sendMail($from, $to, $subject, $comment);

                    header("Location:index.php");
                }

            } catch (Exeption $e) {
                error_log('エラー発生'. $e->getMessage());
                $err_msg['common'] = MSG07;
            }

        }

    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<?php
$title = 'パスワード変更';
require('head.php');
?>

<body>
<head></head>
<main>
    <form action="" method="POST" class="form">
        <div class="msg-area">
            <?php echo getErrMsg('common'); ?>
        </div>
        <label for="" class="label">
        古いパスワード
        <input type="text" style="display:block;" name="pass_old" class="input">
        </label>
        <div class="msg-area">
            <?php echo getErrMsg('pass_old'); ?>
        </div>
        <label for="" class="label">
        新しいパスワード
        <input type="text" style="display:block;" name="pass_new" class="input">
        </label>
        <div class="msg-area">
            <?php echo getErrMsg('pass_new'); ?>
        </div>
        <label for="" class="label">
        新しいパスワード（再入力）
        <input type="text" style="display:block;" name="pass_new_re" class="input">
        </label>
        <div class="msg-area">
            <?php echo getErrMsg('pass_new_re'); ?>
        </div>
        <input type="submit" class="">
    </form>
</main>
</body>
</html>