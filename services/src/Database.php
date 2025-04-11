<?php

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../data');
$dotenv->load();


class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    // Constructor to initialize the database connection
    public function __construct() {
        // Assign values from $_ENV to class properties
        $this->host = $_ENV['HOST_NAME'];
        $this->db_name = $_ENV['DATABASE'];
        $this->username = $_ENV['USER_NAME'];
        $this->password = $_ENV['PASSWORD'];
        
        // Establish the database connection
        //$this->connect();
    }

    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
