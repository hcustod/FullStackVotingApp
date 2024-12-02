<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Include necessary files
include 'db.config.php';
include 'classes.php';

// Initialize PDO and Classes
$config = include 'db.config.php';
$pdo = new PDO(
    "mysql:host={$config['app']['host']};dbname={$config['app']['dbname']}",
    $config['app']['username'],
    $config['app']['password']
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$topic = new Topic($pdo);
$vote = new Vote($pdo);

// Fetch topics
$topics = $topic->getTopics();

// Handle voting
$output = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topicID = $_POST['topicID'];
    $voteType = $_POST['voteType'];

    // Fetch user ID
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute([':username' => $_SESSION['username']]);
    $userId = $stmt->fetchColumn();

    if (!$vote->hasVoted($topicID, $userId)) {
        if ($vote->vote($userId, $topicID, $voteType)) {
            $output = "Your vote has been cast.";
        } else {
            $output = "Failed to cast your vote. Please try again.";
        }
    } else {
        $output = "You already voted on this topic.";
    }
}

// Theme handling
$currentTheme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
if (isset($_GET['theme'])) {
    $theme = $_GET['theme'];
    if ($theme === 'light' || $theme === 'dark') {
        setcookie('theme', $theme, time() + (86400 * 30), '/'); // Save theme preference in a cookie
        header('Location: vote.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Topics</title>
    <?php if ($currentTheme === 'dark'): ?>
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
<h1>Topics List</h1>
<nav>
    <a href="create_topic.php">Dashboard</a>
    <a href="vote.php">Topics</a>
    <a href="profile.php">Profile</a>
    <a href="create_topic.php?logout=true">Logout</a>
</nav>

<?php if (isset($output)) { echo "<p style='color: green;'>$output</p>"; } ?>

<table border="1">
    <tr>
        <th>Title</th>
        <th>Description</th>
        <th>Vote</th>
    </tr>

    <?php foreach ($topics as $topic): ?>
        <tr>
            <td><?php echo htmlspecialchars($topic['title']); ?></td>
            <td><?php echo htmlspecialchars($topic['description']); ?></td>
            <td>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="topicID" value="<?php echo htmlspecialchars($topic['id']); ?>">
                    <button type="submit" name="voteType" value="up">Upvote</button>
                </form>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="topicID" value="<?php echo htmlspecialchars($topic['id']); ?>">
                    <button type="submit" name="voteType" value="down">Downvote</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<div class="theme-toggle">
    <p>Themes:</p>
    <a href="?theme=light">Light</a> | <a href="?theme=dark">Dark</a>
</div>
</body>
</html>
