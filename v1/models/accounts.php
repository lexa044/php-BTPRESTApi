<?php
  class accounts{
 
    // database connection and table name
    private $conn;
    private $table_name = "api_accounts";
 
    // object properties
    public $id;
    public $code;
    public $secret;
    public $description;
    public $merchant_id;
    public $status_id;


    // constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }


    // create a random password 
    function pwdGenerator() {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789!?~@#-_+<>[]{}";
        $pass = array(); 
        $alphaLength = strlen($alphabet) - 1; 
        for ($i = 0; $i < 18; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); 
    }


    // get basic information from accounts
    function getAccountsInfo() {
        $query = "SELECT
                    tb1.id as account_id, tb1.code as account_code, tb1.description as account_description, tb1.secret as account_secret, tb1.status_id, tb2.name as account_status
                FROM " . $this->table_name . " tb1
                    INNER JOIN api_status tb2 ON tb1.status_id = tb2.id
                    INNER JOIN api_merchants tb3 ON tb1.merchant_id = tb3.id 
                WHERE tb1.merchant_id = " . $this->merchant_id  . "                  
                ORDER BY tb1.id ASC";
     
        $stmtAccount = $this->conn->prepare($query);
        $stmtAccount->execute();
     
        return $stmtAccount;
    }


    // get basic information from accounts
    function getAccountsCode() {
        $query = "SELECT
                    tb1.id as account_id, tb1.code as account_code, tb1.description as account_description, tb1.secret as account_secret, tb1.balance as account_balance, tb1.status_id as account_status
                FROM " . $this->table_name . " tb1
                WHERE tb1.merchant_id= " . $this->merchant_id . "
                  AND tb1.code='" . $this->code . "'
                  LIMIT 0,1";
     
        $stmtAccount = $this->conn->prepare($query);
        $stmtAccount->execute();
     
        return $stmtAccount;
    }


    // create an accounts
    function addAccounts() {
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    merchant_id=:merchant_id,
                    code=:code,
                    secret=:secret,
                    description=:description,                    
                    status_id=:status_id";
     
        $stmtAccount = $this->conn->prepare($query);
     
        $this->merchant_id=htmlspecialchars(strip_tags($this->merchant_id));
        $this->code=htmlspecialchars(strip_tags($this->code));
        $this->secret=htmlspecialchars(strip_tags($this->secret));
        $this->description=htmlspecialchars(strip_tags($this->description));
        $this->status_id=htmlspecialchars(strip_tags($this->status_id));
        
        $stmtAccount->bindParam(":merchant_id", $this->merchant_id);        
        $stmtAccount->bindParam(":code", $this->code);
        $stmtAccount->bindParam(":secret", $this->secret);
        $stmtAccount->bindParam(":description", $this->description);
        $stmtAccount->bindParam(":status_id", $this->status_id);
        
        if($stmtAccount->execute()){
            return true;
        }
     
        return false;         
    }


  }