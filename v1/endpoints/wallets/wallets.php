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
                    getWalletsInfo();
                    break;
                
                case 'POST':
                    addWalletsInfo();
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



    function getWalletsInfo() {

        if(isset($_GET['merchant'])) {

            // include database and object files
            include_once '../../config/mysql.php';
            include_once '../../models/wallets.php';

            // initialize objects
            $database = new mysql();
            $db = $database->getConnection();
            $wallets = new accountswallet($db);

            $wallets->merchant_id = $_GET['merchant'];
            $stmtWallet = $wallets->getWalletsInfo();
            $records = $stmtWallet->rowCount();

            if($records>0) { 
                $wallet_array=array();
                $wallet_array["wallets"]=array();

                while ($row = $stmtWallet->fetch(PDO::FETCH_ASSOC)){
                    extract($row);
                    $wallet_item=array("account" => $account_code,
                                       "id" => $wallet_id,
                                       "address" => $wallet_address,                                    
                                       "balance" => $wallet_balance,
                                       "description" => $wallet_description,
                                       "status" => $wallet_status);

                    array_push($wallet_array["wallets"], $wallet_item);
                }

                http_response_code(200);
                echo json_encode($wallet_array);                            
            }
            else {
                http_response_code(404);
                echo json_encode(
                    array("error" => "Not Found",
                          "message" => "No wallets associated with the merchant were found.")
                );
            }
        }
        else {
            http_response_code(401);
            echo json_encode(
                array("error" => "Unauthorized",
                      "message" => "Merchant Code is required field. Data is incomplete.")
            );
        }
    }



    function addWalletsInfo() {

        $data = json_decode(file_get_contents("php://input"));
        if(!empty($data->merchant)) {
            if(!empty($data->account)) { 

                // include database and object files
                include_once '../../config/mysql.php';
                include_once '../../models/wallets.php';

                include_once '../../others/jsonRPCClient.php';
                include_once '../../config/rpc.php';

                // initialize objects
                $database = new mysql();
                $db = $database->getConnection();
                $wallets = new accountswallet($db);

                $wallets->merchant_id = $data->merchant;
                $wallets->account_code = $data->account;
                if(!empty($data->description)){
                    $wallets->description = $data->description;
                } 
                else {
                    $wallets->description = "API Betchip Wallet";
                }
                $wallets->cryptocurrency_id = 1;
                $wallets->balance = "0.00";
                $wallets->status_id = 1;


                if($wallets->addWallets()) {
                    $last_wallet_id = $db->lastInsertId();
                     $wallets->id = $last_wallet_id;

                    // set address hash on betchip node
                    $rpcnode = new rpcClass();
                    $hash_address = $rpcnode->getNewAddress($data->account);
                    $wallets->address = $hash_address;

                    // update address from node
                    if($wallets->setAddress()){
                        http_response_code(201);
                        echo json_encode(
                            array("account" => $data->account,
                                  "id" => $last_wallet_id, 
                                  "address" => $hash_address,
                                  "balance" => $wallets->balance,
                                  "description" => $wallets->description,
                                  "message" => "Betchip wallet was created.")
                        );
                    }
                    else {
                        http_response_code(503);
                        echo json_encode(
                            array("error" => "Service Unavailable",
                                  "message" => "Unable to update Betchip address. Please contact Betchip stuff.")
                        );
                    }
                }
                else { 
                    http_response_code(503);
                    echo json_encode(
                        array("error" => "Service Unavailable",
                              "message" => "Unable to create Betchip account. Please try again later.")
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
                      "message" => "Merchant Code is required field. Data is incomplete.")
            );
        }
    }
