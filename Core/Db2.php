<?php
namespace Core;

class Database {
    private $host;
    private $user;
    private $password;
    private $dbname;
    private $connection;

    public function __construct() {
        // Reference global constants using the backslash
        $this->host = \DB_HOST;
        $this->user = \DB_USER;
        $this->password = \DB_PASSWORD;
        $this->dbname = \DB_NAME;
        $this->connect();
    }

    // Establish connection using MySQLi
    private function connect() {
        $this->connection = new \mysqli($this->host, $this->user, $this->password, $this->dbname);
        if ($this->connection->connect_error) {
            die("Database connection failed: " . $this->connection->connect_error);
        }
    }

    // Return the active MySQLi connection
    public function getConnection() {
        return $this->connection;
    }

    // Execute a query and return the result or display an error
    public function query($sql) {
        $result = $this->connection->query($sql);
        if (!$result) {
            die("Query Error: " . $this->connection->error);
        }
        return $result;
    }

    // Escape string to avoid SQL injection
    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }
    
}
?>
