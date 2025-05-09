<?php
session_start();
require 'callBsiteAPI.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['user_id'];
$errors = [];
$uploadedPicPath = '';

$response = callBsiteAPI([
    'action' => 'get_user',
    'email' => $email
]);
$user = json_decode($response, true);
if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = trim($_POST['current_password'] ?? '');
    $current_username = trim($_POST['current_username'] ?? '');
    $current_email = trim($_POST['current_email'] ?? '');
    $new_username = trim($_POST['new_username'] ?? '');
    $new_email = trim($_POST['new_email'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $uploadedPicPath = $user['profile_picture'];

    if (empty($current_password) || !password_verify($current_password, $user['password']))
        $errors[] = "Current password is incorrect.";
    if (empty($current_username) || $current_username !== $user['username'])
        $errors[] = "Current username doesn't match.";
    if (empty($current_email) || $current_email !== $user['email'])
        $errors[] = "Current email doesn't match.";
    if (empty($new_username) || !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $new_username))
        $errors[] = "New username must be 3-20 chars using letters/numbers/_";
    if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL))
        $errors[] = "Invalid new email format.";


        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['profile_picture']['type'];

        if (!in_array($fileType, array_keys($allowed))) {
            $errors[] = "File must be jpg, png, or gif.";
        } elseif ($_FILES['profile_picture']['size'] > 2 * 1024 * 1024) {
            $errors[] = "File must be under 2MB.";
        } else {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir);

            $filename = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
            $target = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target)) {
                if ($user['profile_picture'] && file_exists($user['profile_picture'])) {
                    unlink($user['profile_picture']);
                }
                $uploadedPicPath = $target;
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }
    if (empty($errors)) {
        $update = callBsiteAPI([
            'action' => 'update_profile',
            'email' => $email,
            'new_username' => $new_username,
            'new_email' => $new_email,
            'new_password' => $new_password,
            'profile_picture' => $uploadedPicPath
        ]);

        
        if ($update === 'success') {
            $_SESSION['user_id'] = $new_email;
            header("Location: profile.php");
            exit;
        } else {
            $errors[] = "Failed to update profile in server.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>edit profile</title>
    <link rel="stylesheet" href="styles.css">
  
</head>
<body>
    <header>
        <h1>edit profile</h1>
        <nav>
            <a href="profile.php">Profile</a>
            <a href="index.php">Home</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <?php if(!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="animation1">
        <form method="POST" enctype="multipart/form-data">
            <h3>Current Information</h3>
            <div class="inputs">
                <label>Current Password:</label>
                <input type="password" name="current_password" required>
            </div>
            
            <div class="inputs">
                <label>Current Username:</label>
                <input type="text" name="current_username" required>
            </div>
            
            <div class="inputs">
                <label>Current Email:</label>
                <input type="email" name="current_email" required>
            </div>

            <h3>New Information</h3>
            <div class="inputs">
                <label>New Username:</label>
                <input type="text" name="new_username" 
                       value="<?= htmlspecialchars($new_username ?? $user['username']) ?>" 
                       required
                       pattern="[a-zA-Z0-9_]{3,20}"
                       title="3-20 char(letters | numbers| underscores)"><br>
                <small>allowed char: a-z, A-Z, 0-9, _</small>
            </div>

            <div class="inputs">
                <label>New Email:</label>
                <input type="email" name="new_email" 
                       value="<?= htmlspecialchars($new_email ?? $user['email']) ?>" 
                       required>
            </div>

            <div class="inputs">
                <label>New Password:</label>
                <input type="password" name="new_password" 
                       placeholder="Leave blank to keep current"
                       
            </div>

            <div class="inputs">
                <label>Profile Picture:</label>
                <input type="file" name="profile_picture">
                <?php if($user['profile_picture']): ?>
                    <small>Current: <?= htmlspecialchars(basename($user['profile_picture'])) ?></small>
                <?php endif; ?>
            </div>

            <button type="submit">Update Profile</button>
        </form>
    </div>
</body>
</html>