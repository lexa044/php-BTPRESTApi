<?php
  class wallettransactions{
 
    // database connection and table name
    private $conn;
    private $table_name = "api_transactions";
 
    // object properties
    //public $id;
    public $merchant_id;
    public $account_code;
    public $date;
    public $type;
    public $source;
    public $destination;
    public $description;
    public $amount;
    public $fee;


    // constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }


    // get basic information from accounts




  }