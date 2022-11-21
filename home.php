<?php
session_start();
if (!isset($_SESSION['userID'])) header('location: ./login.php');
require './db.php';

if (!empty($_POST)) {
    $postTextContent = (isset($_POST['postTextContent']) && !empty(trim($_POST['postTextContent']))) ? $_POST['postTextContent'] : null;

    if (isset($_FILES['postPhoto']) && $_FILES['postPhoto']['name'] != '') {
        $postPhoto = $_FILES['postPhoto'];
        if ($postPhoto['type'] == 'image/jpeg' || $postPhoto['type'] == 'image/png') {
            if ($postPhoto['error'] != 2) {
                $file_name = './assets/img/postPictures/uploaded_' . $postPhoto['name'];
                $upload_result = move_uploaded_file($postPhoto['tmp_name'], $file_name);
                if ($upload_result) $postPhotoUrl = $file_name;
            }
        }
    }
}

if (isset($_POST['post'])) {
    $postPhotoUrlHidden = $_POST['postPhotoUrlHidden'];

    if ($postTextContent == null && $postPhotoUrlHidden == 'none') $post_error = "Please upload an image or write something to post.";
    else {
        $stmt = $db->prepare('INSERT INTO post (postTextContent,postPhotoUrl,postUserID) VALUES (?,?,?)');
        $flag = $stmt->execute([
            isset($postTextContent) ? $postTextContent : 'noPostTextContent',
            $postPhotoUrlHidden,
            $_SESSION['userID']
        ]);
        if ($flag) header('location: ./home.php');
    }
}

// get the IDs of fiends
$stmt = $db->prepare('SELECT * FROM friends WHERE userID1 = ?');
$stmt->execute([$_SESSION['userID']]);
$result1 = $stmt->fetchAll(PDO::FETCH_OBJ);
$stmt = $db->prepare('SELECT * FROM friends WHERE userID2 = ?');
$stmt->execute([$_SESSION['userID']]);
$result2 = $stmt->fetchAll(PDO::FETCH_OBJ);
$friendIDs = [];
foreach ($result1 as $result) {
    array_push($friendIDs, $result->userID2);
}
foreach ($result2 as $result) {
    array_push($friendIDs, $result->userID1);
}

//include the current user in the friends to show his own posts
array_push($friendIDs, $_SESSION['userID']);

