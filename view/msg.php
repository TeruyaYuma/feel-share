<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　メッセージページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//require('auth.php');//

//boards id//
$b_id = (!empty($_GET['b_id']))? $_GET['b_id'] : '';

if( empty($b_id) || !empty($b_id) && (int)$b_id <= 0 ) {
    error_log('エラー発生：指定ページに不正な値が入りました。');
    header("Location:index.php");
    return;
}
//boradとmsg情報取得//
$viewData = getMsgAndBoard($b_id);
debug('$viewData:'.print_r($viewData,true));

//相手のIDの抜き出し//
$dealUserIds[] = $viewData[0]['to_user'];
$dealUserIds[] = $viewData[0]['from_user'];
debug('$dealUserIds'.print_r($dealUserIds,true));

if(($key = array_search($_SESSION['user_id'], $dealUserIds)) !== false){
    unset($dealUserIds[$key]);
}

$partnerUserId = array_shift($dealUserIds);
//相手の情報取得//
if(isset($partnerUserId)){
    $partnerUserInfo = getUserData($partnerUserId);
    $partnerUserName = $partnerUserInfo['first_name']. ' ' .$partnerUserInfo['last_name'];
}
debug('$partnerUserInfo:'.print_r($partnerUserInfo, true));

//自分の情報//
$myUserInfo = getUserData($_SESSION['user_id']);
debug('$myUserInfo'.print_r($myUserInfo,true));

if(!empty($_POST)){

    $msg = (isset($_POST['msg']))? $_POST['msg'] : '';
    debug('#msg:'.$msg);

    try{

        $dbh = dbConnect();

        $sql = 'INSERT INTO msg(board_id, user_id, comment, send_date, create_date) VALUES(:b_id, :u_id, :comment, :s_date, :c_date)';
        $data = array(':b_id' => $b_id, ':u_id' => $_SESSION['user_id'], ':comment' => $msg, ':s_date' => date('Y-m-d H:i:s'), 'c_date' => date('Y-m-d H:i:s'));

        $stmt = queryPost($dbh, $sql, $data);

        if($stmt) {
            debug('連絡掲示板へ遷移します');
            header("Location:".$_SERVER['PHP_SELF'].'?b_id='.$b_id);
        }
    } catch (Exeption $e) {
        error_log('エラーが発生しました');
        $err_msg['common'] = MSG07;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<?php
    $title = 'メッセージページ';
    require('head.php');
?>

<style>

/* /////////////// */
/* utility
/* /////////////// */
.mt{
    margin-top: 80px;
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
    <main id="#main">
        <div class="container container--m">
            <section class="message mt100">
                
                <div class="message__head">
                    <h1 class="message__title"><?php echo $partnerUserName; ?></h1>
                </div>
                
                <div class="message__body js-scroll-auto">
                    <?php
                    if(!empty($viewData[0]['m_id'])){
                        foreach($viewData as $key => $val){
                            if(!empty($val['user_id']) && $val['user_id'] == $_SESSION['user_id']){
                    ?>

                        <div class="message__body-item">
                            <div class="message__body-baloon message__body-baloon--right">
                                <p class="message__body-txt message__body-txt--right"><?php echo sanitize(($key + 1).'回目、コメント：'.$val['comment']); ?></p>
                            </div>
                        </div>

                    <?php
                            } else {
                    ?>

                        <div class="message__body-item">
                            <div class="message__body-baloon message__body-baloon--left"> 
                                <div class="message__body-avatar">
                                    <span class="message__body-icon">
                                        <img src="<?php echo '../dist/'.showImg( sanitize($partnerUserInfo['pic']) );?>" alt="">
                                    </span>
                                    <span class="message__body-name"><?php echo $partnerUserName; ?></span>
                                </div>
                                <p class="message__body-txt message__body-txt--left"><?php echo sanitize(($key + 1).'回目、コメント：'.$val['comment']); ?></p>
                            </div>
                        </div>

                    <?php
                            }
                        }
                    } else {
                    ?>

                    <p>まだ投稿がありません。</p>

                    <?php
                    }
                    ?>
                </div>
            
            
                <form action="" method="POST" class="form-msg">
                    <textarea name="msg" class="txtarea form__txtarea"></textarea>
                    <input type="submit" class="btn btn--message">
                </form>

            </section>
        </div>
    </main>

    <footer id="footer" class="l-footer js-footer">
    Copryright&copy; U
    </footer>

<script src="../dist/js/bundle.js"></script>
</body>
</html>