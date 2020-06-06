<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　プロフィール　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

$u_id = (!empty($_GET['u_id']))? $_GET['u_id'] : '';
debug('$u_id:'.$u_id);

//$_GETパラメーター改ざん防止
if( !empty($u_id) && (int)$u_id <= 0 ) {
    error_log('エラー発生：指定ページに不正な値が入りました。');
    header("Location:index.php");
    return;
}

//ユーザー情報
$formData = getUserData($u_id);
$userName = $formData['last_name']. " " .$formData['first_name'];
//images全権取得
$images = getUserImages($u_id);
debug('$images:'.print_r($images,true));

//boardsテーブル作成
if(!empty($_POST['submit'])){
    debug('POSTされました');

    if(isLogin() === false){
        header("Location:index.php");
        exit();
    }

    try{

    $dbh = dbConnect();

    $sql = 'SELECT id FROM boards WHERE to_user = :m_id AND from_user = :u_id AND delete_flg = 0
                                     OR to_user = :u_id AND from_user = :m_id AND delete_flg = 0';
    $data = array(':m_id' => $_SESSION['user_id'], ':u_id' => $formData['id']);

    $stmt = querypost($dbh, $sql, $data);
    $rst = $stmt->fetch(PDO::FETCH_ASSOC);

    if($rst){
        debug('boardsが見つかりました、そのまま繊維します');
        header("Location:msg.php?b_id=".$rst['id']);

    } else {        
        debug('新規作成して遷移します');
    
        $sql = 'INSERT INTO boards (to_user, from_user, create_date) VALUES (:to_uid, :from_uid, :date)';
        $data = array(':to_uid' => $formData['id'], ':from_uid' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));

        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
            debug('メッセージ画面へ遷移');
            header("Location:msg.php?b_id=".$dbh->lastInsertId());
        }
    }

    } catch (Exeption $e) {
        error_log('エラーが発生しました。'. $e->getMessage());
        $err_msg['common'] = MSG07;
    }
}

debug('画面表示終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<!DOCTYPE html>
<html lang="ja">

<?php
$title = 'プロフィール';
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
                    <li class="nav-menu__list-item"><a href=".signup.php" class="nav-menu__list-link">登録</a></li>
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
        <section class="container container--s">
                <div class="profile mt100">

                    <div class="profile__avatar">
                        <img src="<?php echo '../dist/'.showImg(sanitize( $formData['pic']) ); ?>" alt="">
                    </div>

                    <div class="profile__detail">

                        <h2 class="profile__name"><?php echo sanitize($userName); ?></h2>

                        <div class="profile__social">
                        <i class="fab fa-twitter-square profile__social-icon" aria-hidden="true"></i><span>U@WEB</span>
                        </div>

                        <?php if( !empty($_SESSION['user_id']) && $_SESSION['user_id'] === $u_id ){ ?>

                            
                            <a href="./myPage.php" class="btn btn--link">マイページ</a>
                            

                        <?php } else { ?>

                            <form action="" method="POST">
                                <input type="submit" value="メッセージ" name="submit" class="btn btn--link">
                            </form>

                        <?php } ?>
                        
                    </div>

                </div>
        </section>
        
        <section class="image-container mt100">
                <div class="image-container__head">
                    <h2 class="image-container__title">投稿画像</h2>
                    <span class="image-container__total">投稿数: <?php echo count($images); ?> 件</span>
                </div>
                
                <div class="image image--m">
                    <?php
                        if(!empty($images)){
                            foreach($images as $val){
                    ?>
                    <div class="image__item-m">
                        <img src="../dist/<?php echo showImg( sanitize($val['name']) ); ?>" alt="">
                    </div>
                    <?php
                            }
                        }
                    ?>
                </div>
        </section>
    </main>

    <footer id="footer" class="l-footer js-footer">
        Copryright&copy; U
    </footer>

<script src="../dist/js/bundle.js"></script>
</body>
</html>