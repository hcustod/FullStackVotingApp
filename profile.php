<?php
ob_start();
include 'db.config.php';
include 'classes.php';
include 'helperFunctions.php';

// Session setup
if (session_status() == PHP_SESSION_NONE)
{
    session_start();
}

if (!isset($_SESSION['username']))
{
    die("You must be logged in to view your profile.");
}

// Sqlite db setup
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


$username = $_SESSION['username'];

// Get user ID
$SQLQuery = $pdo->prepare("SELECT id FROM Users WHERE username = :username");
$SQLQuery->execute([':username' => $username]);
$userId = $SQLQuery->fetchColumn();

// Get user topics
$topic = new Topic($pdo);
$createdTopics = $topic->getCreatedTopics($userId);
$totalUserTopicsCreated = count($createdTopics);

// Get user voting history
$vote = new Vote($pdo);
$votingUserHistory = $vote->getUserVoteHistory($userId);
$totalUserVotes = count($votingUserHistory);

// Get current theme
$currentTheme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';

// Logout
if (isset($_GET['logout']) && $_GET['logout'] === 'true')
{
    logout();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles/main.css" rel="stylesheet">
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
        </style>
    <?php endif; ?>
</head>
<body>

    <div class="container mt-5">

        <div class="border border-5 position-relative">
            <h1 class="text-secondary text-center mb-0 p-4 title-text"><?php echo htmlspecialchars(captialize($username)); ?>'s Profile</h1>
        </div>
        <br>

        <nav class="text-center">
            <a class="large-nav-links p-2" href="create_topic.php">Dashboard</a>
            <a class="large-nav-links p-2" href="vote.php">Topics</a>
            <a class="large-nav-links p-2" href="profile.php">Profile</a>
            <a class="large-nav-links p-2" href="create_topic.php?logout=true"">Logout</a>
        </nav>

        <p class="text-secondary">Total Topics Created: <?php echo htmlspecialchars($totalUserTopicsCreated); ?></p>
        <p>Total User Votes: <?php echo htmlspecialchars($totalUserVotes); ?></p>

        <h2>Your Topics</h2>
        <?php if (!empty($createdTopics)): ?>
            <ul>

                <?php foreach ($createdTopics as $t): ?>
                    <li><?php echo htmlspecialchars($t['title']); ?> - <?php echo htmlspecialchars($t['description']); ?></li>
                <?php endforeach; ?>

            </ul>
        <?php else: ?>
            <p>No topics created yet.</p>
        <?php endif; ?>

        <h2>Voting History</h2>
        <?php if (!empty($votingUserHistory)): ?>
            <ul>
                <?php foreach ($votingUserHistory as $userHis): ?>

                    <li>
                        <p>Title: <?php echo htmlspecialchars($userHis['title']); ?></p>
                        <p>Description: <?php echo htmlspecialchars($userHis['description']); ?></p>
                        <p>Vote: <?php echo htmlspecialchars($userHis['vote_type']); ?></p>
                    </li>

                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>You have not voted on any topics yet. Please vote on topics to see some results!</p>
        <?php endif; ?>

        <div class="theme-toggle">
            <p>Themes:</p>
            <a href="?theme=light">Light</a> | <a href="?theme=dark">Dark</a>
        </div>



    </div>

</body>
</html>
