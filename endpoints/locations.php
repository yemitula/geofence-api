<?php
// list 
$app->get('/locations', function() use ($app) {
    // authAdmin();
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
// single staff
// $app->get('/resources/:id', function($id) use ($app) {
//     // authAdmin();
//     // initialize response array
//     $response = [];
//     // database handler
//     $db = new DbHandler();
//     // compose sql query
//     $query = "SELECT *,
//         (SELECT COUNT(*) FROM customer_resource WHERE cr_resource_id = rsc_id) AS rsc_like_count 
//         FROM resource LEFT JOIN resource_category ON rsc_category_id=rcat_id WHERE rsc_id = '$id' ";
//     // run query
//     $resource = $db->getOneRecord($query);
//     // return resource
//     if($resource) {
//         $resource_faqs = $db->getRecordset("SELECT * FROM resource_faq WHERE rfaq_resource_id='$resource[rsc_id]' ");
//         // determine if viewing customer likes resource
//         $cust_id = $db->purify($app->request->get('customer'));
//         $resource['rsc_is_liked'] = '0';
//         if($cust_id) {
//             $liked = $db->getOneRecord("SELECT COUNT(*) AS like_count FROM customer_resource WHERE cr_customer_id = '$cust_id' AND cr_resource_id = '$id'");
//             if($liked && $liked['like_count'] > 0) {
//                 $resource['rsc_is_liked'] = '1';
//             }

//         }
//         $response['resource'] = $resource;
//         $response['resource_faqs'] = $resource_faqs;
//     	$response['status'] = "success";
//         $response["message"] =  " Resource found!";
//         echoResponse(200, $response);
//     } else {
//     	$response['status'] = "error";
//         $response["message"] = "Resource not found!";
//         echoResponse(201, $response);
//     }
// });
