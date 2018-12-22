<?php
class rpcClass {

    private $hostname = "127.0.0.1";
    private $hostport = "8443";
    private $username = "RPCUSER";
    private $password = "RPCUSERPASSWORD";

    private $uri;
    private $jsonrpc;

    function __construct(){
        $this->uri = "http://" . $this->username . ":" . $this->password . "@" . $this->hostname . ":" . $this->hostport . "/";
        $this->jsonrpc = new jsonRPCClient($this->uri);
    }

    function getAccount($account_code){
        return $this->jsonrpc->getaddressesbyaccount("zelles(" . $account_code . ")");
    }

    function getBalance($account_code){
        return $this->jsonrpc->getbalance("zelles(" . $account_code . ")", 6);
        //return 21;
    }

    function getAddress($account_code){
        return $this->jsonrpc->getaccountaddress("zelles(" . $account_code . ")");
    }

    function getAddressList($account_code){
        return $this->jsonrpc->getaddressesbyaccount("zelles(" . $account_code . ")");
        //return array("1test", "1test");
    }

    function getTransaction($transaction_id){
        return $this->jsonrpc->gettransaction("zelles(" . $transaction_id . ")");
    }

    function getTransactionList($account_code){
        return $this->jsonrpc->listtransactions("zelles(" . $account_code . ")", 10);
    }

    function getNewAddress($account_code){
        return $this->jsonrpc->getnewaddress("zelles(" . $account_code . ")");
        //return "1test";
    }

    function withdraw($account_code, $address, $amount){
        return $this->jsonrpc->sendfrom("zelles(" . $account_code . ")", $address, (float)$amount, 6);
        //return "ok wow";
    }
}
?>
