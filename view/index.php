<?php 
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　メインページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();


$currentPageNum = (!empty($_GET['p']))? $_GET['p'] : 1;
debug('p'.(int)$currentPageNum);

if( !empty($currentPageNum) && (int)$currentPageNum <= 0 ) {
    error_log('エラー発生：指定ページに不正な値が入りました。');
    header("Location:index.php");
    return;
}

$listSpan = 9;

$category = (!empty($_GET['category']))? $_GET['category'] : '';

$sort = (!empty($_GET['sort']))? $_GET['sort'] : '';

$currentMinNum = (($currentPageNum - 1) * $listSpan);

$images = getImgList($currentMinNum, $category, $sort);
$heroImg = getRandomImage(1);
debug('$freeWord:'.$category);
debug('$order:'.$sort);
debug('$images:'. print_r($images,true));
debug('$heroImg:'.print_r($heroImg[0]['name'],true));
?>
<!DOCTYPE html>
<html lang="ja">
<?php
    $title = 'メインページ';
    require('head.php');
?>

<style>
 /* image */
 
 /* modal */

.isOpen{
    display: block;
}
.active{
    color: red;
}
/* hero */
.l-hero{
    background-image: url('<?php echo '../dist/'.$heroImg[0]['name']; ?>');
}
/* ///////////// 
     utility
///////////// */
.mt50{
    margin-top: 50px;
}
</style>
<body>
    <header class="l-header header header--fix" id="header">
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
    <section class="l-hero">
        <div class="modal modal--hero">
            <h1 class="hero__title">FEEL <br>SO GOOD</h1>
        </div>
    </section>
    <!-- hero -->
    <section class="l-search search">
        <div class="search__body">
            <form action="" method="GET">

                <input type="text" name="category" class="input input--search" placeholder="freeword検索">
                <select name="sort" class="input input--search-select" id="">
                    <option value="0">新しい順</option>
                    <option value="1">古い順</option>
                </select>

                <input type="submit" class="btn btn--search" value="検索">

            </form>
        </div>
    </section>
    <!-- search -->
    <section class="l-image">
        
        <div class="modal modal--image-list js-modal">

            <div class="modal__close js-close">
                <p class="modal__close-icon">✖︎</p>
            </div>

            <div class="container container--l">
                <div class="panel mt50">

                    <div class="panel__head">
                        <div class="panel__auth">
                            <a class="panel__auth-avatar js-link" href=""><img class="panel__auth-img js-avatar" src="" alt=""></a>
                            <h2 class="panel__auth-name"><span class="js-firstName"></span> <span class="js-lastName"></span></h2>
                        </div>
                        
                        <div class="panel__good">
                            <span class="js-good-cnt"></span>件のいいね
                        </div>

                    </div>

                    <div class="panel__body">
                        <div class="panel__img">
                            <img class="js-img" src="../dist/" alt="">
                        </div>

                        <div class="panel__tags">
                            <span class="panel__tags-title">タグ：</span>
                            <span class="js-tags-area"></span>
                        </div>

                        <div class="panel__cmt">
                            <span class="panel__cmt-title">コメント：</span>
                            <span class="js-comment"></span>
                        </div>
                        
                    </div>

                </div>
            </div>

        </div>
        <!-- modal -->
        <div class="container container--l">

            <h2 class="container__title">画像一覧</h2>
            
            <div class="bg-image">
                <?php
                if($images['data']){
                    foreach($images['data'] as $val){
                ?>

                    <div class="bg-image__item js-ajaxImg" data-id="<?php echo $val['id']; ?>">
                        <img src="<?php echo '../dist/'.showImg(sanitize($val['name'])); ?>" alt="">
                        <div class="bg-image__icon js-heart 
                            <?php if(array_key_exists('user_id', $_SESSION)){
                                    if( isLike($_SESSION['user_id'], $val['id']) ){ echo 'active'; }
                                }
                            ?>">♡
                        </div>
                    </div>

                <?php
                    }
                } else {
                ?>

                    <p>まだ投稿はありません。</p>

                <?php
                }
                ?>
            </div> 
            <!-- bg-image --> 

            <?php echo pagination($currentPageNum, $images['total_page']);?>

        </div> 
        <!-- container container--l -->
    </section>
    
    <footer id="footer" class="l-footer js-footer">
        Copryright&copy; U
    </footer>

<script src="../dist/js/bundle.js"></script>
</body>
</html>