<?php
// list users
$app->get('/users', function() use ($app) {
    // authAdmin();
    // initialize response array
    $response = [];
    // database handler
    $db = new DbHandler();
    // get logged in user
    $jh = new JWTHandler();
    $actor = $jh->extractUserFromAuth();
    // compose sql query
    $query = "SELECT * FROM user WHERE user_id <> '{$actor->user_id}' ";
    $query .= " ORDER BY user_name ";
    // run query
    $users = $db->getRecordset($query);
    // return list of users
    if($users) {
    	$response['users'] = $users;
    	$response['status'] = "success";
        $response["message"] =  count($users) . " User(s) found!";
        echoResponse(200, $response);
    } else {
    	$response['status'] = "error";
        $response["message"] = "No user found!";
        echoResponse(201, $response);
    }
});
// single user
$app->get('/users/:id', function($id) use ($app) {
    // authAdmin();
    // initialize response array
    $response = [];
    // database handler
    $db = new DbHandler();
    // compose sql query
    $query = "SELECT * FROM user WHERE user_id = '$id' ";
    // run query
    $user = $db->getOneRecord($query);
    // return user
    if($user) {
    	$response['user'] = $user;
    	$response['status'] = "success";
        $response["message"] =  " User found!";
        echoResponse(200, $response);
    } else {
    	$response['status'] = "error";
        $response["message"] = "User not found!";
        echoResponse(201, $response);
    }
});
// create user
$app->post('/users', function() use ($app) {
    // only super admins allowed
    authAdmin();
    // initialize response array
    $response = [];
    // extract post body
    $r = json_decode($app->request->getBody());
    // check required fields
    verifyRequiredParams(['user_email', 'user_password', 'user_name', 'user_role'],$r->user);
    // instantiate classes
    $db = new DbHandler();
    $jh = new JWTHandler();
    
    // user email
    $user_email = $db->purify($r->user->user_email);
    // check if user_email is already used
    $user_check  = $db->getOneRecord("SELECT user_id FROM user WHERE user_email = '$user_email' ");
    if($user_check) {
        // user already exists
        $response['status'] = "error";
        $response["message"] = "User with same email already Exists!";
        echoResponse(201, $response);
    } else {
        // get fields for insert
        $user_password = $db->purify($r->user->user_password);
        $user_name = $db->purify($r->user->user_name);
        $user_role = $db->purify($r->user->user_role);
        $user_date = date("Y-m-d");
        // currently logged in user
        $actor = $jh->extractUserFromAuth();
        $user_creator_name = $actor->user_name;
        //create new user
        $user_id = $db->insertToTable(
            [ $user_email, $user_password, $user_name, $user_role , $user_date, $user_creator_name ], /*values - array*/
            [ 'user_email', 'user_password', 'user_name', 'user_role' , 'user_date', 'user_creator_name' ], /*column names - array*/
            "user" /*table name - string*/
        );
        // user created successfully?
        if($user_id) {
            // log admin action
            $lg = new Logger();
            $lg->logAction(" Created an Admin");
            $response['user_id'] = $user_id;
            $response['status'] = "success";
            $response["message"] = "User created successfully!";
            echoResponse(200, $response);
        } else {
            $response['status'] = "error";
            $response["message"] = "Something went wrong while trying to create the user!";
            echoResponse(201, $response);
        }
    }
});
// edit user
$app->put('/users', function() use ($app) {
    // only super admins allowed
    authAdmin();
    // initialize response array
    $response = [];
    // extract post body
    $r = json_decode($app->request->getBody());
    // check required fields
    verifyRequiredParams(['user_id', 'user_email', 'user_password', 'user_name', 'user_role'],$r->user);
    // instantiate classes
    $db = new DbHandler();
    
    // user id and email
    $user_id = $db->purify($r->user->user_id);
    $user_email = $db->purify($r->user->user_email);
    // check if user_email is already used by another user
    $user_check  = $db->getOneRecord("SELECT user_id FROM user WHERE user_email = '$user_email' AND user_id <> '$user_id' ");
    if($user_check) {
        // another user already exists
        $response['status'] = "error";
        $response["message"] = "Another user with same email already Exists!";
        echoResponse(201, $response);
    } else {
        // get fields for insert
        $user_password = $db->purify($r->user->user_password);
        $user_name = $db->purify($r->user->user_name);
        $user_role = $db->purify($r->user->user_role);
        //update user
        $update_user = $db->updateInTable(
        	"user", /*table*/
        	[ 'user_email'=>$user_email, 'user_password' => $user_password, 'user_name' => $user_name, 'user_role' => $user_role ], /*columns*/
        	[ 'user_id'=>$user_id ] /*where clause*/
        );
        // user created successfully?
        if($update_user >= 0) {
            // log admin action
            $lg = new Logger();
            $lg->logAction(" Updated an Admin");
            $response['status'] = "success";
            $response["message"] = "User udpated successfully!";
            echoResponse(200, $response);
        } else {
            $response['status'] = "error";
            $response["message"] = "Something went wrong while trying to update the user!";
            echoResponse(201, $response);
        }
    }
});
// delete user
$app->delete('/users/:id', function($id) use ($app) {
    // only super admins allowed
    authAdmin();
    // initialize response array
    $response = [];
    // database handler
    $db = new DbHandler();
    // delete user
    $user_delete = $db->deleteFromTable("user", "user_id", $id);
    // deleted?
    if($user_delete) {
    	// log admin action
            $lg = new Logger();
            $lg->logAction(" Deleted an Admin");
    	$response['status'] = "success";
        $response["message"] =  "User deleted successfully";
        echoResponse(200, $response);
    } else {
    	$response['status'] = "error";
        $response["message"] = "User DELETE failed!";
        echoResponse(201, $response);
    }
});
// list users log
$app->get('/userlogs', function() use ($app) {
    // authAdmin();
    // initialize response array
    $response = [];
    // database handler
    $db = new DbHandler();
    // compose sql query
    $query = "SELECT * FROM user_log";
    $query .= " ORDER BY ulog_time ";
    // run query
    $logs = $db->getRecordset($query);
    // return list of user logs
    if($logs) {
    	$response['logs'] = $logs;
    	$response['status'] = "success";
        $response["message"] =  count($logs) . " User Log(s) found!";
        echoResponse(200, $response);
    } else {
    	$response['status'] = "error";
        $response["message"] = "No User Log found!";
        echoResponse(201, $response);
    }
});