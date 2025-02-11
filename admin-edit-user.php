<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !=='admin') {
    header("Location: login.php");
    exit;
}

$edit_user_id =$_GET['idusers'] ?? null;
if(!$edit_user_id) {
    header("Location: admin.php");
    exit;
}

// ======================================get user data
$stmt =$pdo->prepare("SELECT * FROM users WHERE idusers =?");
$stmt->execute([$edit_user_id]);
$user =$stmt->fetch(PDO::FETCH_ASSOC);

//============================post delete
if($_SERVER['REQUEST_METHOD'] ==='POST' && isset($_POST['delete_post'])) {
    $post_id =$_POST['post_id'];
    $stmt =$pdo->prepare("DELETE FROM posts WHERE id =?");
    $stmt->execute([$post_id]);
    header("Location: admin-edit-user.php?idusers=" . $edit_user_id);
    exit;
}

// ================================comment delet
if($_SERVER['REQUEST_METHOD'] ==='POST' && isset($_POST['delete_comment'])) {
    $comment_id =$_POST['comment_id'];
    $stmt =$pdo->prepare("DELETE FROM comments WHERE id =?");
    $stmt->execute([$comment_id]);
    header("Location: admin-edit-user.php? idusers =" . $edit_user_id);
    exit;
}

//================================to make user update
if($_SERVER['REQUEST_METHOD'] ==='POST' && isset($_POST['update_user'])) {
    $username =$_POST['username'];
    $email =$_POST['email'];
    $role =$_POST['role'];
    
    $stmt =$pdo->prepare("UPDATE users SET username=?, email=?, role=? WHERE idusers=?");
    $stmt->execute([$username, $email, $role, $edit_user_id]);
    header("Location: admin.php");
    exit;
}

// ======================================to get the  users posts
$stmt_posts =$pdo->prepare("SELECT * FROM posts WHERE idusers=?");
$stmt_posts->execute([$edit_user_id]);
$user_posts =$stmt_posts->fetchAll(PDO::FETCH_ASSOC);

// -=====================================to get th users comments
$stmt_comments =$pdo->prepare("SELECT * FROM comments WHERE idusers =?");
$stmt_comments->execute([$edit_user_id]);
$user_comments =$stmt_comments->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="animation1">
        <h2>Edit User</h2>
        <form method="POST">
            <div class="inputs">
                <input type="text" name="username" 
                       value="<?=htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="inputs">
                <input type="email" name="email" 
                       value="<?=htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="inputs">
                <select name="role">
                    <option value="user" <?=$user['role'] ==='user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?=$user['role'] ==='admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <button type="submit" name="update_user">Update User</button>
        </form>

        <!-- ==============================user posts part -->
        <h3>User's Posts</h3>
        <?php foreach ($user_posts as $post): ?>
            <div class="post">
                <p><?=htmlspecialchars($post['content']) ?></p>
                <form method="POST">
                    <input type="hidden" name="post_id" value="<?=$post['id'] ?>">
                    <input type="submit" name="delete_post" value="Delete Post" 
                           onclick="return confirm('Are you sure you want to delete this post?')">
                </form>
            </div>
        <?php endforeach; ?>

        <!-- ============================users comment part -->
        <h3>User's Comments</h3>
        <?php foreach ($user_comments as $comment): ?>
            <div class="comment">
                <p><?=htmlspecialchars($comment['content']) ?></p>
                <form method="POST">
                    <input type="hidden" name="comment_id" value="<?=$comment['id'] ?>">
                    <input type="submit" name="delete_comment" value="Delete Comment" 
                           onclick="return confirm('Are you sure you want to delete this comment?')">
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>