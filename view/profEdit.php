<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　プロフィール編集　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

$dbFormData = getUserData($_SESSION['user_id']);

?><!DOCTYPE html>
<html lang="ja">

<?php
$title = 'プロフィール編集';
require('head.php');
?>

<body>
<form action="" class="form" method="POST">
        <div class="msg-area">
            <?php echo getErrMsg('common'); ?>
        </div>
        <label for="" class="label">FirstName</label>
        <input type="text" class="input input--half" value="<?php echo getFormData('first_name') ?>" name="first_name">
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
</body>
</html>