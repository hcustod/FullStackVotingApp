<?php
session_start();
if (!isset($_SESSION['username']))
{
    header('Location: login.php');
    exit();
}

include 'db.config.php';
include 'classes.php';
include 'helperFunctions.php';

// Initialize PDO
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

$topic = new Topic($pdo);

// Fetch user ID handler
$stmt = $pdo->prepare("SELECT id FROM Users WHERE username = :username");
$stmt->execute([':username' => $_SESSION['username']]);
$userId = $stmt->fetchColumn();

// Theme switching
if (isset($_GET['theme']))
{
    $theme = $_GET['theme'];
    if ($theme == 'light' || $theme == 'dark')
    {
        setcookie('theme', $theme, time() + (86400 * 30), '/');
        header('Location: create_topic.php');
        exit();
    }
}

$currentTheme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

// Topic creation handler
$error = null;
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);

    if (empty($title) || empty($description)) {
        $error = "Title and description cannot be empty!";
    }
    else
    {
        if ($topic->createTopic($userId, $title, $description))
        {
            header("Location: vote.php");
            exit();
        }
        else
        {
            var_dump($pdo->errorInfo());
            $error = "Failed to create topic!";
        }
    }
}

// Logout handler
if (isset($_GET['logout']) && $_GET['logout'] === 'true')
{
    logout();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Topic Dashboard</title>
    <?php if ($currentTheme == 'dark'): ?>
        <style>
            body {
                background-color: black;
                color: white;
            }
        </style>
    <?php else: ?>
        <style>
            body {
                background-color: white;
                color: black;
            }
        </style>
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles/main.css" rel="stylesheet">
</head>
<body>

    <div class="container mt-5">
        <div class="border border-5">
            <h1 class="text-secondary text-center mb-0 p-4 title-text">Welcome, <?php echo htmlspecialchars(captialize($_SESSION["username"])); ?>!</h1>
        </div>
        <br>

        <nav class="text-center">
            <a class="large-nav-links p-2" href="create_topic.php">Dashboard</a>
            <a class="large-nav-links p-2" href="vote.php">Topics</a>
            <a class="large-nav-links p-2" href="profile.php">Profile</a>
            <a class="large-nav-links p-2" href="create_topic.php?logout=true">Logout</a>
        </nav>

        <h1 class="text-secondary">Create a New Topic:</h1>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form class="text-center" method="post">
            <label class="big-label-text text-primary">Title: </label>
            <br>
            <input class="wide-input-field" type="text" name="title" required>
            <br>
            <label class="big-label-text text-primary mt-2">Description: </label>
            <br>
            <textarea class="wide-tall-input-field" name="description" required></textarea>
            <br>
            <input class="btn btn-primary mt-5 mb-1 btn-xl" type="submit" value="Create Topic">
        </form>

        <div class="text-secondary">
            <p>Themes: <a href="?theme=light">Light</a> | <a href="?theme=dark">Dark</a> </p>

        </div>

    </div>
</body>
</html>
