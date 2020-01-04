<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　退会　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

if(!empty($_POST)) {
    debug('POSTされました');

    if(empty($_SESSION['user_id'])) {
       debug('未ログインユーザーです');

       header("Location:login.php");
    }

    $user_id = $_SESSION['user_id'];
    debug('user_id：'. print_r($_SESSION['user_id'], true));

    try{

        $dbh = dbConnect();

        $sql ='UPDATE users SET delete_flg = 1 WHERE id = :user_id';
        $data = array(':user_id' => $user_id);

        $stmt = querypost($dbh, $sql, $data);

        if($stmt) {
            
            session_destroy();

            debug('セッションの中身：'. print_r($_SESSION, true));
            debug('トップページ遷移します');
            header("Location:index.php");
        } else {
            debug('クエリ失敗');

            $err_msg['common'] = MSG07;
        }

    } catch (Exception $e) {
        error_log('エラー発生：'.$e->getMessage());
        $err_msg['common'] = MSG07;
    }
}
?>


<!DOCTYPE html>
<html lang="ja">

<?php
$title = '退会';
require('head.php');
?>

<body>
<header></header>

<main>
    <form action="" class="form" method="POST">
        <h2 class="title">退会</h2>
        <div class="msg-area">
            <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
        </div>
        <input type="submit" class="input" value="退会" name="submit">
    </form>
</main>
</body>
</html>