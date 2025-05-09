<?php
require 'callBsiteAPI.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $profile_picture = '';

    
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileType = mime_content_type($fileTmpPath); 
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

        if (!in_array($fileType, $allowedTypes)) {
            $error = "Only JPG, PNG, or GIF files are allowed.";
        } elseif ($_FILES['profile_picture']['size'] > (2 * 1024 * 1024)) {
            $error = "File must be less than 2MB.";
        } else {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755);
            $filename = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($fileTmpPath, $targetPath)) {
                $profile_picture = $targetPath;
            } else {
                $error = "Upload failed.";
            }
        }
    } else {
        $error = "Profile picture is required.";
    }
    if (empty($error)) {
        $response = callBsiteAPI([
            'action' => 'register_user',
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'profile_picture' => $profile_picture
        ]);
        if ($response === 'success') {
            header("Location: login.php");
            exit;
        } elseif ($response === 'exists') {
            $error = "Username or email already exists.";
        } else {
            $error = "Registration failed.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
        <div class="animation1">
        <?php if(isset($error)): ?>
            <p style="color: red;"><?= $error ?></p>
        <?php endif; ?>
        <table><tr><td>
        <form method="POST" enctype="multipart/form-data">
            <h1 class="banner1">Welcome to </h1>
            <h4 class="banner2">HAKEEM</h4>

            <div class="inputs">
                <input type="text" name="username" placeholder="Username" required>
            </div>

            <div class="inputs">
                <input type="email" name="email" placeholder="email" required>
            </div>

            <div class="inputs">
                <input type="password" name="password" placeholder="password">
            </div>

            <div class="inputs">
                <input type="file" name="profile_picture" placeholder="profile picture"required>
            </div>

            <div class="inputs">
                <label for="role">Role:</label>
                <select name="role" id="role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button type="submit">sign up</button>
            <br><br>

            <p>already have an account? <a href="login.php">Login</a></p>
        </form>
    
        </table></td></tr>
    </div>
</body>
</html>