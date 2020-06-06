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

debug('$imgData:'.print_r($imageData,true));
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
                <li class="nav-menu__list-item"><a href="./index.php" class="nav-menu__list-link" class="nav-menu__list-link">ホーム</a></li>
                <li class="nav-menu__list-item"><a href="./logout.php" class="nav-menu__list-link">ログアウト</a></li>
                <li class="nav-menu__list-item"><a href="./myPage.php" class="nav-menu__list-link">マイページ</a></li>
                <li class="nav-menu__list-item"><a href="./contact.php" class="nav-menu__list-link">お問い合わせ</a></li>
                <li class="nav-menu__list-item"><a href="./imgUpload.php" class="nav-menu__list-link btn btn--header">アップロード</a></li>
            </ul>
        </nav>

    </header>
    <!-- header -->
    <div class="l-wrapper mt100">
        <main id="main" class="l-main">

            <section class="profile profile--myPage mt20">
                <div class="profile__avatar">
                    <img src="../dist/<?php echo showImg(sanitize($myData['pic'])); ?>" alt="">
                </div>
                    
                <div class="profile__detail">

                    <div class="profile__info">
                        <h2 class="profile__name"><?php echo sanitize($myData['first_name']. ' ' .$myData['last_name']); ?></h2>
                    </div>

                    <div class="profile__social">
                    <i class="fab fa-twitter-square profile__social-icon" aria-hidden="true"></i><span>U@WEB</span>
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
                                
                                    <tr>
                                    
                                        <td><a href="./msg.php?b_id=<?php echo sanitize($msg['board_id']); ?>"><?php echo sanitize( date('Y.m.d', strtotime($msg['send_date'])) ); ?></a></td>
                                        <td><a href="./msg.php?b_id=<?php echo sanitize($msg['board_id']); ?>"><?php echo sanitize($val['p_first']. ' ' .$val['p_last']); ?></a></td>
                                        <td><a href="./msg.php?b_id=<?php echo sanitize($msg['board_id']); ?>"><?php echo mb_substr(sanitize($msg['comment']),0,10); ?>...</a></td>
                                        
                                    </tr>
                            <?php
                                    } else {
                            ?>

                                <tr>
                                    <td><a href="./msg.php?b_id=<?php echo sanitize($msg['board_id']); ?>"><?php echo sanitize( date('Y.m.d', strtotime($msg['send_date'])) ); ?></a></td>
                                    <td><a href="./msg.php?b_id=<?php echo sanitize($msg['board_id']); ?>"><?php echo sanitize($val['p_first']. ' ' .$val['p_last']); ?></a></td>
                                    <td>メッセージがありません。</td>
                                </tr>

                            <?php
                                    }
                                }
                            }
                            ?>
                        </tbody>
                    </table>
            </section>
            
            
            
            <section class="l-image">

                <ul class="tab-gruop js-tab">
                    <li class="tab isActive">投稿画像一覧</li>
                    <li class="tab">お気に入り一覧</li>
                </ul>

                <div class="image-container">
                    <!-- お気に入り一覧 -->
                    <div class="image image--s js-tab-content isNone">
                        <?php
                            if(!empty($likeData)){
                                foreach($likeData as $val){
                        ?>
                                    <div class="image__item-s image__item-s--low js-slideDown">
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
                    <!-- 投稿画像一覧 -->
                    <div class="image image--s js-tab-content">
                        <?php
                            if(!empty($imageData)){
                                foreach($imageData as $val){
                        ?>
                                    <div class="image__item-s image__item-s--low js-slideDown">
                                        <a href="./imgUpload.php?i_id=<?php echo sanitize($val['id']); ?>">
                                            <img src="../dist/<?php echo showImg( sanitize($val['name']) ); ?>" alt="">
                                        </a>
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

                </div>

            </section>
            <button class="btn btn--more js-more">もっと見る</button>
        </main>

        <aside id="side-bar">
            <ul class="nav-menu__side">
                <li class="nav-menu__side-item"><a href="./index.php" class="nav-menu__side-link">ホーム</a></li>
                <li class="nav-menu__side-item"><a href="./profEdit.php" class="nav-menu__side-link">プロフィール編集</a></li>
                <li class="nav-menu__side-item"><a href="./passEdit.php" class="nav-menu__side-link">パスワード編集</a></li>
                <li class="nav-menu__side-item"><a href="./withdraw.php" class="nav-menu__side-link">退会</a></li>
            </ul>
        </aside>
    </div>

<footer id="footer" class="l-footer js-footer">
    Copryright&copy; U
</footer>

<script src="../dist/js/bundle.js"></script>
</body>
</html>