<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　退会　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

if(!empty($_POST)) {
    debug('POSTされました');

    if(empty($_SESSION['user_id'])) {
       debug('未ログインユーザーです');

       header("Location:login.php");
    }

    $user_id = $_SESSION['user_id'];
    debug('user_id：'. print_r($_SESSION['user_id'], true));

    try{

        $dbh = dbConnect();
        // usersを論理削除
        $sql ='UPDATE users SET delete_flg = 1 WHERE id = :user_id';
        $data = array(':user_id' => $user_id);

        $stmt = querypost($dbh, $sql, $data);

        if($stmt){
            debug('usersデリートOK');

            // images&image_tag論理削除
            $sql ='SELECT id FROM images WHERE user_id = :u_id';
            $data = array(':u_id' => $user_id);

            $stmt = querypost($dbh, $sql, $data);

            $rst['image_id'] = $stmt->fetchAll();
            
            if(!empty($rst['image_id'])){
                debug('imageID取得');

                 // imagesを論理削除
                 $sql = 'UPDATE images SET delete_flg = 1 WHERE user_id = :u_id';
                 $data = array(':u_id' => $user_id);
 
                 $stmt = querypost($dbh, $sql, $data);
                 debug('imagesデリートOK');
    
                foreach($rst['image_id'][0] as $val){
                    $sql = 'UPDATE image_tag SET delete_flg = 1 WHERE img_id = :i_id';
                    $data = array(':i_id' => $val);

                    $stmt = querypost($dbh, $sql, $data);
                    debug('iamge_tagデリートOK');
                }
            }

            // boards&
            $sql = 'SELECT id FROM boards WHERE from_user = :u_id OR to_user = :u_id';
            $data = array(':u_id' => $user_id);

            $stmt = querypost($dbh, $sql, $data);
            $rst['board_id'] = $stmt->fetchAll();

            var_dump($rst);
            if(!empty($rst['board_id'])){
                debug('board_id取得');
                foreach($rst['board_id'][0] as $val){
                    debug('$val:'.print_r($val));
                    $sql = 'UPDATE boards SET delete_flg = 1 WHERE id = :b_id';
                    $data = array(':b_id' => $val);
    
                    $stmt = querypost($dbh, $sql, $data);

                    // msgを論理削除
                    $sql ='UPDATE msg SET delete_flg = 1 WHERE board_id = :b_id';
                    $data = array(':b_id' => $val);

                    $stmt = querypost($dbh, $sql, $data);
                }
                
            }

            if($stmt) {
                    
                session_unset();
                $_SESSION['msg_success'] = SUC04;
                    
                debug('セッションの中身：'. print_r($_SESSION, true));

                debug('トップページ遷移します');
                header("Location:index.php");
                return;
                    
            } else {
                debug('クエリ失敗');
                $err_msg['common'] = MSG07;
            }

        }

    } catch (Exception $e) {
        error_log('エラー発生：'.$e->getMessage());
        $err_msg['common'] = MSG07;
    }
    
}

// if($stmt){
            

            
            

?>


<!DOCTYPE html>
<html lang="ja">

<?php
$title = '退会';
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

    <main id="main">
        <div class="l-form modal modal--backgroundImg">
            <div class="container container--s">

                <form action="" class="form mt220" method="POST">
                    <h2 class="form__title">退会</h2>

                    <div class="msg-area">
                        <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                    </div>

                    <input type="submit" class="btn btn--form" value="退会" name="submit">
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