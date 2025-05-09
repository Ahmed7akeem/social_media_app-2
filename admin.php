<?php
session_start();
require 'callBsiteAPI.php';
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$email = $_SESSION['user_id'];
$response = callBsiteAPI([
    'action' => 'get_user',
    'email' => $email
]);

if ($response === 'invalid') {
    session_destroy();
    header("Location: login.php");
    exit;
}
$user = json_decode($response, true);
if ($user['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    if ($user_id != $user['idusers']) {
        callBsiteAPI([
            'action' => 'delete_user',
            'user_id' => $user_id
        ]);
        header("Location: admin.php");
        exit;
    } else {
        $error = "You cannot delete yourself!";
    }
}

$stmt = $pdo->query("SELECT * FROM users");
$allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <h1 class="banner1">Admin Dashboard</h1>
    <nav>
        <a class="link" href="index.php">Home</a> |
        <a class="link" href="logout.php">Logout</a>
    </nav>
</header>

<div class="animation1">
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <h3 class="banner2">User Management</h3>

    <table>
        <thead>
        <tr>
            <th>#</th><th>Username</th><th>Email</th><th>Role</th><th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($allUsers as $u): ?>
            <tr style="text-align:center;">
                <td><?= $u['idusers'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= $u['role'] ?></td>
                <td>
                    <a class="link" href="admin-edit-user.php?idusers=<?= $u['idusers'] ?>">Edit</a>
                    <?php if ($u['idusers'] != $user['idusers']): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $u['idusers'] ?>">
                            <button name="delete_user" onclick="return confirm('Delete this user?')">Delete</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>