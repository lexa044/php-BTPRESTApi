<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
 
$request_method=$_SERVER["REQUEST_METHOD"];
if(($request_method) == 'POST') {

    // get posted data
    $data = json_decode(file_get_contents("php://input"));

    if(!empty($data->username) &&
       !empty($data->password)) {

        if(!empty($data->merchant)) {            

            // include database objects
            include_once 'config/mysql.php';            
            include_once 'models/merchants.php';
            include_once 'shared/encrypt.php';

            // initialize objects
            $database = new mysql();
            $db = $database->getConnection();
            $token_auth = new merchants($db);

            // set query arguments
            $token_auth->id = $data->merchant;
            $token_auth->uuid = $data->username;
            $token_auth->secret = $data->password;

            $stmtMerchant = $token_auth->getMerchantAuth();
            $record = $stmtMerchant->rowCount();

            if($record>0){ 
                $merchant_key = $stmtMerchant->fetch(PDO::FETCH_ASSOC)['merchant_key'];

                $token_id = uniqid('',true);
                $token_jti = $merchant_key;
                $token_iss = gethostname();
                $token_aud = $data->username;
                $token_sub = $data->merchant;
                $token_iat = date('YmdHis');
                $token_exp = date("YmdHis", time() + 864000);
                $token_type = "bearer";

                $encrypted= new encrypt();
                $token_hash = $encrypted->getSessionToken( $token_exp . '|' . $token_id  . '|' . 
                                                           $token_iss . '|' . $token_jti . '|' .
                                                           $token_aud . '|' . $token_sub . '|' .
                                                           $token_iat . '|' . $token_exp . '|' . $token_type, 'e' );
                http_response_code(200);
                echo json_encode(                    
                    array("token_type" => "Bearer",
                          "access_token" => $token_hash,
                          "token_expires" => $token_exp)
                );
            }
            else {
                http_response_code(403);
                echo json_encode(
                    array("error" => "Unauthorized",
                          "message" => "The credentials you supplied were not correct. Invalid Access.")
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
    else {
        http_response_code(403);    
        echo json_encode(        
            array("error" => "Forbidden",
                  "message" => "Unauthenticated connection. Credencials are required.")
        );
    }
}
else {
    http_response_code(405);
    echo json_encode(
        array("error" => "Method Not Allowed",
              "message" => "A request method is not supported for the requested resource.")
    );
}