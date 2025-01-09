<?php
include 'db.config.php';
include 'classes.php';
include 'helperFunctions.php';

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

session_start();
if (!isset($_SESSION['username']))
{
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$SQLQuery = $pdo->prepare("SELECT id FROM Users WHERE username = :username");
$SQLQuery->execute([':username' => $username]);
$userId = $SQLQuery->fetchColumn();

$vote = new Vote($pdo);
$topic = new Topic($pdo);
$comment = new Comment($pdo);
$topics = $topic->getTopics();

// Voting handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['topicID'], $_POST['voteType']))
{
    $topicId = $_POST['topicID'];
    $voteType = $_POST['voteType'];
    $voteMessage = $vote->vote($userId, $topicId, $voteType) ? "Vote recorded!" : "You already voted on this topic.";
}

// Comment handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'], $_POST['topic_id']))
{
    $commentText = $_POST['comment'];
    $topicId = $_POST['topic_id'];
    $commentMessage = $comment->addComment($userId, $topicId, $commentText) ? "Comment added successfully!" : "Failed to add comment.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Topics & Comments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles/main.css" rel="stylesheet">
</head>
<body>

    <div class="container mt-5">
        <div class="border border-5 position-relative">
            <h1 class="text-secondary text-center mb-0 p-4 title-text">Welcome, <?php echo htmlspecialchars(captialize($username)); ?>!</h1>
        </div>

        <nav class="text-center">
            <a class="large-nav-links p-2" href="create_topic.php">Dashboard</a>
            <a class="large-nav-links p-2" href="vote.php">Topics</a>
            <a class="large-nav-links p-2" href="profile.php">Profile</a>
            <a class="large-nav-links p-2" href="create_topic.php?logout=true">Logout</a>
        </nav>


        <?php if (isset($voteMessage)): ?>
            <p><?php echo htmlspecialchars($voteMessage); ?></p>
        <?php endif; ?>

        <?php if (isset($commentMessage)): ?>
            <p><?php echo htmlspecialchars($commentMessage); ?></p>
        <?php endif; ?>

        <h2 class="text-secondary">Topics</h2>

        <?php if (!empty($topics)): ?>
            <?php foreach ($topics as $t): ?>

                <div style="border: 1px solid gray; margin-bottom: 20px; padding: 10px;">

                    <h2> <strong class="text-secondary"> Title: </strong> <?php echo htmlspecialchars(captialize($t->title)); ?> </h2>
                    <h2> <strong class="text-secondary"> Description: </strong> <?php echo htmlspecialchars($t->description); ?> </h2>
                    <p><strong>Created:</strong> <?php echo TimeFormatter::formatTimestamp(strtotime($t->createdAt)); ?></p>

                    <!-------------- Votes ---------------->
                    <?php $votes = $vote->getTopicVoteCount($t->id); ?>
                    <p><strong>Votes:</strong> Upvotes: <?php echo $votes['up']; ?>, Downvotes: <?php echo $votes['down']; ?></p>

                    <form method="post" style="display: inline;">
                        <input type="hidden" name="topicID" value="<?php echo htmlspecialchars($t->id); ?>">
                        <button class="btn btn-primary" type="submit" name="voteType" value="up">Upvote</button>
                    </form>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="topicID" value="<?php echo htmlspecialchars($t->id); ?>">
                        <button class="btn btn-primary" type="submit" name="voteType" value="down">Downvote</button>
                    </form>

                    <!-------------- Comments ---------------->
                    <h4>Comments</h4>
                    <?php
                    $comments = $comment->getComments($t->id);
                    if (!empty($comments)): ?>
                        <ul>
                            <?php foreach ($comments as $c): ?>

                                <li>
                                    <strong><?php echo htmlspecialchars($c['username']); ?>:</strong>
                                    <?php echo htmlspecialchars($c['comment']); ?>
                                    <em>(<?php echo TimeFormatter::formatTimestamp(strtotime($c['commented_at'])); ?>)</em>
                                </li>

                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No comments yet. Be the first to comment!</p>
                    <?php endif; ?>

                    <!-------------- Add comments  ---------------->
                    <form method="post">
                        <textarea name="comment" placeholder="Write your comment here..." required></textarea>
                        <input type="hidden" name="topic_id" value="<?php echo htmlspecialchars($t->id); ?>">
                        <br>
                        <button class="btn btn-primary" type="submit">Add Comment</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No topics available. Create a topic to get started!</p>
        <?php endif; ?>

    </div>
</body>
</html>
