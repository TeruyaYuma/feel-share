<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　画像アップロード　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

//ユーザーID//
$u_id = $_SESSION['user_id'];
//投稿画像ID//
$i_id = (!empty($_GET['i_id']))? $_GET['i_id'] : '';
//投稿画像取得//
$dbFormData = (!empty($i_id))? getProduct($u_id, $i_id) : '';
//タグ取得//
if(!empty($dbFormData)){
    $dbFormData['tags'] = getProductTags($dbFormData['id']);
}
$dbTagId = (!empty($dbFormData['tags']))? array_column($dbFormData['tags'],"id") : '';
//編集用判別フラグ//
$edit_flg = (empty($dbFormData))? false : true;

debug('タグ：'.print_r($dbFormData,true));
debug('tags:'.print_r($dbTagId,true));

if(!empty($_POST)) {
    debug('画像：'.print_r($_FILES,true));
    debug('画像：'.print_r($_POST,true));
    $img = (!empty($_FILES['pic']['name']))? uploadImg($_FILES['pic'],'pic') : '';
    $img = ( empty($img) && !empty($dbFormData['name']))? $dbFormData['name'] : $img;
    //costumFilter: null,空をフィルタリング//
    $postTags = (!empty($_POST['tags']))? $_POST['tags'] : ''; 
    $postTags = ( empty($postTags) && !empty($dbFormData['tags']))? $dbFormData['tags'] : $postTags;
    $postTags = (!empty($postTags))? array_filter($postTags, 'costumFilter'): '';

    $comment = (!empty($_POST['comment']))? $_POST['comment'] : '';
    $comment = ( empty($comment) && !empty($dbFormData['comment']))? $dbFormData['comment'] : $comment;
    debug('$img'.print_r($img,true));
    debug('$tags:'.print_r($postTags,true));
    debug('$comment:'.print_r($comment,true));
    //dbにデータ無ければPOSTの値をバリデーション//
    if(empty($dbFormData)){
        debug('データベース無し');
        //空値チェック：画像//
        validRequire($img, 'pic');
        //最大文字数チェック：コメント//
        validMaxLen($comment, 'comment');
        //最大文字数チェック：タグ//
        if(!empty($postTags)){
           foreach($postTags as $key => $val){
               validMaxLen($val, 'tag'.$key, 10);
               if(!empty($err_msg['tag'.$key])){
                $err_msg['tags'][$key] =  $err_msg['tag'.$key]; 
               }
           }
        }       
    //dbにデータがあってPOSTとdbデータが違うならバリデーション//
    } else {
        debug('データベース有り');
        //空値チェック：db画像//
        if($dbFormData['name'] !== $img){
            validRequire($img, 'pic');
        }
        //最大文字数チェック：dbタグ//
        if(!empty($postTags)){
            foreach($postTags as $key => $val){
                if($dbFormData['tags'][$key] !== $val){
                    validMaxLen($val, 'tag'.$key, 10);
                    if(!empty($err_msg['tag'.$key])){
                        $err_msg['tags'][$key] =  $err_msg['tag'.$key]; 
                    }
                }
            }
        }
        //最大文字数チェック：dbコメント//
        if($dbFormData['comment'] !== $comment){
            validMaxLen($comment, 'comment', 500);
        }
        
    }
    
    if(empty($err_msg)){
        debug('エラー無し');
        try{

        $dbh = dbConnect();
        //タグテーブル検索、生成//
        if(isset($postTags)){
            $tagId = [];
            foreach($postTags as $key => $val){
                debug('$val'.$val);
                debug($key.'回目');

                $sql = 'SELECT id FROM tags WHERE name = :name';
                $data = array(':name' => $val);

                $stmt = queryPost($dbh, $sql, $data);
                $rst = $stmt->fetch(PDO::FETCH_ASSOC);
                $rst = $rst['id'];
                debug('$rst'.print_r($rst,true));

                if(!$rst){
                    debug($key.'回目の結果はfalseです');
                    $sql = 'INSERT INTO tags(name, create_date) VALUES(:name, :date)';
                    $data = array(':name' => $val, ':date' => date('Y-m-d H:i:s'));

                    $stmt = queryPost($dbh, $sql, $data);

                    $rst = $dbh->lastInsertId();

                    debug($key.'回目の結果'.print_r($rst,true));
                }

                $tagId[] = $rst;
            }
            debug('$rst総合'.print_r($tagId,true));
        }
        //imagesテーブル登録//
        if($edit_flg){
            $sql = 'UPDATE images SET name = :name, user_id = :u_id, comment = :comment WHERE user_id = :u_id AND id = :i_id';
            $data = array(':name' => $img, ':u_id' => $u_id, ':comment' => $comment, ':i_id' => $i_id);
        } else {
            $sql = 'INSERT INTO images (name, user_id, comment, create_date) VALUES (:name, :u_id, :comment, :date)';
            $data = array(':name' => $img, ':u_id' => $u_id, ':comment' => $comment,
                        ':date' => date('Y-m-d H:i:s'));
        }

        $stmt = querypost($dbh, $sql, $data);

        if(!$stmt) {
            debug('クエリ失敗');
            $err_msg['common'] = MSG07;
        }

        if($edit_flg){
            $imgId = $i_id;
        } else {
            $imgId = $dbh->lastInsertId();
            debug('lastId:'.$imgId);
        }
        //image_tagテーブル登録//
        if( !empty($tagId) && !empty($imgId) ){

            if($edit_flg){

            $oldTagId = array_diff($dbTagId,$tagId);
            $newTagId = array_diff($tagId,$dbTagId);
            debug('$old'.print_r($oldTagId,true));
            debug('$new'.print_r($newTagId,true));

                if(!empty($oldTagId)){
                    foreach($oldTagId as $val){
                        $sql = 'DELETE FROM image_tag WHERE img_id = :i_id AND tag_id = :t_id';
                        $data = array(':i_id' => $imgId, ':t_id' => $val);

                        $stmt = queryPost($dbh, $sql, $data);

                        if(!$stmt){
                            debug('クエリ失敗');
                            $err_msg['common'] = MSG07;
                        }
                    }
                }
                if(!empty($newTagId)){
                    foreach($newTagId as $val){
                        $sql = 'INSERT INTO image_tag(img_id, tag_id, create_date) VALUES (:i_id, :t_id, :date)';
                        $data = array(':i_id' => $imgId,':t_id' => $val, ':date' => date('Y-m-d H:i:s'));

                        $stmt = queryPost($dbh, $sql, $data);

                        if(!$stmt){
                            debug('クエリ失敗');
                            $err_msg['common'] = MSG07;
                        }
                    }
                }

            } else {
                foreach($tagId as $key => $val) {

                    $sql = 'INSERT INTO image_tag(img_id, tag_id, create_date) VALUES(:imgId, :tag_id, :date)';
                    $data = array(':imgId' => $imgId, ':tag_id' => $val, ':date' => date('Y-m-d H:i:s'));

                    $stmt = queryPost($dbh, $sql, $data);

                    if(!$stmt) {
                        debug('クエリ失敗');
                        $err_msg['common'] = MSG07;
                    }
                }

            }
        }

        $_SESSION['msg_success'] = SUC03;
        debug('アップロード成功');
        header("Location:index.php");

        } catch (Exeption $e){
            error_log('エラー発生：'. $e->getMessage());
        }
    }
    debug('通り過ぎ');
}

