<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ユーザー登録　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if(!empty($_POST)) {
    debug('POSTされました。');

    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $pass_re = $_POST['password_re'];

    validRequire($firstName, 'firstName');
    validRequire($lastName, 'lastName');
    validRequire($email, 'email');
    validRequire($pass, 'password');
    validRequire($pass_re, 'password_re');

    if(empty($err_msg)) {
        debug('エラー無し');

        validHalf($firstName, 'firstName');
        validMaxLen($firstName, 'firstName');

        validHalf($lastName, 'lastName');
        validMaxLen($lastName, 'lastName');

        validEmail($email, 'email');
        validMinLen($email, 'email');
        validMaxLen($email, 'email');
        validEmailDup($email);

        validMinLen($pass, 'password');
        validMaxLen($pass, 'password');
        validHalf($pass, 'password');
              
        validMinLen($pass_re, 'password_re');
        validMaxLen($pass_re, 'password_re');

        if(empty($err_msg)) {
            debug('エラー無し2');
            validMatch($pass,$pass_re,'password_re');

            if(empty($err_msg)){
                try{
                    
                    $dbh = dbConnect();

                    $sql = 'INSERT INTO users (first_name, last_name, email, password, create_date) VALUES (:first_name, :last_name, :email, :pass, :date)';
                    $data = array(':first_name' => $firstName, ':last_name' => $lastName, ':email' => $email, 
                                  ':pass' => password_hash($pass, PASSWORD_DEFAULT),
                                  ':date' => date('Y-m-d H:i:s'));

                    $stmt = querypost($dbh, $sql, $data);

                    if($stmt) {
                        $sesLimit = 60 * 60;

                        $_SESSION['login_date'] = time();
                        $_SESSION['login_limit'] = $sesLimit;

                        $_SESSION['user_id'] = $dbh->lastInsertId();

                        debug('セッション変数の中身：'.print_r($_SESSION,true));

                        header('Location:index.php');
                    }

                } catch (Exeption $e) {
                    error_log('エラー発生'. $e->getMessage());
                }
            }
        }
    }
}
 ?>

<!DOCTYPE html>
<html lang="ja">

<?php
    $title = '新規登録';
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
        <label for="" class="label">FirstName</label>
        <input type="text" class="input input--half" name="firstName">
        <div class="msg-area">
            <?php echo getErrMsg('firstName'); ?>
        </div>
        <label for="" class="label">lastName</label>
        <input type="text" class="input--half" name="lastName">
        <div class="msg-area">
            <?php echo getErrMsg('lastName'); ?>
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

        <label for="" class="label">password_re</label>
        <input type="text" class="input" name="password_re">
        <div class="msg-area">
            <?php echo getErrMsg('password_re'); ?>
        </div>
        
        <input type="submit" value="送信">
    </form>
</main>
    

    <script src="dist/js/bundle.js">
    </script>
</body>
</html>