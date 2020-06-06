<?php
//パスワード再発行認証メール画面で作られた認証キー（＄_SESSION['auth_key']）があるか確認なければメール画面に遷移
//POSTチェック
//バリデーションチェック（空値、固定長、半角、発行キーと入力キー同値、リミットチェック）
//パスワード生成（独自ランダム関数）
//UPDATE password
//画面遷移
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード再発行認証　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// if(empty($_SESSION['auth_key'])) {
//     header("Location:passRemindSend.php");
// }

if(!empty($_POST)) {

    $auth_key = $_POST['token'];

    validRequire($auth_key, 'token');

    if(empty($err_msg)) {

        //固定長
        validHalf($auth_key, 'token');

        if(empty($err_msg)) {
            if($auth_key !== $_SESSION['auth_key']) {
                //エラーメッセージ
            }
            if(time() > $_SESSION['auth_limit']) {
                //エラーメッセージ
            }

            if(empty($err_msg)) {

                $pass = makeRandKey();

                $dbh = dbConnecr();

                $sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flg = 0';
                $data = array(':email' => $_SESSION['auth_email'], ':pass' => password_hash($pass,PASSWORD_DEFAULT));

                $stmt = querypost($dbh, $sql, $data);

                if($stmt) {
                    debug('クエリ成功');

                    $from = 'example@gmail.com';
                    $to = $_SESSION['auth_email'];
                    $subject = '';
                    $comment = <<<EOT
新しいパスワード
{$pass}
EOT;

                    session_unset();
                    //サクセスメッセージ
                    header("Location:login.php");
                    return;
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ja">

<?php
$title = 'パスワード再発行認証';
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
                <?php
                    if(empty($_SESSION['user_id'])){
                ?>
                    <li class="nav-menu__list-item"><a href="./singup.php" class="nav-menu__list-link">登録</a></li>
                    <li class="nav-menu__list-item"><a href="./login.php" class="nav-menu__list-link">ログイン</a></li>
                    <li class="nav-menu__list-item"><a href="./contact.php" class="nav-menu__list-link">お問い合わせ</a></li>
                    <li class="nav-menu__list-item"><a href="./imgUpload.php" class="nav-menu__list-link btn btn--header">アップロード</a></li>
                <?php
                    } else {
                ?>
                    <li class="nav-menu__list-item"><a href="./logout.php" class="nav-menu__list-link">ログアウト</a></li>
                    <li class="nav-menu__list-item"><a href="./myPage.php" class="nav-menu__list-link">マイページ</a></li>
                    <li class="nav-menu__list-item"><a href="./contact.php" class="nav-menu__list-link">お問い合わせ</a></li>
                    <li class="nav-menu__list-item"><a href="./imgUpload.php" class="nav-menu__list-link btn btn--header">アップロード</a></li>
                <?php
                    }
                ?>
            </ul>
        </nav>
    </header>
<!-- header -->
<main id="main">
    <div class="l-form modal modal--backgroundImg">
        <div class="container container--s">

            <form action="" method="POST" class="form mt100">
                <h2 class="form__title">パスワード再発行</h2>
                <p class="form__txt">認証キーを入力してください</p>

                <div class="are-msg">
                <?php echo getErrMsg('common'); ?> 
                </div>

                <input type="text" name="token" class="input input--form" value="<?php echo sanitize( getFormData('token') ); ?>">
                <div class="area-msg">
                    <?php echo getErrMsg('token'); ?>
                </div>

                <input type="submit" class="btn btn--form" value="送信">
                <a href="passRemindSend.php">&lt;パスワード再発行する</a>
            </form>
            <!-- form -->
        </div>
    </div>
</main>
<!-- main -->
<footer id="footer" class="l-footer js-footer">
    Copryright&copy; U
</footer>
<!-- footer -->
<script src="../dist/js/bundle.js"></script>
</body>
</html>