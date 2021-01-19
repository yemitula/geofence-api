<?php
// list 
$app->get('/movements', function() use ($app) {
    // authAdmin();
    // initialize response array
    $response = [];
    // database handler
    $db = new DbHandler();
    $mov_id = $db->purify($app->request->get('mov_id'));
    $fex_id = $db->purify($app->request->get('fex_id'));
    // compose sql query
    $query = "SELECT * FROM movement 
        LEFT JOIN fence_exit ON mov_exit_id=fex_id 
        LEFT JOIN staff ON fex_staff_id=stf_id 
        WHERE 1=1";
    if($mov_id!="" && $mov_id!="undefined") {
        $query .= " AND mov_id = '$mov_id'";
    }
    if($fex_id) {
        $query .= " AND mov_exit_id = '$fex_id'";
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

$app->post('/movements', function() use ($app) {
    // initialize response array
    $response = [];
    // extract post body
    $r = json_decode($app->request->getBody());
    // check required fields
    verifyRequiredParams(['mov_exit_id','mov_lat','mov_long'],$r->movement);
    // instantiate classes
    $db = new DbHandler();
    // insert exit
    $movement = $r->movement;
    $mov_id = $db->insertColumnsToTable('movement', [
        'mov_exit_id' => $movement->mov_exit_id,
        'mov_lat' => $movement->mov_lat,
        'mov_long' => $movement->mov_long,
        'mov_time' => date("Y-m-d H:i:s")
    ]);
    if($mov_id) {
        // insert done
        $response['mov_id'] = $mov_id;
        // stop the movements?
        $stop = $app->request->get('stop');
        if($stop) {
            // update the exit
            $response['exit_stopped'] = $db->updateInTable(
                'fence_exit',
                [ 'fex_time_returned' => date("Y-m-d H:i:s") ],
                [ 'fex_id' => $movement->mov_exit_id ]
            );
        }
        $response['status'] = "success";
        $response["message"] =  "Movement logged successfully!";
        echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Movement logging failed!";
        echoResponse(201, $response);
    }
});