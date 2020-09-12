<?php
//die('here');
date_default_timezone_set('Africa/Lagos');
ini_set('log_errors',TRUE);
ini_set('max_execution_time', 3600); //execution time in seconds

error_reporting(E_ALL ^ E_DEPRECATED); ini_set("display_errors", TRUE);
// apc_clear_cache();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Auth-Token, X-Requested-With, Content-Type, Accept, Authorization");

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// var_dump($_SERVER); die;

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
   header( "HTTP/1.1 200 OK" );
   exit();
}

require_once 'config.php';
require_once './vendor/autoload.php';
require_once './libs/Slim/Slim.php';
// require_once './uploads/server.js';
// include_once './uploads/server.js/api';

// include classes
foreach (glob("classes/*.php") as $filename)
{
    require_once $filename;
    // echo $filename. '<br>';
}

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// include functions
foreach (glob("endpoints/*.php") as $filename)
{
    require_once $filename;
}

// include file-upload
foreach (glob("uploads/*.php") as $filename)
{
    require_once $filename;
}

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields,$request_params,$labels=[]) {
    $error = false;
    $error_fields = "";
    foreach ($required_fields as $i=>$field) {
        if (!isset($request_params->$field) || strlen(trim($request_params->$field)) <= 0) {
            $error = true;
            // $error_fields .= $field . ', ';
            $error_fields .= (!empty($labels) && $labels[$i]) ? $labels[$i] . ', ' : $field . ', ' ;
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["status"] = "error";
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoResponse(200, $response);
        $app->stop();
    }
}

//send JSON response back to referrer
function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

// admin auth middleware
// extract JWT from request, verify it and check role if necessary
function authAdmin($role = 'admin') {
    // check if Auth is set in the request
    $headers = apache_request_headers();
    // var_dump($headers); die;
    if(isset($headers['Authorization'])) {
        // Auth present
        $auth_header = $headers['Authorization'];
        // extract JWT and verify
        list($jwt) = sscanf( $auth_header, 'Bearer %s');
        if($jwt) {
            // decode/verify jwt
            $jh = new JWTHandler();
            if($user = $jh->getUserFromToken($jwt)) {
                // valid JWT
                $response['user'] = $user;
                // check role
                if($role == 'super') {
                    // we need to check role
                    if($user->user_role == $role) {
                        // valid role, proceed, do nothing
                    } else {
                        // invalid role, return auth error
                        $response["status"] = "error";
                        $response["message"] = 'Access Denied! You cannot access this resource.';
                        echoResponse(401, $response);
                        exit;
                    }
                } else {
                    // no need to check role, just pass
                }
            } else {
                // invalid JWT, return auth error
                $response["status"] = "error";
                $response["message"] = 'Access Denied! Invalid authentication.';
                echoResponse(401, $response);
                exit;
            }
        } else {
            // couldn't get jwt, return auth error
            $response["status"] = "error";
            $response["message"] = 'Access Denied! Missing Authorization.';
            echoResponse(401, $response);
            exit;
        }
    } else {
        // Auth missing, return auth error
        $response["status"] = "error";
        $response["message"] = 'Access Denied! Missing Authentication Component.';
        echoResponse(401, $response);
        exit;
    }
}


$app->run();