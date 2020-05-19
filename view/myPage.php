<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('　マイページ画面　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

//ユーザーID//
$u_id = $_SESSION['user_id'];
//DBからmyUser情報取得//
$myData = getUserData($u_id);
//DBからmyUser画像全取得//
$imageData = getUserImages($u_id);
//DBからマイメッセージ情報取得//
$myBoardAndMsg = getMyBoardAndMsg($u_id);
//DBからお気に入り全画像取得//
$likeData = getMyLike($u_id);

debug('$myMAB:'.print_r($myBoardAndMsg,true));
debug('$likeData:'.print_r($likeData,true));
// debug('$imageData:'.print_r($imageData,true));
// debug('$imageData:'.print_r($imageData,true));
// debug('$myData:'.print_r($myData,true));
?>

<!DOCTYPE html>
<html lang="ja">

<?php
$title = 'マイページ';
require('head.php');
?>

<style>

/* ------------------------ */
/* utility
/* ----------------------- */
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
                <li class="nav-menu__list-item"><a href="./index.php" class="nav-menu__list-link">ホーム</a></li>
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
<div class="l-wrapper mt100">
    <main id="main">

                <section class="profile profile--myPage mt20">
                    <div class="profile__avatar">
                        <img src="../dist/<?php echo showImg(sanitize($myData['pic'])); ?>" alt="">
                    </div>
                    
                    <div class="profile__detail">

                        <div class="profile__info">
                            <h2 class="profile__name"><?php echo sanitize($myData['first_name']. ' ' .$myData['last_name']); ?></h2>
                        </div>

                        <div class="profile__social">
                            <i class="profile__social-icn">🐤</i><span>U@WEB</span>
                        </div>

                    </div>
                </section>

                <section class="mailBox">
                    <h2 class="mailBox__title">連絡掲示板</h2>

                        <table>
                            <thead>
                                <tr>
                                    <th>最新送信日時</th>
                                    <th>取引相手</th>
                                    <th>メッセージ</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                if(!empty($myBoardAndMsg)){
                                    foreach($myBoardAndMsg as $key => $val){
                                        if(!empty($val['msg'])){
                                            $msg = array_shift($val['msg']);
                                ?>
                                    <a href="">
                                        <tr>
                                            <td><?php echo sanitize( date('Y.m.d', strtotime($msg['send_date'])) ); ?></td>
                                            <td><?php echo sanitize($val['p_first']. ' ' .$val['p_last']); ?></td>
                                            <td><?php echo sanitize($msg['comment']); ?></td>
                                        </tr>
                                    </a>
                                <?php
                                        } else {
                                ?>

                                    <tr>
                                        <td><?php echo sanitize( date('Y.m.d', strtotime($msg['send_date'])) ); ?></td>
                                        <td><?php echo sanitize($val['p_first']. ' ' .$val['p_last']); ?></td>
                                        <td>メッセージがありません。</td>
                                    </tr>

                                <?php
                                        }
                                    }
                                } 
                                ?>
                                
                                <?php for($i = 0; $i <= 5; $i++){?>
                                    <tr>
                                        <td>2020.03.12</td>
                                        <td>test 2</td>
                                        <td>めまして。test2です</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                </section>

                <section class="l-image">

                    <ul class="tab-gruop js-tab">
                        <li class="tab is-active">投稿画像一覧</li>
                        <li class="tab">お気に入り一覧</li>
                    </ul>

                    <div class="image--myPage">
                        <!-- 投稿画像一覧 -->
                        <div class="bg-image-myPage js-tab-content is-show">
                            <?php
                                if(!empty($imageData)){
                                    foreach($imageData as $val){
                            ?>
                                        <div class="bg-image__item-myPage">
                                            <img src="../dist/<?php echo showImg( sanitize($val['name']) ); ?>" alt="">
                                        </div>
                            <?php
                                    }
                                } else {
                            ?>
                                        <p>まだ投稿がありません。</p>
                            <?php
                                }
                            ?>
                        </div>
                        <!-- お気に入り一覧 -->
                        <div class="bg-image-myPage js-tab-content">
                            <?php
                                if(!empty($likeData)){
                                    foreach($likeData as $val){
                            ?>
                                        <div class="bg-image__item-myPage">
                                            <a href="<?php echo './profile.php?u_id='.$val['user_id']; ?>">
                                                <img src="<?php echo showImg( sanitize('../dist/'.$val['name']) ); ?>" alt="">
                                            </a>
                                        </div>
                                        
                            <?php
                                    }
                                } else {
                            ?>
                                    <p>お気に入りはありません。</p>
                            <?php
                                }
                            ?>
                        </div>
                    </div>

                </section>
    </main>

    <aside id="side-bar">
        <nav class="nav-menu">
            <ul class="nav-menu__side">
                <li class="nav-menu__list-item"><a href="" class="nav-menu__list-link">ホーム</a></li>
                <li class="nav-menu__list-item"><a href="" class="nav-menu__list-link">編集</a></li>
                <li class="nav-menu__list-item"><a href="" class="nav-menu__list-link">履歴</a></li>
                <li class="nav-menu__list-item"><a href="" class="nav-menu__list-link">画像</a></li>
                <li class="nav-menu__list-item"><a href="" class="nav-menu__list-link">退会</a></li>
            </ul>
        </nav>
    </aside>
</div>

<footer id="footer" class="l-footer js-footer">
    Copryright&copy; U
</footer>

<script src="../dist/js/bundle.js"></script>
</body>
</html>