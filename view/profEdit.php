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
    $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'], 'pic') : '';

    try {

        $dbh = dbConnect();

        $sql ='UPDATE users SET first_name = :firstName, last_name = :lastName, email = :email, pic = :pic WHERE id = :u_id AND delete_flg = 0';
        $data = array(':firstName' => $firstName, ':lastName' => $lastName, ':email' => $email, ':pic' => $pic, ':u_id' => $_SESSION['user_id']);

        $stmt = querypost($dbh, $sql, $data);

        if(!$stmt) {
            debug('クエリ失敗');
            $err_msg['common'] = MSG07;
        }

        $_SESSION['msg_success'] = SUC05;

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
    <header class="l-header header header--bgColor" id="header">
        <h1><a href="./index.php" class="header__title">FEEL_SHARE</a></h1>

        <div class="menu-trigger js-toggle-sp-menu">
            <span class="menu-trigger__item"></span>
            <span class="menu-trigger__item"></span>
            <span class="menu-trigger__item"></span>
        </div>

        <nav class="nav-menu js-toggle-sp-menu-target">
            <ul class="nav-menu__menu">
                <li class="nav-menu__list-item"><a href="./index.php" class="nav-menu__list-link">ホーム</a></li>
                <li class="nav-menu__list-item"><a href="./logout.php" class="nav-menu__list-link">ログアウト</a></li>
                <li class="nav-menu__list-item"><a href="./myPage.php" class="nav-menu__list-link">マイページ</a></li>
                <li class="nav-menu__list-item"><a href="./contact.php" class="nav-menu__list-link">お問い合わせ</a></li>
                <li class="nav-menu__list-item"><a href="./imgUpload.php" class="nav-menu__list-link btn btn--header">アップロード</a></li>
            </ul>
        </nav>

    </header>
    <!-- header -->
    <main>
        <div class="l-form modal modal--backgroundImg">
            <div class="container container--s">
            
                <form action="" class="form mt100" method="POST" enctype="multipart/form-data">

                    <h1 class="form__title">プロフィール</h1>

                    <div class="form__avatar">
                        <div class="form__avatar-icon">
                            <img src="<?php echo '../dist/'.showImg(sanitize(getFormData('pic'))); ?>" alt="">
                        </div>
                        <label for="file" class="edit-label">
                            <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                            <input type="file" name="pic" id="file" class="input input-file">
                        </label>
                    </div>
                    <div class="msg-area">
                        <?php echo getErrMsg('pic'); ?>
                    </div>

                    <div class="msg-area">
                        <?php echo getErrMsg('common'); ?>
                    </div>

                    <label for="" class="label">FirstName</label>
                    <input type="text" class="input input--form" name="first_name" value="<?php echo sanitize(getFormData('first_name')); ?>">
                    <div class="msg-area">
                        <?php echo getErrMsg('first_name'); ?>
                    </div>

                    <label for="" class="label">lastName</label>
                    <input type="text" class="input input--form" name="last_name" value="<?php echo sanitize(getFormData('last_name')); ?>">
                    <div class="msg-area">
                        <?php echo getErrMsg('last_name'); ?>
                    </div>

                    <label for="" class="label">email</label>
                    <input type="text" class="input input--form" name="email" value="<?php echo sanitize(getFormData('email')); ?>">
                    <div class="msg-area">
                        <?php echo getErrMsg('email'); ?>
                    </div>
                    
                    <a class="btn btn--twitter">tiwtter</a>

                    <input type="submit" class="btn btn--form" value="送信">
                </form>

            </div>
        </div>
    </main>

    <footer id="footer" class="l-footer js-footer">
    Copryright&copy; U
    </footer>
<script src="../dist/js/bundle.js"></script>
</body>
</html>