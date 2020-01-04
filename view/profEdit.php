<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　プロフィール編集　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

$dbFormData = getUserData($_SESSION['user_id']);
debug('$dbFormData'. print_r($dbFormData,true));

if(!empty($_POST)) {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];

    try {

        $dbh = dbConnect();

        $sql ='UPDATE users SET first_name = :firstName, last_name = :lastName, email = :email WHERE id = :u_id AND delete_flg = 0';
        $data = array(':firstName' => $firstName, ':lastName' => $lastName, ':email' => $email, ':u_id' => $_SESSION['user_id']);

        $stmt = querypost($dbh, $sql, $data);

        if(!$stmt) {
            debug('クエリ失敗');
            $err_msg['common'] = MSG07;
        }

        debug('更新します');
        header("Location:profEdit.php");

    } catch (Exeption $e) {
        error_log('エラー発生：'. $e->getMessage());
        $err_msg['common'] = MSG07;
    }
}

?>

<!DOCTYPE html>
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
        <input type="text" class="input input--half" name="first_name" value="<?php echo getFormData('first_name'); ?>">
        <div class="msg-area">
            <?php echo getErrMsg('first_name'); ?>
        </div>
        <label for="" class="label">lastName</label>
        <input type="text" class="input--half" name="last_name" value="<?php echo getFormData('last_name'); ?>">
        <div class="msg-area">
            <?php echo getErrMsg('last_name'); ?>
        </div>

        <label for="" class="label">email</label>
        <input type="text" class="input" name="email" value="<?php echo getFormData('email'); ?>">
        <div class="msg-area">
            <?php echo getErrMsg('email'); ?>
        </div>
        
        <input type="submit" value="送信">
    </form>
</body>
</html>