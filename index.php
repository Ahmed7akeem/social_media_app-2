<?php
session_start();
require 'callBsiteAPI.php';
require 'db.php';  

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$email = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['content'])) {
        callBsiteAPI([
            'action' => 'add_post',
            'email' => $email,
            'content' => $_POST['content']
        ]);
    } elseif (isset($_POST['delete_post'])) {
        callBsiteAPI([
            'action' => 'delete_post',
            'email' => $email,
            'post_id' => $_POST['post_id']
        ]);
    } elseif (isset($_POST['edit_post'])) {
        callBsiteAPI([
            'action' => 'edit_post',
            'email' => $email,
            'post_id' => $_POST['post_id'],
            'new_content' => $_POST['edit_content']
        ]);
    } elseif (isset($_POST['comment_content'])) {
        callBsiteAPI([
            'action' => 'add_comment',
            'email' => $email,
            'post_id' => $_POST['post_id'],
            'comment' => $_POST['comment_content']
        ]);
    } elseif (isset($_POST['delete_comment'])) {
        callBsiteAPI([
            'action' => 'delete_comment',
            'email' => $email,
            'comment_id' => $_POST['comment_id']
        ]);
    } elseif (isset($_POST['edit_comment'])) {
        callBsiteAPI([
            'action' => 'edit_comment',
            'email' => $email,
            'comment_id' => $_POST['comment_id'],
            'new_content' => $_POST['edit_comment_content']
        ]);
    }
    header("Location: index.php");
    exit;
}
$stmt = $pdo->query("SELECT posts.*, users.username FROM posts JOIN users ON posts.idusers = users.idusers ORDER BY posts.created_at DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hakeem | Timeline</title>
    <link rel="stylesheet" href="s.css">

    <style>
        .edit-form {
            display: none;
        }
    </style>

    <script>
        function toggleEdit(id) {
            const form = document.getElementById(id);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <div class="animation1">
        <div class="header">
            <h1 style="text-align:center;">Welcome to HAKEEM</h1>
            <nav style="text-align:center;">
                <a href="profile.php">Profile</a> |
                <a href="logout.php">Logout</a> |
                <a href="admin.php">Admin</a>
            </nav>
        </div>

        <!-- create post -->
        <div class="txtarea">
            <h2 class="lable1">What's on your mind?</h2>
            <form method="POST">
                <textarea name="content" id="post-content" required placeholder="Say anything"></textarea>
                <button type="submit">Post</button>
            </form>
        </div>

        <!-- Display post -->
        <div id="posts-container">
            <h2 style="text-align: center;">Latest Posts</h2>

            <?php foreach ($posts as $post): ?>
                <div class="thepost">
                    <h4><?= htmlspecialchars($post['username']) ?>:</h4>
                    <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

                    <?php
                    $stmtUser = $pdo->prepare("SELECT idusers FROM users WHERE email = ?");
                    $stmtUser->execute([$email]);
                    $currentUser = $stmtUser->fetch();
                    $ownPost = $post['idusers'] == $currentUser['idusers'];
                    ?>

                    <?php if ($ownPost): ?>
                        <!-- Post actions -->
                        <div class="actions">
                            <button type="button" onclick="toggleEdit('edit-post-<?= $post['id'] ?>')">Edit</button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <button name="delete_post" onclick="return confirm('Delete this post?')">Delete</button>
                            </form>
                        </div>

<!-- =======================================================Edit form -->
                        <form id="edit-post-<?= $post['id'] ?>" class="edit-form" method="POST">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <textarea name="edit_content" required><?= htmlspecialchars($post['content']) ?></textarea>
                            <button name="edit_post">Save</button>
                        </form>
                    <?php endif; ?>

                    <!-- Comments -->
                    <div class="comment-part">
                        <h4>Comments:</h4>

                        <?php
                        $stmtC = $pdo->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.idusers = users.idusers WHERE post_id = ?");
                        $stmtC->execute([$post['id']]);
                        $comments = $stmtC->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <?php foreach ($comments as $comment): ?>
                            <div class="comment" style="margin-bottom:10px;">
                                <p><strong><?= htmlspecialchars($comment['username']) ?>:</strong> <?= htmlspecialchars($comment['content']) ?></p>

                                <?php
                                $ownComment = $comment['idusers'] == $currentUser['idusers'];
                                if ($ownComment): ?>
                                    <div class="actions">
                                        <button type="button" onclick="toggleEdit('edit-comment-<?= $comment['id'] ?>')">Edit</button>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                            <button name="delete_comment" onclick="return confirm('Delete this comment?')">Delete</button>
                                        </form>
                                    </div>

<!-- =======================================================Edit Comment Form -->
                                    <form id="edit-comment-<?= $comment['id'] ?>" class="edit-form" method="POST">
                                        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                        <textarea name="edit_comment_content"><?= htmlspecialchars($comment['content']) ?></textarea>
                                        <button name="edit_comment">Save</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
<!-- =======================================================Add Comment --> 
                        <form method="POST">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <textarea name="comment_content" class="comment_area" placeholder="Add a comment..." required></textarea>
                            <button type="submit">Comment</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>