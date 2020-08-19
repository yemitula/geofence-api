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
$app->get('/staff/:id', function($id) use ($app) {
    // authAdmin();
    // initialize response array
    $response = [];
    // database handler
    $db = new DbHandler();
    // compose sql query
    $query = "SELECT * FROM staff WHERE stf_id = '$id' ";
    // run query
    $staff = $db->getOneRecord($query);
    // return staff
    if($staff) {
    	$response['staff'] = $staff;
    	$response['status'] = "success";
        $response["message"] =  " Staff found!";
        echoResponse(200, $response);
    } else {
    	$response['status'] = "error";
        $response["message"] = "Staff not found!";
        echoResponse(201, $response);
    }
});
// edit staff
$app->put('/staff', function() use ($app) {
    // authAdmin();
    // initialize response array
    $response = [];
    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['id','no','name','email','password','loc_id'],$r->staff);
    // database handler
    $db = new DbHandler();// compose sql query
    $stf_id = $db->purify($r->staff->id);
    $stf_no = $db->purify($r->staff->no);
    $stf_name = $db->purify($r->staff->name);
    $stf_email = $db->purify($r->staff->email);
    $stf_password = $db->purify($r->staff->password);
    $stf_location_id = $db->purify($r->staff->loc_id);
    // check if stf_no or stf_email is already added
    $stf_check  = $db->getOneRecord("SELECT stf_id FROM staff WHERE (stf_no = '$stf_no' OR stf_email='$stf_email' ) AND stf_id<>$stf_id");
    if($stf_check) {
        // staff already exists
        $response['status'] = "error";
        $response["message"] = "Staff with Number or Email already Exists!";
        echoResponse(201, $response);
    }else {
         //update staff
         $update_staff = $db->updateInTable(
        	"staff", /*table*/
        	[ 'stf_no'=>$stf_no, 'stf_name' => $stf_name, 'stf_email' => $stf_email, 'stf_password' => $stf_password, 'stf_location_id' => $stf_location_id ], /*columns*/
        	[ 'stf_id'=>$stf_id ] /*where clause*/
        );
        // return resource
        if($update_staff>0) {
            // log admin action
            // $lg = new Logger();
            // $lg->logAction(" Updated a Staff");
            $response['status'] = "success";
            $response["message"] = "Staff udpated successfully!";
            echoResponse(200, $response);
        } else {
            $response['status'] = "error";
            $response["message"] = "Something went wrong while trying to update the staff!";
            echoResponse(201, $response);
        }
    }
});
// update staff
$app->put('/staff/:staffId', function($staffId) use ($app) {
    // authAdmin();
    // initialize response array
    $response = [];
    $r = json_decode($app->request->getBody());
    verifyRequiredParams(['stf_name','stf_email','stf_no'],$r->staff);
    // database handler
    $db = new DbHandler();// compose sql query
    $stf_name = $db->purify($r->staff->stf_name);
    $stf_email = $db->purify($r->staff->stf_email);
    $stf_no = $db->purify($r->staff->stf_no);
    $stf_safety_code = isset($r->staff->stf_safety_code) ? $db->purify($r->staff->stf_safety_code) : null;
    $stf_location_id = isset($r->staff->stf_location_id) ? $db->purify($r->staff->stf_location_id) : null;
    $stf_device_id = isset($r->staff->stf_device_id) ? $db->purify($r->staff->stf_device_id) : null;
    // check if stf_no or stf_email is already added
    $stf_check  = $db->getOneRecord("SELECT stf_id FROM staff WHERE (stf_no = '$stf_no' OR stf_email='$stf_email' ) AND stf_id<>$staffId");
    if($stf_check) {
        // staff already exists
        $response['status'] = "error";
        $response["message"] = "Another Staff with Number or Email already Exists!";
        echoResponse(201, $response);
    }else {
         //update staff
         $fieldsToUdpate = [
             'stf_name' => $stf_name,
             'stf_email' => $stf_email,
             'stf_no' => $stf_no,
             'stf_time_updated' => date("Y-m-d H:i:s")
         ];
         if($stf_safety_code) {
             $fieldsToUdpate['stf_safety_code'] = $stf_safety_code;
         }
         if($stf_location_id) {
             $fieldsToUdpate['stf_location_id'] = $stf_location_id;
         }
         if($stf_device_id) {
             $fieldsToUdpate['stf_device_id'] = $stf_device_id;
         }
         $update_staff = $db->updateInTable(
        	"staff", /*table*/
        	$fieldsToUdpate,
        	[ 'stf_id'=>$staffId ] /*where clause*/
        );
        // return resource
        if($update_staff > 0) {
            // log admin action
            // $lg = new Logger();
            // $lg->logAction(" Updated a Staff");
            $response['status'] = "success";
            $response["message"] = "Staff updated successfully!";
            echoResponse(200, $response);
        } else {
            $response['status'] = "error";
            $response["message"] = "Something went wrong while trying to update the staff!";
            echoResponse(201, $response);
        }
    }
});
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

// delete staff
$app->delete('/staff/:id', function($id) use ($app) {
    // only super admins allowed
    // authAdmin();
    // initialize response array
    $response = [];
    // database handler
    $db = new DbHandler();
    $staff_delete = $db->deleteFromTable("staff", "stf_id", $id);
    // deleted?
    if($staff_delete) {
        // log admin action
        // $lg = new Logger();
        // $lg->logAction(" Deleted a Staff");
    	$response['status'] = "success";
        $response["message"] =  "Staff deleted successfully";
        echoResponse(200, $response);
    } else {
    	$response['status'] = "error";
        $response["message"] = "Staff DELETE failed!";
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