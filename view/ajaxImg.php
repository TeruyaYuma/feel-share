<?php
header("Content-type: application/json; charset=utf-8");

if(!empty($_GET)) {

    $dsn = 'mysql:dbname=feel_share;host=localhost;charset=utf8';
    $user = 'root';
    $password = 'Yuma@19860120';
    $options = array(
        
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );

    $dbh = new PDO($dsn, $user, $password, $options);

    $stmt = $dbh->prepare('SELECT i.id AS img_id, i.user_id, i.name, i.comment,
                                  u.first_name, u.last_name, u.pic, u.twitter_id
                           FROM images AS i LEFT JOIN users AS u 
                           ON i.user_id = u.id
                           WHERE i.id = :id');

    $stmt->execute(array(':id' => $_GET['id']));

    $result = 0;
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $dbh->prepare('SELECT image_id FROM good WHERE image_id = :id');
    
    $stmt->execute(array(':id' => $_GET['id']));

    $cnt = $stmt->fetchAll();
    $result['likeCount'] = count($cnt);
    
    $stmt = $dbh->prepare('SELECT i_tag.img_id, t.id, t.name FROM image_tag AS i_tag RIGHT JOIN tags AS t
                           ON i_tag.tag_id = t.id
                           WHERE i_tag.img_id = :id');
    
    $stmt->execute(array(':id' => $_GET['id']));

    $result['tags'] = $stmt->fetchAll();

    if(!empty($result)) {
        echo json_encode($result);
    }
    
    exit();
}