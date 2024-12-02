<?php
include 'db.config.php';
include 'classes.php';

session_start();

$config = include 'db.config.php';

$pdo = new PDO(
    "mysql:host={$config['app']['host']};dbname={$config['app']['dbname']}",
    $config['app']['username'],
    $config['app']['password']
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_SESSION['username'])) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute([':username' => $_SESSION['username']]);
    $userId = $stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hank's Voting App</title>
</head>
<body>
<h1>Welcome to Hank's Voting App!</h1>

<?php if (isset($_SESSION['username'])): ?>
    <p><a href="profile.php">Profile</a></p>
    <p><a href="create_topic.php">Create Topic</a></p>
    <p><a href="vote.php">Vote</a></p>
    <p><a href="logout.php">Logout</a></p>
<?php else: ?>
    <p><a href="login.php">Login</a></p>
    <p><a href="register.php">Register Now</a></p>
<?php endif; ?>
</body>
</html>
