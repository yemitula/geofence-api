<?php
// list 
$app->get('/staff', function() use ($app) {
    // authAdmin();
    // initialize response array
    $response = [];
    // database handler
    $db = new DbHandler();
    $loc_id = $db->purify($app->request->get('loc_id'));
    // compose sql query
    $query = "SELECT * FROM staff LEFT JOIN location ON stf_location_id=loc_id WHERE 1=1";
    if($loc_id!="" && $loc_id!="undefined") {
        $query .= " AND loc_id = '$loc_id'";
    }
    // run query
    $staff = $db->getRecordset($query);
    // return list of staff
    if($staff) {
    	$response['staff'] = $staff;
    	$response['status'] = "success";
        $response["message"] =  count($staff) . " staff found!";
        echoResponse(200, $response);
    } else {
    	$response['status'] = "error";
        $response["message"] = "No staff found!";
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
// create staff
$app->post('/staff', function() use ($app) {
    // initialize response array
    $response = [];
    // extract post body
    $r = json_decode($app->request->getBody());
    // check required fields
    verifyRequiredParams(['no','name','email','password','loc_id'],$r->staff);
    // instantiate classes
    $db = new DbHandler();
    
    $stf_no = $db->purify($r->staff->no);
    $stf_name = $db->purify($r->staff->name);
    $stf_email = $db->purify($r->staff->email);
    $stf_password = $db->purify($r->staff->password);
    $stf_location_id = $db->purify($r->staff->loc_id);
    //generate safety code
    $pg = new PasswordGenerator();
    $stf_safety_code = $pg->randomNumericCode();
    // var_dump("I AM HERE",$r->staff);die;
        
        // check if stf_no or stf_email is already added
        $stf_check  = $db->getOneRecord("SELECT stf_id FROM staff WHERE stf_no = '$stf_no' OR stf_email='$stf_email' ");
        if($stf_check) {
            // staff already exists
            $response['status'] = "error";
            $response["message"] = "Staff with Number or Email already Exists!";
            echoResponse(201, $response);
        } else {
            //create new staff
            $stf_id = $db->insertToTable(
                [ $stf_no, $stf_name, $stf_email, $stf_password, $stf_safety_code, $stf_location_id ], /*values - array*/
                [ 'stf_no','stf_name','stf_email','stf_password','stf_safety_code','stf_location_id'], /*column names - array*/
                "staff" /*table name - string*/
            );
            // staff created successfully?
            if($stf_id) {
                $response['stf_id'] = $stf_id;
                $response['status'] = "success";
                $response["message"] = "Device Configured Successfully!";
                echoResponse(200, $response);
            } else {
                $response['status'] = "error";
                $response["message"] = "Something went wrong while trying to configure your device!";
                echoResponse(201, $response);
            }
    }
});

// // create staff
// $app->post('/staff', function() use ($app) {
//     // initialize response array
//     $response = [];
//     // extract post body
//     $r = json_decode($app->request->getBody());
//     // check required fields
//     verifyRequiredParams(['no','name','email','password','loc_id'],$r->staff);
//     // instantiate classes
//     $db = new DbHandler();
    
//     $stf_no = $db->purify($r->staff->no);
//     $stf_name = $db->purify($r->staff->name);
//     $stf_email = $db->purify($r->staff->email);
//     $stf_password = $db->purify($r->staff->password);
//     $loc_id = $db->purify($r->staff->loc_id);
//     //generate safety code
//     $pg = new PasswordGenerator();
//     $stf_safety_code = $pg->randomNumericCode();
//     // $rsc_category_id =  isset($r->resource->rsc_category_id) ? $db->purify($r->resource->rsc_category_id) : '';
//     // $rsc_time = date("Y-m-d H:i:s");
//     // var_dump("I AM HERE",$r->staff);die;
        
//         // check if stf_no or stf_email is already used in the same category
//         $stf_check  = $db->getOneRecord("SELECT stf_id FROM staff WHERE stf_no = '$stf_no' OR stf_email='$stf_email' ");
//         if($stf_check) {
//             // staff already exists
//             $response['status'] = "error";
//             $response["message"] = "Staff with Number or Email already Exists!";
//             echoResponse(201, $response);
//         } else {
//             // check if location already exists
//             $loc_check  = $db->getOneRecord("SELECT loc_id FROM location WHERE loc_name = '$loc_name' AND loc_address = '$loc_address'");
//             if($loc_check){//set staff location
//                 $stf_location_id= $loc_check['loc_id'];
//             }
//             else{
//                 //create new location
//                 $loc_id = $db->insertToTable(
//                     [ $loc_name, $loc_lat, $loc_long, $loc_address, $loc_radius ], /*values - array*/
//                     [ 'loc_name','loc_lat','loc_long','loc_address','loc_radius'], /*column names - array*/
//                     "location" /*table name - string*/
//                 );
//                 $stf_location_id= $loc_id;
//             }
//             //create new staff
//             $stf_id = $db->insertToTable(
//                 [ $stf_no, $stf_name, $stf_email, $stf_password, $stf_safety_code, $stf_location_id ], /*values - array*/
//                 [ 'stf_no','stf_name','stf_email','stf_password','stf_safety_code','stf_location_id'], /*column names - array*/
//                 "staff" /*table name - string*/
//             );
//             // staff created successfully?
//             if($stf_id) {
//                 $response['stf_id'] = $stf_id;
//                 $response['status'] = "success";
//                 $response["message"] = "Device Configured Successfully!";
//                 echoResponse(200, $response);
//             } else {
//                 $response['status'] = "error";
//                 $response["message"] = "Something went wrong while trying to configure your device!";
//                 echoResponse(201, $response);
//             }
//     }
// });
//get group of people in an area
$app->get('/geofences', function() use ($app) {
    // authAdmin();
    // initialize response array
    $response = [];
    // database handler
    $db = new DbHandler();
    // compose sql query
    $query = "SELECT *,count(stf_id) as scount FROM staff LEFT JOIN location ON stf_location_id=loc_id GROUP BY loc_id";
    // run query
    $geofences = $db->getRecordset($query);
    // return list of geofences
    if($geofences) {
    	$response['geofences'] = $geofences;
    	$response['status'] = "success";
        $response["message"] =  count($geofences) . " geofences() found!";
        echoResponse(200, $response);
    } else {
    	$response['status'] = "error";
        $response["message"] = "No geofences found!";
        echoResponse(201, $response);
    }
});

// edit resource
$app->put('/resources', function() use ($app) {
    // only super admins allowed
    authAdmin();
    // initialize response array
    $response = [];
    // extract post body
    $r = json_decode($app->request->getBody());
    // check required fields
    verifyRequiredParams(['rsc_id', 'rsc_title','rsc_category_id','rsc_type','rsc_keywords'],$r->resource);
    // instantiate classes
    $db = new DbHandler();
    
    $rsc_id = $db->purify($r->resource->rsc_id);
    $rsc_title = $db->purify($r->resource->rsc_title);
    $rsc_category_id = $db->purify($r->resource->rsc_category_id);
    $rsc_type = $db->purify($r->resource->rsc_type);
    $rsc_keywords = $db->purify($r->resource->rsc_keywords);
    $rsc_image =  isset($r->resource->rsc_image) ? $db->purify($r->resource->rsc_image) : '';
    $rsc_content =  isset($r->resource->rsc_content) ? $db->purify($r->resource->rsc_content) : '';
    // check if rsc_title is already used with same category
    $rsc_check  = $db->getOneRecord("SELECT rsc_id FROM resource WHERE rsc_title = '$rsc_title' AND rsc_category_id='$rsc_category_id' AND rsc_id <> '$rsc_id' ");
    if($rsc_check) {
        // another resource already exists
        $response['status'] = "error";
        $response["message"] = "Another Resource with same name already Exists for this category!";
        echoResponse(201, $response);
    } else {
        //update update_resource
        $update_resource = $db->updateInTable(
        	"resource", /*table*/
        	[ 'rsc_title' => $rsc_title, 'rsc_category_id' => $rsc_category_id,'rsc_type' => $rsc_type, 'rsc_keywords' => $rsc_keywords,'rsc_content' => $rsc_content, 'rsc_image' => $rsc_image], /*columns*/
        	[ 'rsc_id'=>$rsc_id ] /*where clause*/
        );
        // resource updated successfully?
        if($update_resource >= 0) {
            if($rsc_type=='FAQ'){
                // delete all previous faqs aand readd
                $faqs_delete = $db->deleteFromTable("resource_faq", "rfaq_resource_id", $rsc_id);
                $faqs =$r->faqs;
                foreach ($faqs as $faq) {
                    // var_dump("faq=",$faq->rfaq_answer); die;
                    $apt = $db->insertToTable(
                        [$rsc_id, $faq->rfaq_question, $faq->rfaq_answer], /*values - array*/
                        ['rfaq_resource_id','rfaq_question','rfaq_answer'], /*column names - array*/
                        "resource_faq" /*table name - string*/
                        );
                }
            }
            // log admin action
            $lg = new Logger();
            $lg->logAction(" Updates a Resource");
            $response['status'] = "success";
            $response["message"] = "Resource udpated successfully!";
            echoResponse(200, $response);
        } else {
            $response['status'] = "error";
            $response["message"] = "Something went wrong while trying to update the Resource!";
            echoResponse(201, $response);
        }
    }
});
// delete resource
$app->delete('/resources/:id', function($id) use ($app) {
    // only super admins allowed
    authAdmin();
    // initialize response array
    $response = [];
    // database handler
    $db = new DbHandler();
    $resource_delete = $db->deleteFromTable("resource", "rsc_id", $id);
    // deleted?
    if($resource_delete) {
            // log admin action
            $lg = new Logger();
            $lg->logAction(" Deleted a Resource");
    	$response['status'] = "success";
        $response["message"] =  "Resource deleted successfully";
        echoResponse(200, $response);
    } else {
    	$response['status'] = "error";
        $response["message"] = "Resource DELETE failed!";
        echoResponse(201, $response);
    }
});
// like/unlike a resource
$app->put('/resources/like/:id', function($id) use ($app) {
    // initialize response array
    $response = [];
    // instantiate classes
    $db = new DbHandler();
    // id of resource
    $id = $db->purify($id);
    // extract post body
    $r = json_decode($app->request->getBody());
    // check required fields
    verifyRequiredParams(['cust_id'],$r->customer, ['Customer ID']);
    
    $cust_id = $db->purify($r->customer->cust_id);
    // check if customer currently likes the resource
    $cr_check  = $db->getOneRecord("SELECT * FROM customer_resource WHERE cr_customer_id = '$cust_id' AND cr_resource_id='$id' ");
    if($cr_check) {
        // customer likes resource
        $response['action'] = 'unlike';
        // remove the like
        $cr_deleted = $db->deleteFromTableWhere('customer_resource', [ 'cr_customer_id'=>$cust_id, 'cr_resource_id'=>$id ]);
    } else {
        //customer doesn't like the resource YET
        $response['action'] = 'like';
        // insert a new like
        $cr_inserted = $db->insertToTable(
            [ $cust_id, $id, date("Y-m-d H:i:s") ],
            [ 'cr_customer_id', 'cr_resource_id', 'cr_time' ],
            'customer_resource'
        );
        $cr = $db->getOneRecord("SELECT * FROM customer_resource WHERE cr_customer_id = '$cust_id' AND cr_resource_id='$id' ");
    }

    if((isset($cr_deleted) && $cr_deleted > 0) || (isset($cr) && $cr)) {
        // success
        $response['status'] = "success";
        $response["message"] = "Resource like updated successfully!";
        echoResponse(200, $response);
    } else {
        // failed
        $response['status'] = "error";
        $response["message"] = "Resource like update failed!";
        echoResponse(201, $response);
    }
});
// increment number of views
$app->get('/resources/views/:id', function($id) use ($app) {
    // initialize response array
    $response = [];
    // instantiate classes
    $db = new DbHandler();
    // id of resource
    $id = $db->purify($id);
    // check if customer currently likes the resource
    $resource  = $db->getOneRecord("SELECT * FROM resource WHERE rsc_id = '$id'");
    if($resource) {
        // resource found
        // update views
        $rsc_updated = $db->updateInTable(
            'resource',
            [ 'rsc_views'=>($resource['rsc_views']+1), 'rsc_time_viewed'=>date("Y-m-d H:i:s") ],
            [ 'rsc_id'=>$id ]
        );
        if($rsc_updated > 0) {
            // success
            $response['status'] = "success";
            $response["message"] = "Resource views updated successfully!";
            echoResponse(200, $response);
        } else {
            // failed
            $response['status'] = "error";
            $response["message"] = "Resource views update failed!";
            echoResponse(201, $response);
        }
    } else {
        $response['status'] = "error";
        $response["message"] = "Resource not found!";
        echoResponse(201, $response);
    }
});
// customer resources
$app->get('/customer/resources/:id', function($id) use ($app) {
    // authAdmin();
    // initialize response array
    $response = [];
    // database handler
    $db = new DbHandler();
    $cust_id = $db->purify($id);
    // compose sql query
    $query = "SELECT * FROM customer_resource LEFT JOIN resource ON cr_resource_id = rsc_id
        WHERE cr_customer_id = '$cust_id' ";
    // run query
    $resources = $db->getRecordset($query);
    // return list of resources
    if($resources) {
    	$response['resources'] = $resources;
    	$response['status'] = "success";
        $response["message"] =  count($resources) . " resource(s) found!";
        echoResponse(200, $response);
    } else {
    	$response['status'] = "error";
        $response["message"] = "No resource found!";
        echoResponse(201, $response);
    }
});