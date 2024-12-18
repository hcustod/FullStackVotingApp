<?php
include 'db.config.php';
include 'classes.php';

$config = include 'db.config.php';
$pdo = new PDO("mysql:host={$config['app']['host']};dbname={$config['app']['dbname']}", $config['app']['username'], $config['app']['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
</head>
<body>
<h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>

<nav>
    <a href="create_topic.php">Dashboard</a>
    <a href="vote.php">Topics</a>
    <a href="profile.php">Profile</a>
    <a href="create_topic.php?logout=true">Logout</a>
</nav>

<?php if (isset($voteMessage)): ?>
    <p><?php echo htmlspecialchars($voteMessage); ?></p>
<?php endif; ?>

<?php if (isset($commentMessage)): ?>
    <p><?php echo htmlspecialchars($commentMessage); ?></p>
<?php endif; ?>

<h2>Topics</h2>

<?php if (!empty($topics)): ?>
    <?php foreach ($topics as $t): ?>

        <div style="border: 1px solid gray; margin-bottom: 20px; padding: 10px;">

            <h3><?php echo htmlspecialchars($t->title); ?></h3>
            <p><?php echo htmlspecialchars($t->description); ?></p>
            <p><strong>Created:</strong> <?php echo TimeFormatter::formatTimestamp(strtotime($t->createdAt)); ?></p>

            <!-------------- Votes ---------------->
            <?php $votes = $vote->getTopicVoteCount($t->id); ?>
            <p><strong>Votes:</strong> Upvotes: <?php echo $votes['up']; ?>, Downvotes: <?php echo $votes['down']; ?></p>

            <form method="post" style="display: inline;">
                <input type="hidden" name="topicID" value="<?php echo htmlspecialchars($t->id); ?>">
                <button type="submit" name="voteType" value="up">Upvote</button>
            </form>
            <form method="post" style="display: inline;">
                <input type="hidden" name="topicID" value="<?php echo htmlspecialchars($t->id); ?>">
                <button type="submit" name="voteType" value="down">Downvote</button>
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
                <button type="submit">Add Comment</button>
            </form>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No topics available. Create a topic to get started!</p>
<?php endif; ?>
</body>
</html>
