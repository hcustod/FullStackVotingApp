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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles/main.css" rel="stylesheet">
</head>
<body>

    <div class="container mt-5">
        <div class="border border-5 position-relative">
            <h1 class="text-secondary text-center mb-0 p-4 title-text">Login</h1>
            <a class="btn btn-primary btn-lg position-absolute top-50 translate-middle-y end-0 me-4" href="index.php">Return to Main Menu</a>
        </div>

        <br>

        <form class="text-center mt-1" method="post">
            <label class="big-label-text text-primary mt-2" >Username: </label>
            <br>
            <input class="wide-input-field" type="text" name="username" required>
            <br>
            <label class="big-label-text text-primary mt-2">Password: </label>
            <br>
            <input class="wide-input-field" type="password" name="password" required>
            <br>
            <input class="btn btn-primary mt-5 mb-4 btn-xl" type="submit" value="Login">
        </form>

        <?php if (isset($error)) {echo $error;} ?>

        <p class="mt-4 text-warning"> If you do not have an account, please <a href="register.php"> Register Here</a>. </p>

    </div>

</body>
</html>

