<?php
include 'db.config.php';
include 'classes.php';

$config = include 'db.config.php';

try
{
    $pdo = new PDO("sqlite:{$config['app']['database_path']}");
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
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $user->registerUser($username, $email, $password);
    if ($result === true)
    {
        header("location: create_topic.php");
        exit;
    }
    else
    {
        echo "Registration failed!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles/main.css" rel="stylesheet">
</head>
<body>

    <div class="container mt-5">
        <div class="border border-5 position-relative">
            <h1 class="text-secondary text-center mb-0 p-4 title-text">Register</h1>
            <a class="btn btn-primary btn-lg position-absolute top-50 translate-middle-y end-0 me-4" href="index.php">Return to Main Menu</a>
        </div>

        <form class="text-center mt-1" method="post">
            <label class="big-label-text text-primary mt-2">Username:</label>
            <br>
            <input class="wide-input-field" type="text" name="username" required>
            <br>
            <label class="big-label-text text-primary mt-2">Email:</label>
            <br>
            <input class="wide-input-field" type="email" name="email" required>
            <br>
            <label class="big-label-text text-primary mt-2">Password:</label>
            <br>
            <input class="wide-input-field" type="password" name="password" required>
            <br>
            <input class="btn btn-primary mt-5 mb-4 btn-xl" type="submit" value="Register">
        </form>
        <br>


        <p class="text-warning">If you already have an account, please <a href="login.php">Login Here</a>.</p>


    </div>




</body>
</html>
