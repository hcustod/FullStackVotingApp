<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Include necessary files
include 'db.config.php';
include 'classes.php';

// Initialize PDO
$config = include 'db.config.php';
$pdo = new PDO(
    "mysql:host={$config['app']['host']};dbname={$config['app']['dbname']}",
    $config['app']['username'],
    $config['app']['password']
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$topic = new Topic($pdo);

// Handle logout
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Fetch user ID
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
$stmt->execute([':username' => $_SESSION['username']]);
$userId = $stmt->fetchColumn();

// Handle theme switching
if (isset($_GET['theme'])) {
    $theme = $_GET['theme'];
    if ($theme == 'light' || $theme == 'dark') {
        setcookie('theme', $theme, time() + (86400 * 30), '/'); // Save theme preference in a cookie
        header('Location: create_topic.php');
        exit();
    }
}
$currentTheme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

// Handle topic creation
$error = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $description = $_POST["description"];

    if ($topic->createTopic($userId, $title, $description)) {
        header("Location: vote.php");
        exit();
    } else {
        $error = "Failed to create topic!";
    }
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
</head>
<body>
<h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></h1>
<nav>
    <a href="create_topic.php">Dashboard</a>
    <a href="vote.php">Topics</a>
    <a href="profile.php">Profile</a>
    <a href="create_topic.php?logout=true">Logout</a>
</nav>

<h1>Create a New Topic</h1>
<?php if ($error): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<form method="post">
    <label>Title: </label>
    <input type="text" name="title" required>
    <br>
    <label>Description: </label>
    <textarea name="description" required></textarea>
    <br>
    <input type="submit" value="Create Topic">
</form>

<div class="theme-toggle">
    <p>Themes:</p>
    <a href="?theme=light">Light</a> | <a href="?theme=dark">Dark</a>
</div>
</body>
</html>