// get the posts posted by the IDs in the $friends array
$posts = [];
$stmt = $db->prepare('SELECT * FROM post WHERE postUserID = ?');
foreach ($friendIDs as $ID) {
    $stmt->execute([$ID]);
    $post = $stmt->fetchAll(PDO::FETCH_OBJ);
    array_push($posts, $post);
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>TSN | Home</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&amp;display=swap">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/css/Navbar-Right-Links-icons.css">
</head>

<body>
    <nav class="navbar navbar-light navbar-expand-md py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="./profile.php">
                <img style="width: 45px;height: 45px;border-radius: 50%;" src="<?= $_SESSION['profilePictureUrl'] ?>" />
                <span style="margin-left: 15px;"><?= $_SESSION['fullname'] ?></span>
            </a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navcol-2"><span class="visually-hidden">Toggle navigation</span><span class="navbar-toggler-icon"></span></button>
            <div id="navcol-2" class="collapse navbar-collapse">
                <form action="./search.php" method="get">
                    <input class="form-control form-control-sm" type="search" name="searchTerm" placeholder="Search friends..." style="width: 190px;height: 35px;padding-left: 20px;" />
                </form>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="./home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="./profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Messages</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Notifications</a></li>
                    <li class="nav-item"><a class="nav-link" href="./logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main style="background: var(--bs-gray-100);padding: 1% 20%;">

        <!-- NEW POST FORM -->
        <section id="New-Post" style="margin: 0px;">
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728" />
                <textarea class="form-control form-control-lg" name="postTextContent" placeholder="What&#39;s on your mind?"><?= isset($postTextContent) ? $postTextContent : '' ?></textarea>
                <div id="postPhotoDiv" style="display: <?= isset($postPhotoUrl) ? 'block' : 'none' ?>;">
                    <img width="120px" height="120px" id="postPhotoImg" src="<?= isset($postPhotoUrl) ? $postPhotoUrl : '' ?>" alt="" />
                </div>
                <div class="d-xl-flex justify-content-between align-items-center align-items-xl-center" style="margin-top: 6px;">
                    <input type="hidden" name="postPhotoUrlHidden" value="<?= isset($postPhotoUrl) ? $postPhotoUrl : 'none' ?>">
                    <label class="form-label" for="postPhoto" style="cursor: pointer;margin: 0px;">Upload Image</label>
                    <input id="postPhoto" class="form-control" type="file" name="postPhoto" style="display: none;" onchange="this.form.submit()" />
                    <button class="btn btn-primary btn-sm" type="submit" name='post' style="padding-right: 13px;padding-left: 13px;">Post</button>
                </div>
            </form>
        </section>
        <!-- FEED SECTION -->
        <section>
            <?php foreach ($posts as $post) : ?>
                <?php
                if (empty($post)) continue;
                foreach ($post as $p) :
                    // getting the post user profile picture and name
                    $stmt = $db->prepare('SELECT * FROM users WHERE userID = ?');
                    $stmt->execute([$p->postUserID]);
                    $postUser = $stmt->fetch(PDO::FETCH_OBJ);
                ?>
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class="d-flex flex-row justify-content-start align-items-center" style="margin-bottom: 19px;">
                                <img src="<?= $postUser->profilePictureUrl ?>" style="width: 30px;height: 30px;border-radius: 50%;" />
                                <span style="margin-left: 11px;"><?= $postUser->fullname ?></span>
                            </div>
                            <p class="card-text">
                                <?= $p->postTextContent == 'noPostTextContent' ? '' : $p->postTextContent
                                ?>
                            </p>
                            <?= $p->postPhotoUrl == 'none' ? '' : "<img src='$p->postPhotoUrl' style='width: 100%;height: 500px;' />" ?>
                            <div class="d-flex" style="margin-top: 20px;">
                                <button class="btn btn-primary d-xl-flex justify-content-xl-center align-items-xl-center" type="button" style="width: -0;height: 0;margin-right: 12px;" onclick="handleLike(this, <?= $p->postID ?>)">
                                    <i class="far fa-thumbs-up"></i><span style="margin-left: 5px;" id="<?= $p->postID ?>"><?= $p->likesCount ?></span>
                                </button>
                                <button class="btn btn-primary d-xl-flex justify-content-xl-center align-items-xl-center" type="button" style="width: -0;height: 0;">
                                    <i class="far fa-comment-alt"></i><span style="margin-left: 5px;"><?= $p->commentsCount ?></span>
                                </button>
                            </div>
                            <div style="margin-top: 24px;display:none">
                                <form><input class="form-control" type="text" placeholder="Comment..." /></form>
                            </div>
                        </div>
                    </div>
            <?php endforeach;
            endforeach; ?>
        </section>
    </main>

    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/bs-init.js"></script>
    <script>
        <?= isset($post_error) ? "alert('$post_error')" : "" ?>

        function handleLike(button, postID) {
            button.setAttribute('onclick', 'handleUnlike(this, <?= $p->postID ?>)')
            // var xhttp = new XMLHttpRequest();
            // xhttp.open("GET", `./handleLike.php?postID=${postID}`, true);
            // xhttp.send();
        }

        function handleUnlike(button, postID) {
            button.setAttribute('onclick', 'handleLike(this, <?= $p->postID ?>)')
            // var xhttp = new XMLHttpRequest();
            // xhttp.open("GET", `./handleUnlike.php?postID=${postID}`, true);
            // xhttp.send();
        }
    </script>
</body>

</html>