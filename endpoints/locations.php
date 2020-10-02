<?php
// list 
$app->get('/locations', function() use ($app) {
    // initialize response array
    $response = [];
    // database handler
    $db = new DbHandler();
    // compose sql query
    $query = "SELECT * FROM `location`";
    // run query
    $locations = $db->getRecordset($query);
    // return list of locations
    if($locations) {
    	$response['locations'] = $locations;
    	$response['status'] = "success";
        $response["message"] =  count($locations) . " location(s) found!";
        echoResponse(200, $response);
    } else {
    	$response['status'] = "error";
        $response["message"] = "No location found!";
        echoResponse(201, $response);
    }
});
// get single locations
$app->get('/locations/:locId', function($locId) use ($app) {
    // initialize response array
    $response = [];
    // database handler
    $db = new DbHandler();
    // compose sql query
    $query = "SELECT * FROM `location` WHERE loc_id = '$locId'";
    // run query
    $location = $db->getOneRecord($query);
    // return location
    if($location) {
    	$response['location'] = $location;
    	$response['status'] = "success";
        $response["message"] =  "location found!";
        echoResponse(200, $response);
    } else {
    	$response['status'] = "error";
        $response["message"] = "No location found!";
        echoResponse(201, $response);
    }
});
// edit location
$app->put('/locations', function() use ($app) {
    // authAdmin();
    // initialize response array
    $response = [];
    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['loc_id','loc_name','loc_lat','loc_long','loc_address','loc_radius'],$r->location);
    // database handler
    $db = new DbHandler();
    $loc_id = $db->purify($r->location->loc_id);
    $loc_name = $db->purify($r->location->loc_name);
    $loc_lat = $db->purify($r->location->loc_lat);
    $loc_long = $db->purify($r->location->loc_long);
    $loc_address = $db->purify($r->location->loc_address);
    $loc_radius = $db->purify($r->location->loc_radius);
    // check if loc_lat or loc_long is already added
    $loc_check  = $db->getOneRecord("SELECT loc_id FROM `location` WHERE loc_lat = '$loc_lat' AND loc_long='$loc_long' AND loc_id<>$loc_id");
    if($loc_check) {
        // location already exists
        $response['status'] = "error";
        $response["message"] = "Location already Exists!";
        echoResponse(201, $response);
    }else {
         //update location
         $update_location = $db->updateInTable(
        	"location", /*table*/
        	[ 'loc_name'=>$loc_name, 'loc_lat' => $loc_lat, 'loc_long' => $loc_long, 'loc_address' => $loc_address, 'loc_radius' => $loc_radius ], /*columns*/
        	[ 'loc_id'=>$loc_id ] /*where clause*/
        );
        // return location
        if($update_location>0) {
            $response['status'] = "success";
            $response["message"] = "Location udpated successfully!";
            echoResponse(200, $response);
        } else {
            $response['status'] = "error";
            $response["message"] = "Something went wrong while trying to update the location!";
            echoResponse(201, $response);
        }
    }
});
// create location
$app->post('/locations', function() use ($app) {
    // initialize response array
    $response = [];
    // extract post body
    $r = json_decode($app->request->getBody());
    // check required fields
    verifyRequiredParams(['loc_name','loc_lat','loc_long','loc_address','loc_radius'],$r->location);
    // instantiate classes
    $db = new DbHandler();
    
    $loc_name = $db->purify($r->location->loc_name);
    $loc_lat = $db->purify($r->location->loc_lat);
    $loc_long = $db->purify($r->location->loc_long);
    $loc_address = $db->purify($r->location->loc_address);
    $loc_radius = $db->purify($r->location->loc_radius);        
    // check if loc_lat or loc_long is already added
    $loc_check  = $db->getOneRecord("SELECT loc_id FROM `location` WHERE loc_lat = '$loc_lat' AND loc_long='$loc_long' ");
        if($loc_check) {
           // location already exists
        $response['status'] = "error";
        $response["message"] = "Location already Exists!";
        echoResponse(201, $response);
        } else {
            //create new location
            $loc_id = $db->insertToTable(
                [ $loc_name, $loc_lat, $loc_long, $loc_address, $loc_radius ], /*values - array*/
                [ 'loc_name','loc_lat','loc_long','loc_address','loc_radius'], /*column names - array*/
                "location" /*table name - string*/
            );
            // location created successfully?
            if($loc_id) {
                $response['loc_id'] = $loc_id;
                $response['status'] = "success";
                $response["message"] = "Location Created Successfully!";
                echoResponse(200, $response);
            } else {
                $response['status'] = "error";
                $response["message"] = "Something went wrong while trying to create the location!";
                echoResponse(201, $response);
            }
    }
});

// delete location
$app->delete('/location/:id', function($id) use ($app) {
    // only super admins allowed
    // authAdmin();
    // initialize response array
    $response = [];
    // database handler
    $db = new DbHandler();
    $loc_delete = $db->deleteFromTable("location", "loc_id", $id);
    // deleted?
    if($loc_delete) {
    	$response['status'] = "success";
        $response["message"] =  "Location deleted successfully";
        echoResponse(200, $response);
    } else {
    	$response['status'] = "error";
        $response["message"] = "Location DELETE failed!";
        echoResponse(201, $response);
    }
});