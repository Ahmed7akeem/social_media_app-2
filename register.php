<?php
require 'db.php';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username =$_POST['username'];
    $email =$_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role =$_POST['role']; 
    
    $profile_picture= '';
    if(isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir='uploads/';
        if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $filename=basename($_FILES['profile_picture']['name']);
        $targetPath=$uploadDir . $filename;
        if(move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath)) {
            $profile_picture=$targetPath;
        }
    }
   
    $stmt=$pdo->prepare("SELECT * FROM users WHERE username=? OR email=?");
    $stmt->execute([$username, $email]);
    
    if($stmt->fetch()) {
        $error="username or email already exists.";
    }else{
        $stmt=$pdo->prepare("INSERT INTO users (username, email, password, profile_picture, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password, $profile_picture, $role]);
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="forms-styles.css">
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