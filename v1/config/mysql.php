<?php
  class mysql {

    // database parameters
    private $hostname = "localhost";
    private $username = "YOURUSERNAME";
    private $password = "YOURPASSWORD";
    private $database = "YOURDB";

    public $connection;

    // database connection
    public function getConnection(){

        $this->connection = null;

        try{
            $this->connection = new PDO("mysql:host=" . $this->hostname . ";dbname=" . $this->database, $this->username, $this->password);
            $this->connection->exec("set names utf8");
        }catch(PDOException $exception){
            echo "Error: " . $exception->getMessage();
        }

        return $this->connection;
    }
  }
?>