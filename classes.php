<?php

// ----------------- Interfaces ------------------------
interface UserActionsInterface
{
    public function registerUser($username, $email, $password);
    public function authenticateUser($username, $password);
}

interface VotingInterface
{
    public function vote($userId, $topicId, $voteType): bool;
    public function hasVoted($topicId, $userId): bool;
    public function getUserVoteHistory($userId): array;
}

interface CommentInterface
{
    public function addComment($userId, $topicId, $comment);
    public function getComments($topicId);
}

interface TimestampFormatterInterface
{
    public static function formatTimestamp($timestamp);
}


// ----------------- Classes ------------------------
/*
 * Classes are not implemented here to carry or modify data internally except for the Topic class.
 * Other classes provide the requested methods which act upon the PDO objects and database directly;
 * Encapsulating properties for them seemed unnecessary given data can be passed directly into the parameters for the methods
 * and immediately then used in SQL queries.
 */

// User Class
class User implements UserActionsInterface
{
    private $pdo;

    // PDO constructor
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Register user method
    public function registerUser($username, $email, $password)
    {
        // Check for empty input
        if (empty($username))
        {
            echo "Username cannot be empty.";
            return false;
        }

        // Email check using PHP Email constant filter; easy check for valid email.
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            echo "Invalid email format.";
            return false;
        }

        // Check for password being below 9 characters; provide error prompt to user.
        if (strlen($password) < 9)
        {
            echo "Password must be at least 9 characters long.";
            return false;
        }

        // A built-in function for hashing, so user passwords are not stored as clear text.
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Try inserting the user into the database & return true if successful
        try
        {
            $stmt = $this->pdo->prepare("INSERT INTO Users (username, email, password) VALUES (:username, :email, :password)");
            $stmt->execute([':username' => $username, ':email' => $email, ':password' => $hashedPassword,]);
            return true;
        }
        catch (PDOException $e) // Catching any errors related to DB data.
        {
            if ($e->getCode() == 23000)  // 23000 is a SQLSTATECODE; Represents an Integrity Constraint Violation.
            {
                echo "Username or email already exists.";
                return false;

            }
            echo "Database error: " . $e->getMessage();
            return false;
        }
    }

    // User Auth method; user already has an account.
    public function authenticateUser($username, $password)
    {
        try
        {
            $authSQLQUERY = $this->pdo->prepare("SELECT password FROM Users WHERE username = :username");
            $authSQLQUERY->execute([':username' => $username]);
            $user = $authSQLQUERY->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password']))
            {
                return true;
            }
        }
        catch (PDOException $e)
        {
            return false;
        }
    }
}

// Topic Class
class Topic
{
    private $pdo;
    public $id;
    public $userId;
    public $title;
    public $description;
    public $createdAt;

    /*
    // Getters and Setters
    public function getTopicId() { return $this->id; }
    public function getUserId() { return $this->userId; }
    public function getTitle() { return $this->title; }
    public function getDescription() { return $this->description; }
    public function getCreatedAt() { return $this->createdAt; }
    public function setId($topicId) { $this->id = $topicId; }
    public function setUserId($userId) { $this->userId = $userId; }
    public function setTitle($title) { $this->title = $title; }
    public function setDescription($description) { $this->description = $description; }
    public function setCreatedAt($createdAt) { $this->createdAt = $createdAt; }
    */

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createTopic($userId, $title, $description): bool
    {
        if (empty($title) || empty($description))
        {
            return false;
        }

        try {
            $createTopicSQLQuery = $this->pdo->prepare("INSERT INTO Topics (user_id, title, description, created_at) VALUES (:user_id, :title, :description, CURRENT_TIMESTAMP)");
            return $createTopicSQLQuery->execute(['user_id' => $userId, 'title' => $title, 'description' => $description]);
        }
        catch (PDOException $e) {
            return false;
        }
    }

    public function getTopics(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT id, user_id, title, description, created_at FROM Topics");
            $topics = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $topic = new Topic($this->pdo);
                $topic->id = $row['id'];
                $topic->userId = $row['user_id'];
                $topic->title = $row['title'];
                $topic->description = $row['description'];
                $topic->createdAt = $row['created_at'];
                $topics[] = $topic;
            }
            return $topics;
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
            return [];
        }
    }

    public function getCreatedTopics($userId): array
    {
        try {
            $getUserCreatedTopicsSQLQuery = $this->pdo->prepare("SELECT id, user_id, title, description, created_at FROM Topics WHERE user_id = :user_id");
            $getUserCreatedTopicsSQLQuery->execute([':user_id' => $userId]);

            return $getUserCreatedTopicsSQLQuery->fetchAll(PDO::FETCH_ASSOC);

            /*
            $topics = [];

            while ($row = $getUserCreatedTopicsSQLQuery->fetch(PDO::FETCH_ASSOC)) {
                $topic = new Topic($this->pdo);
                $topic->id = $row['id'];
                $topic->userId = $row['user_id'];
                $topic->title = $row['title'];
                $topic->description = $row['description'];
                $topic->createdAt = $row['created_at'];
                $topics[] = $topic;
            }
            return $topics;
            */
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
            return [];
        }
    }
}


