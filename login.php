<?php
include 'db.config.php';
include 'classes.php';

$dbConfig = include 'db.config.php';

try
{
    $pdo = new PDO("sqlite:{$dbConfig['app']['database_path']}");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

}
catch (PDOException $e)
{
    die("Connection failed: " . $e->getMessage());
}

$user = new User($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($user->authenticateUser($username, $password))
    {
        // Start session or redirect to user dashboard
        session_start();
        $_SESSION['username'] = $username;
        header("Location: create_topic.php");
        exit;
    }
    else
    {
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

    <br>
    <p><a href="index.php">Return to Main Menu</a></p>
    <br>


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

