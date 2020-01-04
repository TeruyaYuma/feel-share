<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　画像アップロード　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

if(!empty($_POST)) {
    debug('画像：'.print_r($_FILES,true));

    $img = (!empty($_FILES['pic']['name']))? uploadImg($_FILES['pic'],'pic') : '';
    $u_id = $_SESSION['user_id'];
    debug('$img'.print_r($img,true));

    $dbh = dbConnect();

    $sql ='INSERT INTO images (name, user_id, create_date) VALUES (:name, :u_id, :date)';
    $data = array(':name' => $img, ':u_id' => $u_id,
                  ':date' => date('Y-m-d H:i:s'));
    
    $stmt = querypost($dbh, $sql, $data);

    if(!$stmt) {
        debug('クエリ失敗');
        $err_msg['common'] = MSG07;
    }

    debug('アップロード成功');
    header("Location:index.php");
}

?>
<!DOCTYPE html>
<html lang="ja">

<?php
$title = 'イメージアップロード';
require('head.php');
?>

<body>
<head></head>

<main>
<form action="" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
    <input type="file" name="pic">
    <div>
        <?php if(!empty($err_msg['pic'])){ echo $err_msg['pic']; }?>
    </div>
    <input type="submit">
</form>
</main>

</body>
</html>