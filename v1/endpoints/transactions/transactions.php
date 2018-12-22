<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 3600");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 

$request_headers = apache_request_headers();
if(isset($request_headers['Authorization'])) {
    $authorization_key = array();
    preg_match('/Bearer\s(\S+)/', $request_headers['Authorization'], $authorization_key);
    if(isset($authorization_key[1])) {

        $token_post = $authorization_key[1];

        include_once '../../shared/encrypt.php';
        $dencrypted= new encrypt();
        $token_hash = $dencrypted->getSessionToken($token_post, 'd');

        $date = date('YmdHis');
        $token_exp = strstr($token_hash, '|', true);

        if (intval($token_exp) > intval($date)) {

            $request_method=$_SERVER["REQUEST_METHOD"];
            switch ($request_method) {
                case 'GET':
                    //getTransactionsInfo();
                    break;
                
                case 'POST':
                    addTransactions();
                    break;

                default:
                    http_response_code(405);
                    echo json_encode(
                        array("error" => "Method Not Allowed",
                              "message" => "A request method is not supported for the requested resource.")
                    );
                    break;
            }
        }
        else {
            http_response_code(401);
            echo json_encode(
                array("error" => "Unauthorized",
                      "message" => "Your Access Token is expired. Please regenerate your Access Token.")
            );
        }
    }
    else {
        http_response_code(401);
        echo json_encode(
            array("error" => "Unauthorized",
                  "message" => "Your Access Token is incorrectly specified. Invalid Access.")
        );
    }
}
else {
    http_response_code(401);
    echo json_encode(
        array("error" => "Unauthorized",
              "message" => "No token provided in the request header. Invalid Access.")
    );
} 




    function addTransactions() {

        $data = json_decode(file_get_contents("php://input"));
        if(!empty($data->merchant)) {
            if(!empty($data->account)) { 
                if(!empty($data->source)) { 
                    if(!empty($data->destination)) { 
                        if(!empty($data->amount)) { 

                            // include database and object files
                            include_once '../../config/mysql.php';
                            include_once '../../models/accounts.php';
                            include_once '../../models/wallets.php';


                            // initialize odabatase objects
                            $database = new mysql();
                            $db = $database->getConnection();

                            $accounts = new accounts($db);
                            $accounts->merchant_id = $data->merchant;
                            $accounts->code = $data->account;

                            $stmtAccount = $accounts->getAccountsCode();
                            $records = $stmtAccount->rowCount();
                            if($records>0) { 

                                $wallets = new accountswallet($db);
                                $wallets->account_code = $data->account;
                                $wallets->address = $data->source;

                                $stmtWallet = $wallets->getWalletsAddress();
                                $records = $stmtWallet->rowCount();
                                if($records>0) {                            

                                    include_once '../../others/jsonRPCClient.php';
                                    include_once '../../config/rpc.php';

                                    //getBalance
                                    $rpcnode = new rpcClass();
                                    $address_balance = $rpcnode->getBalance($data->account);
                                    if ($address_balance > $data->amount) {

                                        $transaction_id = $rpcnode->withdraw($data->account, $data->source, $data->amount);

                                        http_response_code(201);
                                        echo json_encode(
                                            array("account" => $data->account,
                                                  "address" => $data->source,
                                                  "destination" => $data->destination,
                                                  "txid" => $transaction_id,
                                                  "amount" => $data->amount,
                                                  //"description" => $accounts->description,
                                                  "message" => "Betchip transaction was processed.")
                                        );
                                        //echo $transaction_id;
                                    }
                                    else {
                                        http_response_code(401);
                                        echo json_encode(
                                            array("error" => "Unauthorized",
                                                  "message" => "Account Balance is invalid. Data is incomplete.")
                                        );                                                                        
                                    }
                                }
                                else { 
                                    http_response_code(401);
                                    echo json_encode(
                                        array("error" => "Unauthorized",
                                              "message" => "Source Address is invalid. Data is incomplete.")
                                    ); 
                                } 
                            }
                            else {
                                http_response_code(401);
                                echo json_encode(
                                    array("error" => "Unauthorized",
                                          "message" => "Account Code is invalid. Data is incomplete.")
                                );                                                                        
                            }                            
                        }
                        else {
                            http_response_code(401);
                            echo json_encode(
                                array("error" => "Unauthorized",
                                      "message" => "Amount is required field. Data is incomplete.")
                            );                                                                        
                        }
                    }
                    else {
                        http_response_code(401);
                        echo json_encode(
                            array("error" => "Unauthorized",
                                  "message" => "Destination Addess is required field. Data is incomplete.")
                        );                                            
                    }
                }
                else {
                    http_response_code(401);
                    echo json_encode(
                        array("error" => "Unauthorized",
                              "message" => "Source Betchip Addess is required field. Data is incomplete.")
                    );                    
                }
            }
            else {
                http_response_code(401);
                echo json_encode(
                    array("error" => "Unauthorized",
                          "message" => "Account Code is required field. Data is incomplete.")
                );
            }
        }
        else {
           http_response_code(401);
           echo json_encode(
                array("error" => "Unauthorized",
                      "message" => "Merchant Code is required field. Data is incomplete." )
            );
        }
    }