<?php
session_start();
if (
    isset($_SESSION['userID']) &&
    isset($_GET['postID'])
) {
    //check if user already liked the post
    $postID = $_GET['postID'];
    require './db.php';

    $stmt = $db->prepare('SELECT * FROM likes WHERE postID = ? AND userID = ?');
    $stmt->execute([$postID, $_SESSION['userID']]);
    $liked = $stmt->fetch(PDO::FETCH_OBJ);

    if ($liked == true) {
        $stmt = $db->prepare('SELECT * FROM post WHERE postID=?');
        $stmt->execute([$postID]);
        $post = $stmt->fetch(PDO::FETCH_OBJ);
        $stmt = $db->prepare('UPDATE post SET likesCount = ? WHERE postID = ?');
        $stmt->execute([
            $post->likesCount - 1,
            $postID
        ]);
        //remove from likes table
        $stmt = $db->prepare('DELETE FROM likes WHERE postID = ? AND userID = ?');
        $stmt->execute([$postID, $_SESSION['userID']]);
    }
}
