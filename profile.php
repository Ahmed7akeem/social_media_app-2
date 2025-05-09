<?php
session_start();
require 'callBsiteAPI.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$response = callBsiteAPI([
    'action' => 'get_user',
    'email' => $_SESSION['user_id']
]);
if ($response === 'invalid') {
    session_destroy();
    header("Location: login.php");
    exit;
}
$user = json_decode($response, true);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Profile</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <div class="animation1"> 
<a href="edit-profile.php">edit the profile</a>
<div class="colorr">
    <?php 
echo '<p>user name: '.htmlspecialchars($user['username']).'</p>';
echo '<p>email: '.htmlspecialchars($user['email']).'</p>';
echo '<p> profile pic: '.htmlspecialchars($user['profile_picture']).'</p>';
?>
<img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture" width="100px" >
</div>
</div>
</body>
</html>