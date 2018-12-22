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
                    getAccountsInfo();
                    break;
                
                case 'POST':
                    addAccountsInfo();
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



    function getAccountsInfo() {

        if(isset($_GET['merchant'])) {

            // include database and object files
            include_once '../../config/mysql.php';
            include_once '../../models/accounts.php';

            // initialize objects
            $database = new mysql();
            $db = $database->getConnection();
            $accounts = new accounts($db);

            $accounts->merchant_id = $_GET['merchant'];
            $stmtAccount = $accounts->getAccountsInfo();
            $records = $stmtAccount->rowCount();

            if($records>0) { 
                $account_array=array();
                $account_array["accounts"]=array();

                while ($row = $stmtAccount->fetch(PDO::FETCH_ASSOC)){
                    extract($row);
                    $account_item=array("code" => $account_code,
                                        "password" => $account_secret,
                                        "description" => $account_description,
                                        "status" => $account_status);

                    array_push($account_array["accounts"], $account_item);
                }

                http_response_code(200);
                echo json_encode($account_array);                            
            }
            else {
                http_response_code(404);
                echo json_encode(
                    array("error" => "Not Found",
                          "message" => "No accounts associated with the merchant were found.")
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



    function addAccountsInfo() {

        $data = json_decode(file_get_contents("php://input"));
        if(!empty($data->merchant)) {
            if(!empty($data->code)) { 

                // include database and object files
                include_once '../../config/mysql.php';
                include_once '../../models/accounts.php';

                // initialize objects
                $database = new mysql();
                $db = $database->getConnection();
                $accounts = new accounts($db);

                $accounts->merchant_id = $data->merchant;
                $accounts->code = $data->code;
                $accounts->secret = md5($accounts->pwdGenerator());
                if(!empty($data->description)){
                    $accounts->description = $data->description;
                } 
                else {
                    $accounts->description = "API Betchip Account";
                }
                $accounts->status_id = 1;

                if($accounts->addAccounts()) {
                    $last_account_id = $db->lastInsertId();
                    http_response_code(201);
                    echo json_encode(
                        array(//"account_id" => $last_account_id,
                              "code" => $data->code,
                              "secret" => $accounts->secret,  
                              "description" => $accounts->description,
                              "message" => "Betchip account was created.")
                    );
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
