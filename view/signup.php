<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ユーザー登録　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ランダム背景イメージ取得//
$randomImages = getRandomImage();
debug('$randomImages:'.print_r($randomImages,true));


if(!empty($_POST)) {
    debug('POSTされました。');

    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $pass_re = $_POST['password_re'];
    //validRequire：空チェック//
    validRequire($firstName, 'firstName');
    validRequire($lastName, 'lastName');
    validRequire($email, 'email');
    validRequire($pass, 'password');
    validRequire($pass_re, 'password_re');

    if(empty($err_msg)) {
        debug('エラー無し');
        /* validHalf:     半角英数字チェック
           validMaxLen:   最大文字数チェック
           validMinLen:   最小文字数チェック
           validEmail:    Email形式チェック
           validEmailDup: dbEmailチェック */
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
            //validMatch:同値チェック//
            validMatch($pass,$pass_re,'password_re');

            if(empty($err_msg)){
                try{
                    
                    $dbh = dbConnect();

                    $sql = 'INSERT INTO users (first_name, last_name, email, password, create_date)
                            VALUES (:first_name, :last_name, :email, :pass, :date)';

                    $data = array(':first_name' => $firstName, ':last_name' => $lastName, ':email' => $email, 
                                  ':pass' => password_hash($pass, PASSWORD_DEFAULT),
                                  ':date' => date('Y-m-d H:i:s'));

                    $stmt = querypost($dbh, $sql, $data);

                    if($stmt) {
                        //ユーザーログインタイムの初期値設定//
                        $sesLimit = 60 * 60;

                        $_SESSION['login_date'] = time();
                        $_SESSION['login_limit'] = $sesLimit;

                        $_SESSION['user_id'] = $dbh->lastInsertId();
                        $_SESSION['msg_success'] = SUC02;

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

<body>
    <header class="l-header header js-header" id="header">
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
                <li class="nav-menu__list-item">登録してるならコチラ ＞＞</li>
                <li class="nav-menu__list-item">
                    <a href="./login.php" class="nav-menu__list-link btn btn--header">ログイン</a>
                </li>
            </ul>
        </nav>
        
    </header> -->
    <!-- ヘッダー -->
    <main id="main">
        <section class="container container--l-img">
            <div class="image image--s">
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
                    <h1 class="form__title">sign up</h1>

                    <div class="msg-area">
                        <?php echo getErrMsg('common'); ?>
                    </div>

                    <div class="form__half">
                        <div class="form__half-area">
                            <label for="" class="label">FirstName</label>
                            <input type="text" class="input input--form" name="firstName" value="<?php echo sanitize(getFormData('firstName'));?>">
                            <div class="msg-area">
                                <?php echo getErrMsg('firstName'); ?>
                            </div>
                        </div>

                        <div class="form__half-area">
                            <label for="" class="label">lastName</label>
                            <input type="text" class="input input--form" name="lastName" value="<?php echo sanitize(getFormData('lastName'));?>">
                            <div class="msg-area">
                                <?php echo getErrMsg('lastName'); ?>
                            </div>
                        </div>
                    </div>
                        
                    <label for="" class="label">email</label>
                    <input type="text" class="input input--form" name="email" value="<?php echo sanitize(getFormData('email'));?>">
                    <div class="msg-area">
                        <?php echo getErrMsg('email'); ?>
                    </div>

                    <label for="" class="label">password</label>
                    <input type="text" class="input input--form" name="password" value="<?php echo sanitize(getFormData('password'));?>">
                    <div class="msg-area">
                        <?php echo getErrMsg('password'); ?>
                    </div>

                    <label for="" class="label">password_re</label>
                    <input type="text" class="input input--form" name="password_re" value="<?php echo sanitize(getFormData('password_re'));?>">
                    <div class="msg-area">
                        <?php echo getErrMsg('password_re'); ?>
                    </div>
                    
                    <input type="submit" value="送信" class="btn btn--form">
                </form>
            </div>
        </section>
        <!-- フォーム -->
    </main>
    

    <script src="../dist/js/bundle.js">
    </script>
</body>
</html>