// Voting class
class Vote implements VotingInterface
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Records a users vote on the topic
    public function vote($userId, $topicId, $voteType): bool
    {
        if ($this->hasVoted($topicId, $userId))
        {
            return false;
        }

        try
        {
            $voteSQLQuery = $this->pdo->prepare("INSERT INTO Votes (user_id, topic_id, vote_type, voted_at) 
                                                VALUES (:user_id, :topic_id, :vote_type, CURRENT_TIMESTAMP)");
            $voteSQLQuery->execute([
                ':user_id' => $userId,
                ':topic_id' => $topicId,
                ':vote_type' => $voteType,
            ]);
            return true; // Vote success
        }
        catch (PDOException $e)
        {
            error_log("Failed to record vote: " . $e->getMessage());    // Debugging use
            return false; // Database error
        }
    }


    // Checks if user has already voted on given topic
    public function hasVoted($topicId, $userId): bool
    {
        try
        {
            $hasVotedSQLQuery = $this->pdo->prepare("SELECT COUNT(*) FROM Votes WHERE topic_id = :topic_id AND user_id = :user_id");
            $hasVotedSQLQuery->execute([
                ':topic_id' => $topicId,
                ':user_id' => $userId,
            ]);
            return $hasVotedSQLQuery->fetchColumn() > 0; // True if count > 0
        }
        catch (PDOException $e)
        {
            // Log error for debugging (optional)
            error_log("Error checking vote: " . $e->getMessage());
            return false; // Database error
        }
    }

    // Takes vote history and returns an associative array
    public function getUserVoteHistory($userId): array
    {
        try
        {
            $userVoteHistSQLQuery = $this->pdo->prepare("SELECT v.topic_id, v.vote_type, v.voted_at, t.title, t.description FROM Votes v
                                        JOIN Topics t ON v.topic_id = t.id WHERE v.user_id = :user_id ORDER BY v.voted_at DESC");
            $userVoteHistSQLQuery->execute([':user_id' => $userId]);
            return $userVoteHistSQLQuery->fetchAll(PDO::FETCH_ASSOC); // Return vote history as associative array; FETCH_ASSOC is predefined php constant
        }
        catch (PDOException $e)
        {
            error_log("Error retrieving vote history: " . $e->getMessage());
            return []; // Return empty array on error
        }
    }

    public function getTopicVoteCount($topicId)
    {
        $topicVCountSQLQuery = $this->pdo->prepare("SELECT vote_type, COUNT(*) as count FROM Votes 
                                    WHERE topic_id = :topic_id GROUP BY vote_type");
        $topicVCountSQLQuery->execute([':topic_id' => $topicId]);

        $result = ['up' => 0, 'down' => 0];
        foreach ($topicVCountSQLQuery->fetchAll(PDO::FETCH_ASSOC) as $row)
        {
            $result[$row['vote_type']] = $row['count'];
        }

        return $result;
    }
}



class Comment implements CommentInterface
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addComment($userId, $topicId, $comment): bool
    {
        if (empty($comment))
        {
            return false;
        }

        try
        {
            $addCommentSQLQuery = $this->pdo->prepare("INSERT INTO Comments (user_id, topic_id, comment, commented_at) VALUES (:user_id, :topic_id, :comment, CURRENT_TIMESTAMP)");
            return $addCommentSQLQuery->execute([':user_id' => $userId, ':topic_id' => $topicId, ':comment' => $comment,]);
        }
        catch (PDOException $e)
        {
            // Log error or handle exception
            return false;
        }
    }

    // Retrieve all comments for single topic
    public function getComments($topicId): array
    {
        try
        {
            $getCommentsSQLQuery = $this->pdo->prepare("SELECT c.comment, c.commented_at, u.username FROM Comments c
                                                            JOIN Users u ON c.user_id = u.id WHERE c.topic_id = :topic_id
                                                             ORDER BY c.commented_at DESC ");
            $getCommentsSQLQuery->execute([':topic_id' => $topicId]);
            return $getCommentsSQLQuery->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e)
        {
            return [];
        }
    }
}


class TimeFormatter implements TimestampFormatterInterface
{
    public static function formatTimestamp($timestamp)
    {
        // Sets default timezone for PHP.
        if (!is_numeric($timestamp)) {
            $timestamp = strtotime($timestamp);
        }

        $difference = time() - $timestamp;

        if ($difference < 60) {
            return "$difference seconds ago";
        } elseif ($difference < 3600) {
            $minutes = floor($difference / 60);
            return $minutes . ($minutes == 1 ? " minute ago" : " minutes ago"); // Ternary as easy way to implement grammar.
        } elseif ($difference < 86400) {
            $hours = floor($difference / 3600);
            return $hours . ($hours == 1 ? " hour ago" : " hours ago");
        } elseif ($difference < 2592000) {                                                  // 30 days
            $days = floor($difference / 86400);
            return $days . ($days == 1 ? " day ago" : " days ago");
        } elseif ($difference < 31536000) {                                                 // 12 months
            $months = floor($difference / 2592000);                                    // Assuming here; 30 days per month
            return $months . ($months == 1 ? " month ago" : " months ago");
        }
        else
        {
            return date("M d, Y", $timestamp);
        }
    }
}



?>
