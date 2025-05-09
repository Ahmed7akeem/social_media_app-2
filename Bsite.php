<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    switch ($action) {
        case 'login':
            $email = $_POST['email'];
            $password = $_POST['password'];
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo $user && password_verify($password, $user['password']) ? 'valid' : 'invalid';
            break;

        case 'get_user':
            $email = $_POST['email'] ?? '';
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo $user ? json_encode($user) : 'invalid';
            break;

        case 'add_post':
            $email = $_POST['email'];
            $content = $_POST['content'];
            $stmt = $pdo->prepare("SELECT idusers FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            $stmt = $pdo->prepare("INSERT INTO posts (idusers, content) VALUES (?, ?)");
            $stmt->execute([$user['idusers'], $content]);
            echo 'success';
            break;

        case 'register_user':
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $profile_picture = $_POST['profile_picture'] ?? '';
            $role = $_POST['role'] ?? 'user';

            if (empty($username) || empty($email) || empty($password)) {
                echo 'missing_fields';
                break;
            }

            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                echo 'exists';
                break;
            }

            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, profile_picture, role) VALUES (?, ?, ?, ?, ?)");
            $success = $stmt->execute([
                $username,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                $profile_picture,
                $role
            ]);

            echo $success ? 'success' : 'fail';
            break;

        case 'edit_post':
            $email = $_POST['email'];
            $post_id = $_POST['post_id'];
            $new_content = $_POST['new_content'];
            $stmt = $pdo->prepare("SELECT idusers FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            $stmt = $pdo->prepare("UPDATE posts SET content = ? WHERE id = ? AND idusers = ?");
            $stmt->execute([$new_content, $post_id, $user['idusers']]);
            echo 'success';
            break;

        case 'delete_post':
            $email = $_POST['email'];
            $post_id = $_POST['post_id'];
            $stmt = $pdo->prepare("SELECT idusers FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND idusers = ?");
            $stmt->execute([$post_id, $user['idusers']]);
            echo 'success';
            break;

        case 'add_comment':
            $email = $_POST['email'];
            $post_id = $_POST['post_id'];
            $comment = $_POST['comment'];
            $stmt = $pdo->prepare("SELECT idusers FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            $stmt = $pdo->prepare("INSERT INTO comments (idusers, post_id, content) VALUES (?, ?, ?)");
            $stmt->execute([$user['idusers'], $post_id, $comment]);
            echo 'success';
            break;

        case 'edit_comment':
            $email = $_POST['email'];
            $comment_id = $_POST['comment_id'];
            $new_content = $_POST['new_content'];
            $stmt = $pdo->prepare("SELECT idusers FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            $stmt = $pdo->prepare("UPDATE comments SET content = ? WHERE id = ? AND idusers = ?");
            $stmt->execute([$new_content, $comment_id, $user['idusers']]);
            echo 'success';
            break;

        case 'delete_comment':
            $email = $_POST['email'];
            $comment_id = $_POST['comment_id'];
            $stmt = $pdo->prepare("SELECT idusers FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND idusers = ?");
            $stmt->execute([$comment_id, $user['idusers']]);
            echo 'success';
            break;

        case 'update_user':
            $email = $_POST['email'];
            $new_username = $_POST['new_username'];
            $new_email = $_POST['new_email'];
            $new_password = !empty($_POST['new_password']) ? password_hash($_POST['new_password'], PASSWORD_DEFAULT) : null;

            $stmt = $pdo->prepare("SELECT idusers FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($new_password) {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE idusers = ?");
                $stmt->execute([$new_username, $new_email, $new_password, $user['idusers']]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE idusers = ?");
                $stmt->execute([$new_username, $new_email, $user['idusers']]);
            }

            echo 'success';
            break;

                
        case 'get_user_posts':
            $idusers = $_POST['idusers'];
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE idusers = ?");
            $stmt->execute([$idusers]);
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($posts);
            break;

        
        case 'get_user_comments':
            $idusers = $_POST['idusers'];
            $stmt = $pdo->prepare("SELECT * FROM comments WHERE idusers = ?");
            $stmt->execute([$idusers]);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($comments);
            break;

        case 'delete_user':
            $user_id = $_POST['user_id'] ?? '';
            $stmt = $pdo->prepare("DELETE FROM users WHERE idusers = ?");
            $stmt->execute([$user_id]);
            echo 'success';
            break;

        case 'update_profile':
            $email = $_POST['email'];
            $new_username = $_POST['new_username'];
            $new_email = $_POST['new_email'];
            $new_password = !empty($_POST['new_password']) ? password_hash($_POST['new_password'], PASSWORD_DEFAULT) : null;
            $profile_picture = $_POST['profile_picture'] ?? '';

            $stmt = $pdo->prepare("SELECT idusers FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                echo 'invalid_user';
                break;
            }

            if ($new_password) {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ?, profile_picture = ? WHERE idusers = ?");
                $success = $stmt->execute([$new_username, $new_email, $new_password, $profile_picture, $user['idusers']]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, profile_picture = ? WHERE idusers = ?");
                $success = $stmt->execute([$new_username, $new_email, $profile_picture, $user['idusers']]);
            }

            echo $success ? 'success' : 'fail';
            break;

        default:
            echo 'invalid_action';
    }
}
?>
