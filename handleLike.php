<?php
session_start();
if (
    isset($_SESSION['userID']) &&
    isset($_GET['postID'])
) {
    $postID = $_GET['postID'];
    require './db.php';
    $stmt = $db->prepare('SELECT * FROM post WHERE postID=?');
    $stmt->execute([$postID]);
    $post = $stmt->fetch(PDO::FETCH_OBJ);
    $stmt = $db->prepare('UPDATE post SET likesCount = ? WHERE postID = ?');
    $stmt->execute([
        $post->likesCount + 1,
        $postID
    ]);
}
