<?php
include 'db.config.php';
include 'classes.php';

$config = include 'db.config.php';
$pdo = new PDO("mysql:host={$config['app']['host']};dbname={$config['app']['dbname']}", $config['app']['username'], $config['app']['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$user = new User($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($user->authenticateUser($username, $password)) {
        echo "Login successful!";
        // Start session or redirect to user dashboard
        session_start();
        $_SESSION['username'] = $username;
        header("Location: profile.php");
        exit;
    } else {
        echo "Invalid credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title> Login </title>
</head>
<body>
<h1> Login </h1>
<form method="post">
    <label>Username: </label>
    <input type="text" name="username" required>
    <br>
    <label>Password: </label>
    <input type="password" name="password" required>
    <br>
    <input type="submit" value="Login">
</form>

<?php if (isset($error)) {echo $error;} ?>

<p> If you do not have an account, please <a href="register.php"> Register Here</a>. </p>
</body>
</html>

