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

$app->post('/exits', function() use ($app) {
    // initialize response array
    $response = [];
    // extract post body
    $r = json_decode($app->request->getBody());
    // check required fields
    verifyRequiredParams(['fex_staff_id','fex_location_id','fex_lat','fex_long','fex_code_expected', 'fex_code_supplied', 'fex_is_safe'],$r->fence_exit);
    // instantiate classes
    $db = new DbHandler();
    // insert exit
    $exit = $r->fence_exit;
    $fex_id = $db->insertColumnsToTable('fence_exit', [
        'fex_staff_id' => $exit->fex_staff_id,
        'fex_location_id' => $exit->fex_location_id,
        'fex_lat' => $exit->fex_lat,
        'fex_long' => $exit->fex_long,
        'fex_time_exited' => date("Y-m-d H:i:s"),
        'fex_code_expected' => $exit->fex_code_expected,
        'fex_code_supplied' => $exit->fex_code_supplied,
        'fex_is_safe' => $exit->fex_is_safe
    ]);
    if($fex_id) {
        // insert done
        $response['fex_id'] = $fex_id;
        $response['status'] = "success";
        $response["message"] =  "Fence Exit recorded successfully!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Exit record failed!";
        echoResponse(201, $response);
    }
});