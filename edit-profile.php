<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE idusers = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$errors = [];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = trim($_POST['current_password'] ?? '');
    $current_username = trim($_POST['current_username'] ?? '');
    $current_email = trim($_POST['current_email'] ?? '');

    $new_username = trim($_POST['new_username'] ?? '');
    $new_email = trim($_POST['new_email'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $pic = $user['profile_picture'];

    $current_errors = [];
    
    if(empty($current_password)) {
        $current_errors[] = "Current password is required.";
    }elseif(!password_verify($current_password, $user['password'])) {
        $current_errors[] = "Current password is incorrect.";
    }
    
    if(empty($current_username)) {
        $current_errors[] = "Current username is required.";
    }elseif($current_username !== $user['username']) {
        $current_errors[] = "Current username doesn't match.";
    }
    
    if(empty($current_email)) {
        $current_errors[] = "Current email is required.";
    }elseif($current_email !== $user['email']) {
        $current_errors[] = "Current email doesn't match.";
    }
    
    if(!empty($current_errors)) {
        $errors = array_merge($errors, $current_errors);
    }

    //========================ifcurrent info0 is valid
    if(empty($errors)) {
        if(empty($new_username)) {
            $errors[] = "New username is required.";
        }elseif(!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $new_username)) {
            $errors[] = "Username must be 3-20 characters (letters, numbers, underscores).";
        } else{
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND idusers != ?");
            $stmt->execute([$new_username, $user_id]);
            if($stmt->fetch()) {
                $errors[] = "Username already taken.";
            }
        }

        
        if(empty($new_email)) {
            $errors[] = "New email is required.";
        }elseif(!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        } else{
            $email_parts = explode('@', $new_email);
            $domain = array_pop($email_parts);
            if(!checkdnsrr($domain, 'MX')) {
                $errors[] = "Email domain is invalid.";
            } else{
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND idusers != ?");
                $stmt->execute([$new_email, $user_id]);
                if($stmt->fetch()) {
                    $errors[] = "Email already in use.";
                }
            }
        }

        
        $password = $user['password'];
        if(!empty($new_password)) {
            if(strlen($new_password) < 1) {
                $errors[] = "Password must be at least 1 characters.";
             
            } 
            else{
                $password = password_hash($new_password, PASSWORD_DEFAULT);
            }
        }

        if(isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
            $fileType = $_FILES['profile_picture']['type'];
            
            if(!array_key_exists($fileType, $allowedTypes)) {
                $errors[] = "Only JPG, PNG, and GIF files are allowed.";
            }elseif($_FILES['profile_picture']['size'] > 2 * 1024 * 1024) {
                $errors[] = "File size must be less than 2MB.";
            } else{
                $uploadDir = 'uploads/';
                $filename = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
                $targetPath = $uploadDir . $filename;
                
                if(move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath)) {
            // ==============to delete old picture 
            if($user['profile_picture'] && file_exists($user['profile_picture'])) {
                        unlink($user['profile_picture']);
                    }
                    $pic = $targetPath;
                } else{
                    $errors[] = "Error uploading profile picture.";
                }
            }
        }

        if(empty($errors)) {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ?, profile_picture = ? WHERE idusers = ?");
            if($stmt->execute([$new_username, $new_email, $password, $pic, $user_id])) {
                if($new_username !== $user['username']) {
                    $_SESSION['username'] = $new_username;
                }
                header("Location: profile.php");
                exit;
            } else{
                $errors[] = "Failed to update profile.";
            }
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
    <link rel="stylesheet" href="forms-styles.css">
  
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