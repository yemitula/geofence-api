<?php
// list 
$app->get('/notifications', function() use ($app) {
    // initialize response array
    $response = [];
    // database handler
    $db = new DbHandler();
    // compose sql query
    $query = "SELECT * FROM exit_notification" ;
    // run query
    $notifications = $db->getRecordset($query);
    // return list of notifications
    if($notifications) {
    	$response['notifications'] = $notifications;
    	$response['status'] = "success";
        $response["message"] =  count($notifications) . " notification(s) found!";
        echoResponse(200, $response);
    } else {
    	$response['status'] = "error";
        $response["message"] = "No notification found!";
        echoResponse(201, $response);
    }
});