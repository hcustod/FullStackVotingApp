<?php
include 'db.config.php';
include 'classes.php';

$config = include 'db.config.php';
$pdo = new PDO("mysql:host={$config['app']['host']};dbname={$config['app']['dbname']}", $config['app']['username'], $config['app']['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$user = new User($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $user->registerUser($username, $email, $password);
    if ($result === true)
    {
        header("location: create_topic.php");
    }
    else
    {
        echo "Registration failed! Reason: " . $result;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
<h1>Register</h1>

<form method="post">
    <label>Username:</label>
    <input type="text" name="username" required>
    <br>
    <label>Email:</label>
    <input type="email" name="email" required>
    <br>
    <label>Password:</label>
    <input type="password" name="password" required>
    <br>
    <input type="submit" value="Register">
</form>

<p>If you already have an account, please <a href="login.php">Login Here</a>.</p>

</body>
</html>
