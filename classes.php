<?php

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function registerUser($username, $email, $password) {
        // Input validation
        if (empty($username)) {
            return "Username cannot be empty.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Invalid email format.";
        }
        if (strlen($password) < 9) {
            return "Password must be at least 9 characters long.";
        }

        // Hash the password for security
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Attempt to insert the user into the database
        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashedPassword,
            ]);
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry error code
                return "Username or email already exists.";
            }
            return "Database error: " . $e->getMessage();
        }
    }

    public function authenticateUser($username, $password) {
        try {
            $stmt = $this->pdo->prepare("SELECT password FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            return $user && password_verify($password, $user['password']);
        } catch (PDOException $e) {
            return false;
        }
    }
}

class Topic
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Method: Create a new topic
    public function createTopic($userId, $title, $description)
    {
        if (empty($title)) {
            return false;
        }

        $stmt = $this->pdo->prepare("INSERT INTO topics (user_id, title, description) VALUES (:user_id, :title, :description)");
        return $stmt->execute([
            ':user_id' => $userId,
            ':title' => $title,
            ':description' => $description,
        ]);
    }

    // Method: Retrieve all topics
    public function getTopics()
    {
        $stmt = $this->pdo->query("SELECT id, title, description FROM topics");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Method: Retrieve topics created by a specific user
    public function getCreatedTopics($userId)
    {
        $stmt = $this->pdo->prepare("SELECT id, title, description FROM topics WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}

class Vote {
    private $pdo;

    // Constructor: Receives the PDO object for database interactions
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Method: Cast a vote
    public function vote($userId, $topicId, $voteType) {
        if ($this->hasVoted($topicId, $userId)) {
            return "You have already voted on this topic.";
        }

        try {
            $stmt = $this->pdo->prepare("INSERT INTO votes (user_id, topic_id, vote_type, voted_at) VALUES (:user_id, :topic_id, :vote_type, NOW())");
            $stmt->execute([
                ':user_id' => $userId,
                ':topic_id' => $topicId,
                ':vote_type' => $voteType,
            ]);
            return true;
        } catch (PDOException $e) {
            return "Failed to record vote due to a database error.";
        }
    }


        // Method: Check if a user has already voted on a topic
    public function hasVoted($topicId, $userId) {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM votes WHERE topic_id = :topic_id AND user_id = :user_id");
            $stmt->execute([
                ':topic_id' => $topicId,
                ':user_id' => $userId,
            ]);
            return $stmt->fetchColumn() > 0; // Returns true if count > 0
        } catch (PDOException $e) {
            return false; // Database error
        }
    }

    // Method: Retrieve a user's voting history
    public function getUserVoteHistory($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT v.topic_id, v.vote_type, v.voted_at, t.title, t.description 
                FROM votes v 
                JOIN topics t ON v.topic_id = t.id 
                WHERE v.user_id = :user_id
            ");
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return []; // Return an empty array on error
        }
    }
}


class TimeFormatter
{
    // Static method: Format a timestamp into a human-readable string
    public static function formatTimestamp($timestamp)
    {
        $difference = time() - $timestamp;

        if ($difference < 60) {
            return "$difference seconds ago";
        } elseif ($difference < 3600) {
            return floor($difference / 60) . " minutes ago";
        } elseif ($difference < 86400) {
            return floor($difference / 3600) . " hours ago";
        } elseif ($difference < 2592000) { // 30 days
            return floor($difference / 86400) . " days ago";
        } elseif ($difference < 31536000) { // 12 months
            return floor($difference / 2592000) . " months ago";
        } else {
            return date("M d, Y", $timestamp);
        }
    }
}


?>
