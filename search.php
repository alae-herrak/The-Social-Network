<?php
session_start();
if (!isset($_SESSION['userID'])) header('location: ./login.php');
require './db.php';
//===============================================================
if (isset($_GET['searchTerm'])) {
    $searchTerm = $_GET['searchTerm'];
    if (empty($searchTerm)) $empty_msg = 'Try typing something in the search field.';
    else {
        $stmt = $db->prepare('SELECT * FROM users WHERE fullname LIKE ? OR username LIKE ?');
        $stmt->execute(["%$searchTerm%", "%$searchTerm%"]);
        $searchResult = $stmt->fetchAll(PDO::FETCH_OBJ);
    }
} else header('location: ./home.php');
//===============================================================
if (isset($_POST['sendRequest'])) {
    $targetUserID = $_POST['targetUserID'];
    //check if already friends
    $stmt = $db->prepare('SELECT * FROM friends WHERE userID1 IN (?,?) AND userID2 in (?,?)');
    $stmt->execute([$targetUserID, $_SESSION['userID'], $targetUserID, $_SESSION['userID']]);
    $friends = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$friends) {
        //check if request already exists
        $stmt = $db->prepare('SELECT * FROM friendrequest WHERE targetUserID=? AND senderUserID=?');
        $stmt->execute([$targetUserID, $_SESSION['userID']]);
        $exists = $stmt->fetch(PDO::FETCH_OBJ);
        //send request only if it doesn't exist
        if (!$exists) {
            $stmt = $db->prepare('INSERT INTO friendrequest VALUES (?, ?, ?)');
            $stmt->execute([$targetUserID, $_SESSION['userID'], 1]);
        }
    }
}
//===============================================================
if (isset($_POST['cancelRequest'])) {
    $stmt = $db->prepare('DELETE FROM friendrequest WHERE targetUserID=? AND senderUserID=?');
    $stmt->execute([$_POST['targetUserID'], $_SESSION['userID']]);
}
//===============================================================
if (isset($_POST['rejectRequest'])) {
    $stmt = $db->prepare('DELETE FROM friendrequest WHERE targetUserID=? AND senderUserID=?');
    $stmt->execute([$_SESSION['userID'], $_POST['targetUserID']]);
}
//===============================================================
if (isset($_POST['confirmRequest'])) {
    $stmt = $db->prepare('INSERT INTO friends VALUES (?,?)');
    $stmt->execute([$_SESSION['userID'], $_POST['targetUserID']]);
    $stmt = $db->prepare('DELETE FROM friendrequest WHERE targetUserID=? AND senderUserID=?');
    $stmt->execute([$_SESSION['userID'], $_POST['targetUserID']]);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>TSN | Search</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&amp;display=swap">
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
                    <input class="form-control form-control-sm" type="search" name="searchTerm" placeholder="Search friends..." value="<?= isset($searchTerm) ? $searchTerm : '' ?>" style="width: 190px;height: 35px;padding-left: 20px;" />
                </form>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="./home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="./profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Messages</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Notifications</a></li>
                    <li class="nav-item"><a class="nav-link" href="./logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <section>
        <?php if (isset($empty_msg)) : ?>
            <h3><?= $empty_msg ?></h3>
        <?php else : ?>
            <h3>Showing search results for: <b><?= $searchTerm ?></b></h3>
            <?php if (count($searchResult) == 0) : ?>
                <span>Nothing matches your search.</span>
                <?php else :
                foreach ($searchResult as $searchResultUser) :
                    $stmt = $db->prepare('SELECT * FROM friendrequest WHERE targetUserID IN (?,?) AND senderUserID IN (?,?)');
                    $stmt->execute([$searchResultUser->userID, $_SESSION['userID'], $searchResultUser->userID, $_SESSION['userID']]);
                    $friendRequest = $stmt->fetch(PDO::FETCH_OBJ);
                    if ($friendRequest) {
                        $FRTreatment = $friendRequest->targetUserID == $_SESSION['userID'] ? 'confirmOrReject' : 'pending';
                    } else {
                        $stmt = $db->prepare('SELECT * FROM friends WHERE userID1 IN (?,?) AND userID2 in (?,?)');
                        $stmt->execute([$searchResultUser->userID, $_SESSION['userID'], $searchResultUser->userID, $_SESSION['userID']]);
                        $friends = $stmt->fetch(PDO::FETCH_OBJ);
                        if ($friends) $FRTreatment = 'friends';
                        else $FRTreatment = 'sendable';
                    }
                ?>
                    <div class="d-flex flex-column align-items-center" style="width: 100%;margin-top: 50px;">
                        <div class="row d-flex flex-row justify-content-between align-items-center" style="width: 500px;">
                            <div class="col">
                                <img style="width: 55px;height: 55px;border-radius: 50%;" src="<?= $searchResultUser->profilePictureUrl ?>" />
                                <span style="margin-left: 15px;"><?= $searchResultUser->fullname ?></span>
                            </div>
                            <?php if ($searchResultUser->userID != $_SESSION['userID']) : ?>
                                <div class="col d-lg-flex justify-content-lg-end">
                                    <?php if ($FRTreatment == 'sendable') : ?>
                                        <form action="" method="post" class="col d-lg-flex justify-content-lg-end">
                                            <input type="hidden" name="targetUserID" value="<?= $searchResultUser->userID ?>">
                                            <button class="btn btn-primary btn-sm" type="submit" name="sendRequest" style="padding-right: 14px;padding-left: 14px;">Add friend</button>
                                        </form>
                                    <?php elseif ($FRTreatment == 'pending') : ?>
                                        <form action="" method="post" class="col d-lg-flex justify-content-lg-end">
                                            <input type="hidden" name="targetUserID" value="<?= $friendRequest->targetUserID ?>">
                                            <button class="btn btn-primary btn-sm" type="submit" name="cancelRequest" style="padding-right: 14px;padding-left: 14px;">cancel</button>
                                        </form>
                                    <?php elseif ($FRTreatment == 'confirmOrReject') : ?>
                                        <form action="" method="post" class="col d-lg-flex justify-content-lg-end">
                                            <input type="hidden" name="targetUserID" value="<?= $searchResultUser->userID ?>">
                                            <button class="btn btn-primary btn-sm" type="submit" name="rejectRequest" style="padding-right: 14px;padding-left: 14px;">Reject</button>
                                            <button class="btn btn-primary btn-sm" type="submit" name="confirmRequest" style="padding-right: 14px;padding-left: 14px;">Confirm</button>
                                        </form>
                                    <?php else : ?>
                                        <div class="col d-lg-flex justify-content-lg-end">
                                            <button class="btn btn-primary btn-sm" type="button" name="" disabled style="padding-right: 14px;padding-left: 14px;">Friends</button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
        <?php endforeach;
            endif;
        endif; ?>
    </section>
</body>

<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/bs-init.js"></script>
</body>

</html>