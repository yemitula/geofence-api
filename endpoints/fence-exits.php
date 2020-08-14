<?php
// list 
$app->get('/exits', function() use ($app) {
    // authAdmin();
    // initialize response array
    $response = [];
    // database handler
    $db = new DbHandler();
    $stf_id = $db->purify($app->request->get('stf_id'));
    // compose sql query
    $query = "SELECT * FROM fence_exit LEFT JOIN staff ON fex_staff_id=stf_id LEFT JOIN `location` ON fex_location_id=loc_id WHERE 1=1";
    if($stf_id!="" && $stf_id!="undefined") {
        $query .= " AND stf_id = '$stf_id'";
    }
    // run query
    $exits = $db->getRecordset($query);
    // return list of fence exits
    if($exits) {
    	$response['exits'] = $exits;
    	$response['status'] = "success";
        $response["message"] =  count($exits) . " exit(s) found!";
        echoResponse(200, $response);
    } else {
    	$response['status'] = "error";
        $response["message"] = "No exit found!";
        echoResponse(201, $response);
    }
});