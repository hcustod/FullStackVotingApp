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

        <div class="text-center border border-5 p-4 mt-4 big-label-text">
            <p class="text-secondary">Total Topics Created by User: <?php echo htmlspecialchars($totalUserTopicsCreated); ?></p>
            <p class="text-secondary">Total User Votes: <?php echo htmlspecialchars($totalUserVotes); ?></p>
        </div>

        <br>
        <h2 class="text-secondary big-label-text">Your Topics: </h2>
        <?php if (!empty($createdTopics)): ?>

            <div class="border border-5 p-4 mt-4 w-50 mx-auto">

                <?php foreach ($createdTopics as $t): ?>

                    <p class="regular-text"><strong> Title: </strong><?php echo htmlspecialchars($t['title']); ?> </p>
                    <p class="regular-text"><strong> Description: </strong> <?php echo htmlspecialchars($t['description']); ?> </p>

                <?php endforeach; ?>

            </div>

        <?php else: ?>
            <p>No topics created yet.</p>
        <?php endif; ?>

        <br>
        <h2 class="text-secondary big-label-text">Voting History:</h2>
        <?php if (!empty($votingUserHistory)): ?>

                <?php foreach ($votingUserHistory as $userHis): ?>

                    <div class="border border-5 p-4 mt-4 mx-auto w-50 5rem">
                        <p class="regular-text"><strong>Title: </strong> <?php echo htmlspecialchars($userHis['title']); ?></p>
                        <p class="regular-text"><strong>Description: </strong> <?php echo htmlspecialchars($userHis['description']); ?></p>
                        <p class="regular-text"><strong class="text-primary">Vote: </strong> <?php echo htmlspecialchars($userHis['vote_type']); ?></p>
                    </div>

                <?php endforeach; ?>

        <?php else: ?>
            <p>You have not voted on any topics yet. Please vote on topics to see some results!</p>
        <?php endif; ?>

    </div>

</body>
</html>
