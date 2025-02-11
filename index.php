<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// =====================insert new post
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content= $_POST['content'];
    $user_id= $_SESSION['user_id'];
    $stmt= $pdo->prepare("INSERT INTO posts (idusers, content) VALUES (?, ?)");
    $stmt->execute([$user_id, $content]);
    header("Location: index.php");
    exit;
}

// =============================insert new comment
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content']) && isset($_POST['post_id'])) {
    $comment_content =$_POST['comment_content'];
    $post_id= $_POST['post_id'];
    $user_id= $_SESSION['user_id'];
    $stmt= $pdo->prepare("INSERT INTO comments (idusers, post_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $post_id, $comment_content]);
    header("Location: index.php");
    exit;
}

// =================post delete
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post'])) {
    $post_id=$_POST['post_id'];
    $stmt=$pdo->prepare("DELETE FROM posts WHERE id= ?");
    $stmt->execute([$post_id]);
    header("Location: index.php");
    exit;
}

// ============================== post editing
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_post'])) {
    $post_id=$_POST['post_id'];
    $new_content=$_POST['edit_content'];
    $stmt=$pdo->prepare("UPDATE posts SET content=? WHERE id= ?");
    $stmt->execute([$new_content, $post_id]);
    header("Location: index.php");
    exit;
}

//============================= delete comment 
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
    $comment_id=$_POST['comment_id'];
    $stmt=$pdo->prepare("DELETE FROM comments WHERE id= ?");
    $stmt->execute([$comment_id]);
    header("Location: index.php");
    exit;
}

// =======================editing comment 
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_comment'])) {
    $comment_id=$_POST['comment_id'];
    $new_content=$_POST['edit_comment_content'];
    $stmt=$pdo->prepare("UPDATE comments SET content=? WHERE id=?");
    $stmt->execute([$new_content, $comment_id]);
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="s.css" />
    <title>hakeem</title>
    <style>
        .edit-form {
            display: none; 
        }
    </style>
    <script>
        function toggleEditForm(formId) {
            const form=document.getElementById(formId);
            form.style.display=form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <div class="animation1">
        <div class="header">
            <div class="wave-container">
                <h1 class="wave-text">
                    <span>H</span><span>A</span><span>K</span><span>E</span
                    ><span>E</span><span>M</span>
                </h1>
            </div>
        </div>

        
            <a href="profile.php">profile</a>
            <a href="logout.php">logout</a>
            <a href="admin.php">admin page</a>
        <div class="txtarea">
            <h2 class="lable1">What's on your mind?</h2>
            <form method="POST" action="index.php">
                <textarea
                    id="post-content"
                    name="content"
                    placeholder="Write your post here..."
                    required
                ></textarea>
                <br />
                <button type="submit">Create Post</button>
            </form>
        </div>
<!--=========================================================posts===================================-->
        <div id="posts-container">
            <h2>POSTS</h2>
            <?php
            //=============================display posts
            $stmt=$pdo->query("SELECT posts.*, users.username FROM posts JOIN users ON posts.idusers=users.idusers ORDER BY created_at DESC");
            $posts=$stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($posts as $post): ?>
                <div class="thepost">
                    <h2>Post by <?= htmlspecialchars($post['username']) ?>:</h2>
                    <div class="paragraph">
                        <p><?= htmlspecialchars($post['content']) ?></p>
                    </div>
                    <?php if($_SESSION['user_id'] == $post['idusers']): ?>
                        <div class="actions">
                            <button onclick="toggleEditForm('edit-post-form-<?=$post['id'] ?>')">Edit</button>
                            <form method="POST" action="index.php" style="display:inline;">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <input type="hidden" name="delete_post" value="1">
                                <button type="submit">Delete</button>
                            </form>
                        </div>
                        <!-- =========== edit post======= -->
                        <form id="edit-post-form-<?= $post['id'] ?>" class="edit-form" method="POST" action="index.php">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <textarea name="edit_content"><?= htmlspecialchars($post['content']) ?></textarea>
                            <button type="submit" name="edit_post">Save Changes</button>
                        </form>
                    <?php endif; ?>
<!--=====================================================comment=================-->
                    <div class="comment-part">
                        <h5>Comments:</h5>
                        <?php
                        // get all the comment of the post
                        $stmt=$pdo->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.idusers=users.idusers WHERE post_id=? ORDER BY created_at DESC");
                        $stmt->execute([$post['id']]);
                        $comments=$stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($comments as $comment): ?>
                            <div class="comment">
                                <p><strong><?= htmlspecialchars($comment['username']) ?>:</strong> <?= htmlspecialchars($comment['content']) ?></p>
                                <?php if($_SESSION['user_id'] == $comment['idusers']): ?>
                                    <div class="actions">
                                        <button onclick="toggleEditForm('edit-comment-form-<?= $comment['id'] ?>')">Edit-comment</button>
                                        <form method="POST" action="index.php" style="display:inline;">
                                            <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                            <input type="hidden" name="delete_comment" value="1">
                                            <button type="submit">Delete-comment</button>
                                        </form>
                                    </div>
                                    <!--=========== Edit Comment Form============ -->
                                    <form id="edit-comment-form-<?= $comment['id'] ?>" class="edit-form" method="POST" action="index.php">
                                        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                        <textarea name="edit_comment_content"><?= htmlspecialchars($comment['content']) ?></textarea>
                                        <button type="submit" name="edit_comment">Save Changes</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <form method="POST" action="index.php">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <textarea name="comment_content" class="comment_area" placeholder="Add a comment..." required></textarea>
                            <button type="submit">add comment</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>