<?php

// User Class
class User
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
        // Input validation
        if (empty($username))
        {
            return "Username cannot be empty.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Invalid email format.";
        }
        if (strlen($password) < 9) {
            return "Password must be at least 9 characters long.";
        }

        // A built-in function for hashing, so user passwords are not stored as clear text.
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Try inserting the user into the database & return true if successful
        try
        {
            $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashedPassword,
            ]);
            return true;
        }
        catch (PDOException $e) // Catching any errors that may arise related to DB data.
        {
            if ($e->getCode() == 23000)  // TODO; check on this
            {
                return "Username or email already exists.";
            }
            return "Database error: " . $e->getMessage();
        }
    }

    // User Auth method; user already has an account.
    public function authenticateUser($username, $password)
    {
        try
        {
            $stmt = $this->pdo->prepare("SELECT password FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            return $user && password_verify($password, $user['password']);
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

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Creating a topic method
    public function createTopic($userId, $title, $description)
    {
        // Check if user did not input anything
        if (empty($title)) {
            return false;
        }

        // Create a var for the sql insertion format and inset user input into appropriate columns.
        $stmt = $this->pdo->prepare("INSERT INTO topics (user_id, title, description) VALUES (:user_id, :title, :description)");
        return $stmt->execute([
            ':user_id' => $userId,
            ':title' => $title,
            ':description' => $description,
        ]);
    }

    // Get all topics from topics table in DB
    public function getTopics() {
        $stmt = $this->pdo->query("SELECT id, title, description, created_at FROM topics");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Retrieve topics created by a specific user
    public function getCreatedTopics($userId)
    {
        $stmt = $this->pdo->prepare("SELECT id, title, description FROM topics WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}


// Voting class
class Vote {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Records a user's vote on a topic.
     * Returns true if the vote is successfully recorded, false if it fails.
     */
    public function vote($userId, $topicId, $voteType): bool {
        if ($this->hasVoted($topicId, $userId)) {
            return false; // User has already voted
        }

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO votes (user_id, topic_id, vote_type, voted_at) 
                VALUES (:user_id, :topic_id, :vote_type, NOW())
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':topic_id' => $topicId,
                ':vote_type' => $voteType,
            ]);
            return true; // Vote successfully recorded
        } catch (PDOException $e) {
            // Log error for debugging (optional)
            error_log("Failed to record vote: " . $e->getMessage());
            return false; // Database error
        }
    }

    /**
     * Checks if a user has already voted on a topic.
     * Returns true if the user has voted, false otherwise.
     */
    public function hasVoted($topicId, $userId): bool {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) 
                FROM votes 
                WHERE topic_id = :topic_id AND user_id = :user_id
            ");
            $stmt->execute([
                ':topic_id' => $topicId,
                ':user_id' => $userId,
            ]);
            return $stmt->fetchColumn() > 0; // True if count > 0
        } catch (PDOException $e) {
            // Log error for debugging (optional)
            error_log("Error checking vote: " . $e->getMessage());
            return false; // Database error
        }
    }

    /**
     * Retrieves a history of votes cast by the specified user.
     * Returns an array of associative arrays with details like topicId, voteType, and timestamp.
     */
    public function getUserVoteHistory($userId): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT v.topic_id, v.vote_type, v.voted_at, t.title, t.description 
                FROM votes v
                JOIN topics t ON v.topic_id = t.id
                WHERE v.user_id = :user_id
                ORDER BY v.voted_at DESC
            ");
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Return vote history
        } catch (PDOException $e) {
            // Log error for debugging (optional)
            error_log("Error retrieving vote history: " . $e->getMessage());
            return []; // Return empty array on error
        }
    }

    public function getTopicVoteCount($topicId) {
        $stmt = $this->pdo->prepare("
        SELECT vote_type, COUNT(*) as count 
        FROM votes 
        WHERE topic_id = :topic_id 
        GROUP BY vote_type
    ");
        $stmt->execute([':topic_id' => $topicId]);

        $result = ['up' => 0, 'down' => 0];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[$row['vote_type']] = $row['count'];
        }
        return $result;
    }
}



class Comment
{
    private $pdo;

    // Constructor: Accepts a PDO object for database interactions
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Method: Add a comment to a specific topic
    public function addComment($userId, $topicId, $comment)
    {
        // Validate inputs
        if (empty($comment)) {
            return false;
        }

        // Insert the comment into the database
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO comments (user_id, topic_id, comment, commented_at)
                VALUES (:user_id, :topic_id, :comment, NOW())
            ");
            return $stmt->execute([
                ':user_id' => $userId,
                ':topic_id' => $topicId,
                ':comment' => $comment,
            ]);
        } catch (PDOException $e) {
            // Log error or handle exception
            return false;
        }
    }

    // Method: Retrieve all comments for a specific topic
    public function getComments($topicId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.comment, c.commented_at, u.username
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.topic_id = :topic_id
                ORDER BY c.commented_at DESC
            ");
            $stmt->execute([':topic_id' => $topicId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log error or handle exception
            return [];
        }
    }
}


class TimeFormatter
{
    public static function formatTimestamp($timestamp)
    {
        $difference = time() - $timestamp;

        if ($difference < 60) {
            return "$difference seconds ago";
        } elseif ($difference < 3600) {
            $minutes = floor($difference / 60);
            return $minutes . ($minutes == 1 ? " minute ago" : " minutes ago");
        } elseif ($difference < 86400) {
            $hours = floor($difference / 3600);
            return $hours . ($hours == 1 ? " hour ago" : " hours ago");
        } elseif ($difference < 2592000) { // Approx. 30 days
            $days = floor($difference / 86400);
            return $days . ($days == 1 ? " day ago" : " days ago");
        } elseif ($difference < 31536000) { // Approx. 12 months
            $months = floor($difference / 2592000); // Approx. 30 days per month
            return $months . ($months == 1 ? " month ago" : " months ago");
        } else {
            return date("M d, Y", $timestamp);
        }
    }
}



?>
