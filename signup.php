<?php
session_start();
if (isset($_SESSION['userID'])) header('location: ./home.php');
require './db.php';
if (isset($_POST['signup'])) {
    $fullname = (isset($_POST['fullname'])) ? $_POST['fullname'] : '';
    $username = (isset($_POST['username'])) ? $_POST['username'] : '';
    $password = (isset($_POST['password'])) ? $_POST['password'] : '';
    $passwordR = (isset($_POST['passwordR'])) ? $_POST['passwordR'] : '';
    if (empty($fullname) || empty($username) || empty($password) || empty($passwordR)) $error = 'Please fill in all your information.';
    else {
        if (isset($_FILES['photo'])) {
            $photo = $_FILES['photo'];
            if ($photo['type'] != 'image/jpeg' && $photo['type'] != 'image/png') {
                $error = "Select an image file (JPEG/PNG)";
            } else {
                if ($photo['error'] == 2) {
                    $error = "Photo size is too big.";
                } else {
                    $file_name = './assets/img/profilePictures/uploaded_' . $photo['name'];
                    $upload_result = move_uploaded_file($photo['tmp_name'], $file_name);
                    if ($upload_result) {
                        $profilePictureUrl = $file_name;
                    } else {
                        $error = "There was an error uploading your photo.";
                    }
                }
            }
        }
        if ($password !== $passwordR) $error = "Passwords don't match.";
        else {
            $stmt = $db->prepare("SELECT * FROM users WHERE username=?");
            $stmt->execute([$username]);
            $test = $stmt->fetch(PDO::FETCH_OBJ);
            if ($test) $error = "Username already taken.";
            else {
                $stmt = $db->prepare('INSERT INTO users (fullname, username, password, profilePictureUrl) VALUES (?, ?, ?, ?)');
                $flag = $stmt->execute([
                    $fullname,
                    $username,
                    $password,
                    isset($profilePictureUrl)? $profilePictureUrl : './assets/img/profilePictures/noProfilePicture.png'
                ]);
                if ($flag){
                    $stmt = $db->prepare('SELECT * FROM users WHERE username=?');
                    $stmt->execute([$username]);
                    $user = $stmt->fetch(PDO::FETCH_OBJ);
                    $_SESSION['userID'] = $user->userID;
                    $_SESSION['fullname'] = $user->fullname;
                    $_SESSION['username'] = $user->username;
                    $_SESSION['profilePictureUrl'] = $user->profilePictureUrl;
                    header('location: ./home.php');
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>TSN | Signup</title>
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
                            <h1 class="pb-3">Signup</h1>
                            <form class="text-center" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="MAX_FILE_SIZE" value="3145728" />
                                <div class="mb-3"><input class="form-control" type="text" data-bs-toggle="tooltip" data-bss-tooltip="" data-bs-placement="bottom" name="fullname" placeholder="Full Name" required="" title="The name that your friends will see."></div>
                                <div class="mb-3"><input class="form-control" type="text" data-bs-toggle="tooltip" data-bss-tooltip="" data-bs-placement="bottom" name="username" placeholder="Username" required="" title="The username is used to login into your account."></div>
                                <div class="mb-3"><input class="form-control" type="file" data-bs-toggle="tooltip" data-bss-tooltip="" name="photo" title="Choose a profile picture."></div>
                                <div class="mb-3"><input class="form-control" type="password" name="password" placeholder="Password" required=""></div>
                                <div class="mb-3"><input class="form-control" type="password" data-bs-toggle="tooltip" data-bss-tooltip="" name="passwordR" placeholder="Password Repeat" title="Confirm your password." required=""></div>
                                <div class="mb-3"><button class="btn btn-primary d-block w-100" type="submit" name="signup" style="background: rgb(217, 227, 241);color: rgb(123, 138, 184);">Signup</button></div>
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