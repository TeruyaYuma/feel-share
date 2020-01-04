<?php 
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　メインページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

$images = getImg();
debug('$images:'. print_r($images,true));
?>
<!DOCTYPE html>
<html lang="ja">
<?php
    $title = 'メインページ';
    require('head.php');
?>

<style>
 .over{
     display: flex;
     flex-wrap: wrap;
 }
 .img{
     display: inline-block;
     width: 30%;
     height: auto;
     
 }
</style>
<body>
    <p>メインページ</p>
    <a href="logout.php">ログアウト</a>

    <div class="over">
    <?php 
    foreach($images as $val){
    ?>
        <img src="<?php echo '../dist/'.$val['name']; ?>" alt="" class="img">
    <?php
    }
    ?>
    </div>

</body>
</html>