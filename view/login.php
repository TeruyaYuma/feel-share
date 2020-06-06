<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ログイン　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ランダム背景イメージ取得//
$randomImages = getRandomImage();
debug('$randomImages:'.print_r($randomImages,true));

if(!empty($_POST)) {
    debug('POSTされました。');

    $email = $_POST['email'];
    $pass = $_POST['password'];
    $pass_save = !empty($_POST['pass_save']) ? $_POST['pass_save'] : '';
    //validRequire: 空値チェック//
    validRequire($email, 'email');
    validRequire($pass, 'password');

    if(empty($err_msg)) {
        debug('エラー無し');
        /* validMinLen: 最大文字数チェック
           validMaxLen: 最小文字数チェック
           validHalf:   半角英数字チェック
           validEmail:  dbEmailチェック */
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
                //ポストされたパスワードと取得したDBのパスワードが合致すればログインタイム設定して繊維//
                if(!empty($result)){
                    
                    if(!empty($stmt) && password_verify($pass, array_shift($result))) {
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
                        $_SESSION['msg_success'] = SUC01;

                        debug('セッションの中身'. print_r($_SESSION, true));
                        header("Location:index.php");

                    } else {
                        debug('パスワードアンマッチ');

                        $err_msg['common'] = MSG09;
                    }

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

<body>
    <header class="l-header header" id="header">
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
                    <li class="nav-menu__list-item"><a href="./signup.php" class="nav-menu__list-link">サインアップ</a></li>
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
    <!-- <header class="l-header header" id="header">
        <h1><a href="./index.php" class="header__title">FEEL_SHARE</a></h1>

        <nav class="nav-menu">
            <ul class="nav-menu__menu">
                <li class="nav-menu__list-item">新規登録はこちら ＞＞</li>
                <li class="nav-menu__list-item">
                    <a href="./signup.php" class="nav-menu__list-link btn btn--header">サインアップ</a>
                </li>
            </ul>
        </nav>

    </header> -->
    <!-- ヘッダー -->
    <main id="main"> 
        <section class="container container--l-img">
            <div class="image image--s mt">
                <?php
                    foreach($randomImages as $val){
                ?>
                    <div class="image__item-s image__item-s--high">
                        <img src="<?php echo sanitize('../dist/'.$val['name']); ?>" alt="">
                    </div>
                <?php
                    }
                ?>
            </div>
        </section>
        <!-- ランダムイメージ背景 -->
        <section class="l-form modal modal--backgroundImg">
            <div class="container container--s">
                <form action="" class="form mt220" method="POST">

                    <h1 class="form__title">login</h1>

                    <div class="msg-area">
                        <?php echo getErrMsg('common'); ?>
                    </div>

                    <label for="" class="label">email</label>
                    <input type="text" class="input input--form" name="email" value="<?php echo sanitize(getFormData('email')); ?>">
                    <div class="msg-area">
                        <?php echo getErrMsg('email'); ?>
                    </div>

                    <label for="" class="label">password</label>
                    <input type="text" class="input input--form" name="password" value="<?php echo sanitize(getFormData('password')); ?>">
                    <div class="msg-area">
                        <?php echo getErrMsg('password'); ?>
                    </div>

                    <label for="" class="label">ログイン保持</label>
                    <input type="checkbox" class="input" name="pass_save">
                    
                    <input type="submit" value="送信" class="btn btn--form">

                    <a href="./passRemindSend.php" class="form__link mt20">パスワードをお忘れですか？</a>
                </form>
            </div>
        </section>
        <!-- フォーム -->
    </main>
    

    <script src="../dist/js/bundle.js">
    </script>
</body>
</html>