<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ログイン　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

if(!empty($_POST)) {
    debug('POSTされました。');

    $email = $_POST['email'];
    $pass = $_POST['password'];
    $pass_save = !empty($_POST['pass_save']) ? $_POST['pass_save'] : '';

    validRequire($email, 'email');
    validRequire($pass, 'password');

    if(empty($err_msg)) {
        debug('エラー無し');

        validEmail($email, 'email');
        validMinLen($email, 'email');
        validMaxLen($email, 'email');

        validMinLen($pass, 'password');
        validMaxLen($pass, 'password');
        validHalf($pass, 'password');

        if(empty($err_msg)){
            try{
                    
                $dbh = dbConnect();

                $sql = 'SELECT password,id FROM users WHERE email = :email AND delete_flg = 0';
                $data = array(':email' => $email);

                $stmt = querypost($dbh, $sql, $data);

                $result = $stmt->fetch(PDO::FETCH_ASSOC);
               
                if(!empty($stmt) && $pass = password_verify($pass, array_shift($result))) {
                    debug('パスワードマッチ');

                    $seslimit = 60 * 60;

                    $_SESSION['login_date'] = time();

                    if($pass_save) {
                        debug('ログイン保持がチェック有り');

                        $_SESSION['login_limit'] = $seslimit * 24 * 30;

                    } else {
                        debug('ログイン保持チェック無し');

                        $_SESSION['login_limit'] = $seslimit;
                    }

                    $_SESSION['user_id'] = $result['id'];

                    debug('セッションの中身'. print_r($_SESSION, true));
                    header("Location:index.php");

                } else {
                    debug('パスワードアンマッチ');

                    $err_msg['common'] = MSG09;
                }

            } catch (Exeption $e) {
                error_log('エラー発生'. $e->getMessage());
            }
        }
    }
}
 ?>

<!DOCTYPE html>
<html lang="ja">

<?php
    $title = 'ログイン';
    require('head.php');
?>

<style>
    .form {
        width: 50%;
    }
    .input {
        margin-bottom: 20px;
        width : 100%;
    }
    .input--half {
        display: inline-block;
        width: 40%;
    }
    .label {
        display: block;
    }
    .label--half {
        margin-right: 20px;
        display: inline-block;
    }

</style>
<body>
<header></header>
<main>
    <form action="" class="form" method="POST">
        <div class="msg-area">
            <?php echo getErrMsg('common'); ?>
        </div>

        <label for="" class="label">email</label>
        <input type="text" class="input" name="email">
        <div class="msg-area">
            <?php echo getErrMsg('email'); ?>
        </div>

        <label for="" class="label">password</label>
        <input type="text" class="input" name="password">
        <div class="msg-area">
            <?php echo getErrMsg('password'); ?>
        </div>

        <label for="" class="label">ログイン保持</label>
        <input type="checkbox" class="input" name="pass_save">
        
        <input type="submit" value="送信">
    </form>
</main>
    

    <script src="dist/js/bundle.js">
    </script>
</body>
</html>