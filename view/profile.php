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

<style>

.mt200{
    margin-top: 200px;
}
.mt100{
    margin-top: 100px;
}
</style>
<body>
    <header class="l-header header header--fix isHeaderColor" id="header">
        <h1><a href="./index.php" class="header__title">FEEL_SHARE</a></h1>

        <nav class="nav-menu">
            <ul class="nav-menu__menu">
                <li class="nav-menu__list-item"><a href="./index.php">ホーム</a></li>
                <?php
                    if(empty($_SESSION['user_id'])){
                ?>
                    <li class="nav-menu__list-item"><a href="" class="nav-menu__list-link">登録</a></li>
                    <li class="nav-menu__list-item"><a href="" class="nav-menu__list-link">ログイン</a></li>
                <?php
                    } else {
                ?>
                    <li class="nav-menu__list-item"><a href="" class="nav-menu__list-link">ログアウト</a></li>
                    <li class="nav-menu__list-item"><a href="./myPage.php" class="nav-menu__list-link">マイページ</a></li>
                    <li class="nav-menu__list-item"><a href="" class="nav-menu__list-link btn btn--header">アップロード</a></li>
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

                        <div class="profile__info">
                            <h2 class="profile__name"><?php echo sanitize($userName); ?></h2>

                            <?php if( !empty($_SESSION['user_id']) && $_SESSION['user_id'] === $u_id ){ ?>

                                <div class="btn btn--msg"><a href="./myPage.php">マイページ</a></div>

                            <?php } else { ?>

                                <form action="" method="POST">
                                    <input type="submit" value="メッセージ" name="submit" class="btn btn--msg">
                                </form>

                            <?php } ?>
                        </div>

                        <div class="profile__social">
                            <i class="profile__social-icn">🐤</i><span>U@WEB</span>
                        </div>

                    </div>

                </div>
        </section>
        
        <section class="image mt100">
                <div class="image__head">
                    <h2 class="image__title">投稿画像</h2>
                    <span class="image__total">投稿数：125枚</span>
                </div>
                
                <div class="bg-image">
                    <?php
                        if(!empty($images)){
                            foreach($images as $val){
                    ?>
                    <div class="bg-image__item">
                        <a href="imgUpload.php?i_id=<?php echo sanitize($val['id']); ?>">
                            <img src="../dist/<?php echo showImg( sanitize($val['name']) ); ?>" alt="" style="width: 100%;">
                        </a>
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