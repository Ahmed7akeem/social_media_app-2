<?php
session_start();
require 'db.php'; 
require 'callBsiteAPI.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$admin = json_decode(callBsiteAPI([
    'action' => 'get_user',
    'email' => $_SESSION['user_id']
]), true);

if (!$admin || $admin['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
$edit_user_id = $_GET['idusers'] ?? null;
if (!$edit_user_id || !is_numeric($edit_user_id)) {
    header("Location: admin.php");
    exit;
}
$stmt = $pdo->prepare("SELECT * FROM users WHERE idusers = ?");
$stmt->execute([$edit_user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Error: User not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_user'])) {
        callBsiteAPI([
            'action' => 'update_user',
            'email' => $user['email'],
            'new_username' => $_POST['username'],
            'new_email' => $_POST['email'],
            'new_password' => ''
        ]);
        header("Location: admin.php");
        exit;
    }
    if (isset($_POST['delete_post'])) {
        callBsiteAPI([
            'action' => 'delete_post',
            'email' => $user['email'],
            'post_id' => $_POST['post_id']
        ]);
        header("Location: admin-edit-user.php?idusers={$edit_user_id}");
        exit;
    }
    if (isset($_POST['delete_comment'])) {
        callBsiteAPI([
            'action' => 'delete_comment',
            'email' => $user['email'],
            'comment_id' => $_POST['comment_id']
        ]);
        header("Location: admin-edit-user.php?idusers={$edit_user_id}");
        exit;
    }
}

$user_posts = json_decode(callBsiteAPI([
    'action' => 'get_user_posts',
    'idusers' => $edit_user_id
]), true);


$user_comments = json_decode(callBsiteAPI([
    'action' => 'get_user_comments',
    'idusers' => $edit_user_id
]), true);
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