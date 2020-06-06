<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ajaxLike　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if( isset($_POST['id']) && isset($_SESSION['user_id']) && isLogin() ){
    debug('post送信');
    $image_id = $_POST['id'];
    debug('商品id:'. $image_id);

    try{

        $dbh = dbConnect();

        $sql = 'SELECT * FROM good WHERE user_id = :u_id AND image_id = :image_id';
        $data = array(':u_id' => $_SESSION['user_id'], ':image_id' => $image_id);

        $stmt = queryPost($dbh, $sql, $data);
        $rstCount = $stmt->rowCount();
        echo $rstCount;
        debug('$rstCount:'.$rstCount);

        if($rstCount){

            $sql = 'DELETE FROM good WHERE user_id = :id AND image_id = :image_id';
            $data = array(':id' => $_SESSION['user_id'], ':image_id' => $image_id);

            $stmt = queryPost($dbh, $sql, $data);

        } else {

            $sql = 'INSERT INTO good(user_id, image_id, create_date) VALUES (:id, :image_id, :date)';
            $data = array(':id' => $_SESSION['user_id'], ':image_id' => $image_id, ':date' => date('Y-m-d H:i:s'));

            $stmt = queryPost($dbh, $sql, $data);
        }

    } catch (Exeption $e) {
        error_log('エラーが発生しました。'. $e->getMessage());

    }
} else {
    $rst = false;
    echo json_encode($rst);
}