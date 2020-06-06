<?php
//POSTチェック
//email変数に代入
//ばりでしょーんチェック
//・空値・最短値・最大値
//count(*)をemailを元に取得
//$stmt（実行）と$result(結果)がtureかチェック
//ランダム認証キー作成
//$from,$to,$subject,$comment(認証キー付き)をユーザーに送信(sendMail関数)
//$_SESSIONに認証キー、ユーザーemail、タイムリミットを入れる
//Recieve画面に遷移
require('function.php');
 
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード再発行送信　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();


if(!empty($_POST)) {
    debug('POSTされました');

    $email = $_POST['email'];

    validRequire($email, 'email');

    if(empty($err_msg)) {
        debug('未入力チェックOK');

        validEmail($email, 'email');
        validMaxLen($email, 'email');

        if(empty($err_msg)) {

            try{
                $dbh = dbConnect();

                $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
                $data = array(':email' => $email);

                $stmt = querypost($dbh, $sql, $data);

                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if($stmt && array_shift($result)) {
                    debug('クエリ成功');

                    //frashメッセージエリア

                    $auth_key = makeRandKey();

                    $from = 'ky19860120@gmail.com';
                    $to = $email;
                    $subject = 'パスワード再発行';
                    $comment = <<<EOT
パスワード再発行
{$auth_key}
EOT;

                    sendMail($from, $to, $subject, $comment);

                    $_SESSION['auth_key'] = $auth_key;
                    $_SESSION['auth_email'] = $email;
                    $_SESSION['auth_limit'] = time() + (60*30);

                    debug('リマインドメール送信成功');
                    // header("Location:passRemindRecieve.php");
                }
            } catch (Exeption $e) {
                error_log('エラーが発生しました：'. $e->getMessage());
                $err_msg['common'] = MSG07;
            }

        }

    }
}
?><!DOCTYPE html>
<html lang="ja">

<?php
$title = 'パスワード再発行メール送信';
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
    </header >
<!-- header -->
<main id="main">
    <div class="l-form modal modal--backgroundImg">
        <div class="container container--s">

            <form action="" method="POST" class="form mt100">
                <h2 class="form__title">パスワード再発行</h2>
                <p class="form__txt">ご指定のメールアドレス宛に再発行用のURLと認証キーをお送りします</p>
                <div class="area-msg">
                    <?php echo getErrMsg('common'); ?>
                </div>

                <label for="" class="label">
                    Email
                    <input type="text" name="email" class="input input--form" value="<?php echo sanitize( getFormData('email')); ?>">
                </label>

                <input type="submit" class="btn btn--form" value="送信する">
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