<?php
// error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
// Configuration
$USE_MOCK_DB = $_ENV['USE_MOCK_DB'] ?? false;

class DatabaseWrapper {
    private $conn;
    private $isMock;

    public function __construct() {
        $this->isMock = $_ENV['USE_MOCK_DB'] ?? false;
        if ($this->isMock) {
            // PHP MySQL Engine connection (PDO)
            try {
              
                $dsn = 'mysql:host=localhost;dbname=' . ($_ENV['DB_NAME'] ?? 'mydatabase') . ';charset=utf8mb4;collation=utf8mb4_unicode_ci;';

                $this->conn = new \Vimeo\MysqlEngine\Php8\FakePdo(
                    $dsn,
                    $_ENV['DB_USER'] ?? 'root',
                    $_ENV['DB_PASS'] ?? '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
                
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
                $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                // Test the connection
                $testQuery = $this->conn->query('SELECT 1');
                if ($testQuery === false) {
                    throw new \Exception('Unable to execute test query');
                }

            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        } else {
            // Real MySQL connection (mysqli)
            $this->conn = new mysqli(
                $_ENV['DB_HOST'] ?? '0.0.0.0',
                $_ENV['DB_USER'] ?? 'root',
                $_ENV['DB_PASS'] ?? '',
                $_ENV['DB_NAME'] ?? 'mydatabase'
            );

            if ($this->conn->connect_error) {
                die("Connection failed: " . $this->conn->connect_error);
            }
        }

        $this->initializeDatabase();
    }

    private function initializeDatabase() {
        $createTable = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            facial_id VARCHAR(255) UNIQUE
        ) CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if ($this->isMock) {
            try {
                $this->conn->query($createTable);
            } catch (PDOException $e) {
                die("Error creating table: " . $e->getMessage());
            }
        } else {
            if (!$this->conn->query($createTable)) {
                die("Error creating table: " . $this->conn->error);
            }
        }
    }

    public function associateFacialId($userId, $facialId) {
        if ($this->isMock) {
            try {
                $stmt = $this->conn->prepare("INSERT INTO users (user_id, facial_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE facial_id = ?");
                $stmt->execute([$userId, $facialId, $facialId]);
                return true;
            } catch (PDOException $e) {
                error_log("Error associating facial ID: " . $e->getMessage());
                return false;
            }
        } else {
            $stmt = $this->conn->prepare("INSERT INTO users (user_id, facial_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE facial_id = ?");
            $stmt->bind_param("sss", $userId, $facialId, $facialId);

            if ($stmt->execute()) {
                return true;
            } else {
                error_log("Error associating facial ID: " . $stmt->error);
                return false;
            }
        }
    }

    public function getUserIdByFacialId($facialId) {
        if ($this->isMock) {
            try {
                $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE facial_id = ?");
                $stmt->execute([$facialId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result ? $result['user_id'] : null;
            } catch (PDOException $e) {
                error_log("Error getting user ID: " . $e->getMessage());
                return null;
            }
        } else {
            $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE facial_id = ?");
            $stmt->bind_param("s", $facialId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['user_id'];
            }
            return null;
        }
    }

    public function checkUserExistsByFacialId($facialId) {
        if ($this->isMock) {
            try {
                $stmt = $this->conn->prepare("SELECT 1 FROM users WHERE facial_id = ?");
                $stmt->execute([$facialId]);
                return $stmt->fetch() !== false;
            } catch (PDOException $e) {
                error_log("Error checking user existence: " . $e->getMessage());
                return false;
            }
        } else {
            $stmt = $this->conn->prepare("SELECT 1 FROM users WHERE facial_id = ?");
            $stmt->bind_param("s", $facialId);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        }
    }

    public function close() {
        if (!$this->isMock) {
            $this->conn->close();
        }
        // PDO connections are automatically closed when the object is destroyed
    }
}

// Create a global database instance
$db = new DatabaseWrapper();

// Wrapper functions to maintain backward compatibility
function associateFacialId($userId, $facialId, $conn) {
    global $db;
    return $db->associateFacialId($userId, $facialId);
}

function getUserIdByFacialId($facialId, $conn) {
    global $db;
    return $db->getUserIdByFacialId($facialId);
}

function checkUserExistsByFacialId($facialId, $conn) {
    global $db;
    return $db->checkUserExistsByFacialId($facialId);
}

function closeDatabaseConnection($conn) {
    global $db;
    $db->close();
}
?>
