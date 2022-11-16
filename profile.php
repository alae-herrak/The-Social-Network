<?php
session_start();
if (!isset($_SESSION['userID'])) header('location: ./login.php');
require './db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>TSN | <?= $_SESSION['fullname'] ?></title>
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
                    <input class="form-control form-control-sm" type="search" name="searchTerm" placeholder="Search friends..." style="width: 190px;height: 35px;padding-left: 20px;" />
                </form>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="./home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="./profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Messages</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Notifications</a></li>
                    <li class="nav-item"><a class="nav-link" href="./logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
</body>

<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/bs-init.js"></script>
</body>

</html>