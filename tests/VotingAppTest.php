<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../classes.php';

class VotingAppTest extends TestCase {
    private static $pdo;
    private static $dbPath;

    public static function setUpBeforeClass(): void {
        // Load database connection information
        $config = include __DIR__ . '/../db.config.php';
        self::$dbPath = $config['app']['database_path'];

        try {
            // Connect to SQLite database
            self::$pdo = new PDO("sqlite:" . self::$dbPath);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            throw new Exception("Failed to set up the database: " . $e->getMessage());
        }
    }

    protected function setUp(): void {
        if (self::$pdo) {
            // Clear all tables before each test
            self::$pdo->exec("DELETE FROM Comments");
            self::$pdo->exec("DELETE FROM Votes");
            self::$pdo->exec("DELETE FROM Topics");
            self::$pdo->exec("DELETE FROM Users");
        }
    }

    public function testRegisterUserWithValidData() {
        $user = new User(self::$pdo);
        $username = 'testuser';
        $email = 'testuser@example.com';
        $password = 'password123';

        $result = $user->registerUser($username, $email, $password);
        $this->assertTrue($result, "User registration failed with valid data");

        $stmt = self::$pdo->prepare("SELECT * FROM Users WHERE username = ?");
        $stmt->execute([$username]);
        $userRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($userRecord, "User record not found in database");
        $this->assertEquals($email, $userRecord['email'], "Email mismatch in database");
        $this->assertTrue(password_verify($password, $userRecord['password']), "Password not properly hashed");
    }

    public function testRegisterDuplicateUser() {
        $user = new User(self::$pdo);
        $username = 'duplicateuser';
        $email = 'duplicate@example.com';
        $password = 'password123';

        // First registration should succeed
        $firstResult = $user->registerUser($username, $email, $password);
        $this->assertTrue($firstResult, "First user registration failed");

        // Attempt to register the same username
        $secondResult = $user->registerUser($username, 'different@example.com', 'differentpassword');
        $this->assertFalse($secondResult, "Duplicate username registration should fail");

        // Attempt to register the same email
        $thirdResult = $user->registerUser('differentuser', $email, 'differentpassword');
        $this->assertFalse($thirdResult, "Duplicate email registration should fail");
    }

    // Add other test methods as needed...
}