?>
<!DOCTYPE html>
<html lang="ja">

<?php
$title = 'イメージアップロード';
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
    <main>
        <div class="container container--m">
        
            <form action="" method="POST" enctype="multipart/form-data" class="form form--imgUpload mt100">

                <label for="file" class="label label--upload  js-drop-area">
                    <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                    <input type="file" id="file"class="input-file js-file" name="pic" value="<?php echo sanitize(getFormData('name')); ?>">
                    <div class="msg-area">
                        <?php if(!empty($err_msg['pic'])){ echo $err_msg['pic']; }?>
                    </div>
                </label>

                <div class="prev js-prev">
                    <div class="prev__group">

                        <div class="prev__left">
                            <img src="<?php echo sanitize(getFormData('name')); ?>" class="prev-img" alt="">
                        </div>

                        <div class="prev__right">
                            <div class="js-tag-area">
                                <?php
                                //  編集モード且つタグがあれば個数分表示
                                    if($edit_flg && $dbFormData['tags']){
                                        foreach($dbFormData['tags'] as $key => $val){
                                ?>
                                            <div class="js-tags">
                                                <input name="tags[]" type="text" class="input input--tag js-input-tag"
                                                value="<?php echo ( !empty($_POST['tags'][$key]) )? sanitize($_POST['tags'][$key]) : sanitize($val['name']); ?>">
                                            </div>
                                <?php
                                        }
                                // 新規作成もしくはタグがなければ表示
                                    } else {
                                        //POSTあればPOST分表示//
                                        if(!empty($_POST['tags'])){
                                            foreach($_POST['tags'] as $key => $val){
                                ?>
                                                <div class="js-tags">
                                                    <input name="tags[]" type="text" class="input input--tag js-input-tag" value="<?php echo sanitize($_POST['tags'][$key]); ?>">
                                                </div>
                                <?php
                                            }
                                        //何も無ければ表示//
                                        } else {
                                ?>
                                            <div class="js-tags">
                                                <input name="tags[]" type="text" class="input input--tag js-input-tag">
                                            </div>
                                <?php           
                                        } 
                                    }
                                ?>
                            </div>
                            
                            <div class="form__prev-btn">
                                <button type="button" class="btn btn--prev js-click-append">タグを追加</button>
                            </div>
                            
                            <textarea name="comment" id="" class="txtarea txtarea--prev"><?php echo sanitize(getFormData('comment')); ?></textarea>
                        </div>
                    </div>

                    <div class="form__prev-btn">
                        <input type="submit" class="btn btn--prev">
                    </div> 
                </div>
            </form>

        </div>
    </main>
    
    <footer id="footer" class="l-footer js-footer">
    Copryright&copy; U
    </footer>

<script src="../dist/js/bundle.js"></script>
</body>
</html>