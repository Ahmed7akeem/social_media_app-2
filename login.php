<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email =$_POST['email'];
    $password= $_POST['password'];
    $siteB_url= "http://localhost/last2/Bsite.php";
    $data = http_build_query([
        'email' => $email,
        'password' => $password
    ]);



    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded",
            'method'  => 'POST',
            'content' => $data
        ]
    ];

    
    $context = stream_context_create($options);
    $result = file_get_contents($siteB_url, false, $context);
    
    if ($result ==="valid") {
        $_SESSION['user_id'] =$email;
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid email or  password.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
        <div class="animation1">
        <?php if(isset($error)): 
            ?>
            <p style="color: red;"><?=$error ?></p>
        <?php endif; ?>
        <table><tr><td>
          <form method="POST">
            <h1 class="banner1">login</h1>
            <br>
            <div class="inputs">
                <input type="email" name="email" placeholder="email" required>
            </div>

            <div class="inputs">
                <input type="password" name="password" placeholder="password" required>
            </div>

            <button type="submit">log in</button>
            <br><br>

            <p>I dont have an account <a href="register.php">Register</a></p>
          </form>
    
        </table></td></tr>
    </div>

</body>
</html>