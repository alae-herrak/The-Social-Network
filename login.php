<?php
session_start();
if (isset($_SESSION['userID'])) header('location: ./home.php');
require './db.php';
if (isset($_POST['login'])) {
    $username = (isset($_POST['username'])) ? $_POST['username'] : '';
    $password = (isset($_POST['password'])) ? $_POST['password'] : '';
    if (empty($username) || empty($password)) $error = 'Please fill in all the fields.';
    else {
        $stmt = $db->prepare("SELECT * FROM users WHERE username=? AND password=?");
        $stmt->execute([$username, $password]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        if (!$user) $error = 'Incorrect username or password.';
        else {
            $_SESSION['userID'] = $user->userID;
            $_SESSION['fullname'] = $user->fullname;
            $_SESSION['username'] = $user->username;
            $_SESSION['profilePictureUrl'] = $user->profilePictureUrl;
            header('location: ./home.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>TSN | Login</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&amp;display=swap">
    <link rel="stylesheet" href="assets/css/Navbar-Right-Links-icons.css">
</head>

<body>
    <nav class="navbar navbar-light navbar-expand-md py-3">
        <div class="container"><a class="navbar-brand d-flex align-items-center" href="#"><span>The Social Network</span></a><button data-bs-toggle="collapse" class="navbar-toggler" data-bs-target="#navcol-2"><span class="visually-hidden">Toggle navigation</span><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navcol-2">
                <ul class="navbar-nav ms-auto"></ul>
                <a class="btn btn-primary ms-md-2" role="button" href="./signup.php" style="background: rgb(217, 227, 241);color: rgb(123, 138, 184);">Signup</a>
                <a class="btn btn-primary ms-md-2" role="button" href="./login.php" style="background: var(--bs-navbar-color);color: var(--bs-gray-100);">Login</a>
            </div>
        </div>
    </nav>
    <section class="position-relative py-4 py-xl-5">
        <div class="container">
            <div class="row d-flex justify-content-center">
                <div class="col-md-6 col-xl-4">
                    <div class="card mb-5">
                        <div class="card-body d-flex flex-column align-items-center">
                            <h1 class="pb-3">Login</h1>
                            <form class="text-center" method="post">
                                <div class="mb-3"><input class="form-control" type="text" name="username" placeholder="Username" required="" ></div>
                                <div class="mb-3"><input class="form-control" type="password" name="password" placeholder="Password" required=""></div>
                                <div class="mb-3"><button class="btn btn-primary d-block w-100" type="submit" name="login" style="background: rgb(217, 227, 241);color: rgb(123, 138, 184);">Login</button></div>
                                <p class="text-danger"><?= isset($error) ? $error : '' ?></p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/bs-init.js"></script>
</body>

</html>