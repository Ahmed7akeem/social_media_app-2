<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

//==========================user delete
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['delete_user'])) {
    $user_id_to_delete=$_POST['user_id'];
    if($user_id_to_delete != $_SESSION['user_id']) {
        $stmt=$pdo->prepare("DELETE FROM users WHERE idusers =?");
        $stmt->execute([$user_id_to_delete]);
        header("Location: admin.php");
        exit;
    }
}

// ===============================retrive all users
$stmt= $pdo->query("SELECT * FROM users");
$users= $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Admin page</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="animation1">
        <h2>User Management</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['idusers'] ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= $user['role'] ?></td>
                <td>
                    <a href="admin-edit-user.php?idusers=<?= $user['idusers'] ?>">Edit</a>
                    <form method="POST" style="display:inline;">
                    <input type="hidden" name="user_id" value="<?= $user['idusers'] ?>">
                    <input type="submit" name="delete_user" value="Delete" 
                               onclick="return confirm('Are you sure?')">
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>