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
    // return list of staff
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
    // return list of staff
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