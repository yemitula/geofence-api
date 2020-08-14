<?php
// list 
$app->get('/movements', function() use ($app) {
    // authAdmin();
    // initialize response array
    $response = [];
    // database handler
    $db = new DbHandler();
    $mov_id = $db->purify($app->request->get('mov_id'));
    // compose sql query
    $query = "SELECT * FROM movement LEFT JOIN fence_exit ON mov_exit_id=fex_id LEFT JOIN staff ON fex_staff_id=stf_id WHERE 1=1";
    if($mov_id!="" && $mov_id!="undefined") {
        $query .= " AND mov_id = '$mov_id'";
    }
    // run query
    $movements = $db->getRecordset($query);
    // return list of movements
    if($movements) {
    	$response['movements'] = $movements;
    	$response['status'] = "success";
        $response["message"] =  count($movements) . " movement(s) found!";
        echoResponse(200, $response);
    } else {
    	$response['status'] = "error";
        $response["message"] = "No movement found!";
        echoResponse(201, $response);
    }
});