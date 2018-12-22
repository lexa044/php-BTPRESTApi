<?php
  class accountswallet{
 
    // database connection and table name
    private $conn;
    private $table_name = "api_accountswallet";
 
    // object properties
    public $id;
    public $address;
    public $balance;    
    public $description;
    public $merchant_id;
    public $account_code;
    public $cryptocurrency_id;
    public $status_id;


    // constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }


    // get basic information from wallets
    function getWalletsInfo(){
        $query = "SELECT
                    tb2.id as account_id, tb2.code as account_code, tb2.description as account_description, tb1.id as wallet_id, tb2.code as wallet_code, tb1.description as wallet_description, tb1.cryptocurrency_id, tb4.code as cryptocurrency_code, tb1.balance as wallet_balance, tb1.address as wallet_address, tb1.status_id, tb3.name as wallet_status
                  FROM " . $this->table_name . " tb1
                    INNER JOIN api_accounts tb2 ON tb1.account_code = tb2.code                    
                    INNER JOIN api_status tb3 ON tb1.status_id = tb3.id
                    INNER JOIN api_cryptocurrency tb4 ON tb1.cryptocurrency_id = tb4.id 
                  WHERE tb2.merchant_id = " . $this->merchant_id . "
                  ORDER BY tb1.id ASC";

        $stmtWallet = $this->conn->prepare($query);
        $stmtWallet->execute();
     
        return $stmtWallet;
    }


    // get wallet address information
    function getWalletsAddress(){
        $query = "SELECT
                    tb1.id as wallet_id, tb1.address as wallet_address, tb1.status_id as wallet_status
                  FROM " . $this->table_name . " tb1
                  WHERE tb1.account_code = '" . $this->account_code . "' 
                    AND tb1.address='" . $this->address . "'
                  LIMIT 0,1";                  

        $stmtWallet = $this->conn->prepare($query);
        $stmtWallet->execute();
     
        return $stmtWallet;
    }


    // create wallets
    function addWallets() {
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    address=:address,
                    balance=:balance,
                    description=:description,
                    account_code=:account_code,                    
                    cryptocurrency_id=:cryptocurrency_id,
                    status_id=:status_id";
     
        $stmtWallet = $this->conn->prepare($query);

        $this->address=htmlspecialchars(strip_tags($this->address));
        $this->balance=htmlspecialchars(strip_tags($this->balance));
        $this->description=htmlspecialchars(strip_tags($this->description));
        $this->account_code==htmlspecialchars(strip_tags($this->account_code));
        $this->cryptocurrency_id=htmlspecialchars(strip_tags($this->cryptocurrency_id));
        $this->status_id=htmlspecialchars(strip_tags($this->status_id));

        $stmtWallet->bindParam(":address", $this->address);
        $stmtWallet->bindParam(":balance", $this->balance);
        $stmtWallet->bindParam(":description", $this->description);
        $stmtWallet->bindParam(":account_code", $this->account_code);
        $stmtWallet->bindParam(":cryptocurrency_id", $this->cryptocurrency_id);        
        $stmtWallet->bindParam(":status_id", $this->status_id);
        
        if($stmtWallet->execute()){
            return true;
        }
     
        return false;
    }

    function setAddress() {
        $query = "UPDATE
                    " . $this->table_name . "
                SET
                    address=:address
                WHERE
                    id= " . $this->id;

        $stmtWallet = $this->conn->prepare($query);

        $this->address=htmlspecialchars(strip_tags($this->address));

        $stmtWallet->bindParam(':address', $this->address);
        if($stmtWallet->execute()){
            return true;
        }
     
        return false;
    }    

    
  }