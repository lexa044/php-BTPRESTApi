<?php
  class merchants{

    // database connection and table name
    private $conn;
    private $table_name = "api_merchants";


    // object properties
    public $id;
    public $uuid;
    public $secret;
    public $code;
    public $name;
    public $status_id;


    // constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }


    // get auth info from merchants
    function getMerchantAuth() {
        $query = "SELECT
                    tb1.id as merchant_id, tb1.uuid as merchant_uuid, tb1.code as merchant_code, tb1.privatekey as merchant_key, tb1.status_id as merchant_status 
                  FROM " . $this->table_name . " tb1 
                  WHERE tb1.uuid= '" . $this->uuid . "'
                  AND tb1.secret= '" . $this->secret . "'
                  AND tb1.id=" . $this->id . "
                  LIMIT 0,1";

        $stmtMerchant = $this->conn->prepare($query);
        $stmtMerchant->execute();

        return $stmtMerchant;        
    }


    // get basic information from merchants
    function getMerchantInfo() {
        $query = "SELECT
                    tb1.id as merchant_id, tb1.uuid as merchant_uuid, tb1.code as merchant_code, tb1.name as merchant_name, tb1.status_id, tb2.name as merchant_status
                  FROM " . $this->table_name . " tb1 
                    INNER JOIN api_status tb2 ON tb1.status_id = tb2.id
                  WHERE tb1.uuid= '" . $this->uuid . "'";

        $stmtMerchant = $this->conn->prepare($query);
        $stmtMerchant->execute();

        return $stmtMerchant;
        }


  }