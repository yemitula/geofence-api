<?php

// check for expiring/expired requests, update the status and send notifications
$app->get('/checkExpiringRequests', function() use ($app) {
	$response = array();
    $db = new DbHandler();
    $lg = new Logger();

    $lg->logToFile('expiring-requests', '--- BEGIN - Checking for expiring requests...');

    // get all requests that are expiring or expired

    $query = "SELECT *, DATEDIFF(CURDATE(), req_time_started) AS req_days  FROM request
	    LEFT JOIN service ON req_service_id=svc_id
	    LEFT JOIN service_category ON svc_scat_id=scat_id
	    LEFT JOIN property ON req_property_id = ppty_id
	    LEFT JOIN customer ON ppty_customer_id = cust_id
	    LEFT JOIN provider ON req_assignee_id = pro_id
	    LEFT JOIN service_manager ON req_smanager_id = sm_id
	    WHERE req_status = 'ONGOING'
	    AND DATEDIFF(CURDATE(), req_time_started) >= 23 ";

    $requests = $db->getRecordset($query);

    if($requests) {
    	$lg->logToFile('expiring-requests', "Found ".count($requests)." expiring/expired request(s)");
    	$os = new OneSignal();
    	$sm = new mySwiftMailer();
    	$nh = new NotificationHandler();
        foreach ($requests as $request) {
        	// set req_state to Overdue
        	if($request['req_state'] != 'Overdue' && $req_state['req_days'] > 30) {
        		$udpate_request = $db->updateInTable(
		            "request", /*table*/
		            [ 'req_state'=>'Overdue' ], /*columns*/
		            [ 'req_id'=>$request['req_id'] ] /*where clause*/
		        );
        	}
	        // notifications
	        $SHORTNAME = SHORTNAME;
	        // provider
	        $subject = "Request #{$request['req_ref']} has been ONGOING for {$request['req_days']}! Please take action.";
	        $body = "<p>Hello {$request['pro_company']},</p>
	        <p>
	        <p>Request #{$request['req_ref']}(<strong>{$request['req_title']}</strong>) has been ONGOING for {$request['req_days']}. Please take action.</p>
	        </p>
	        <p>Please complete work on this request immediately to avoid penalties. Note that the Call2Fix Service Manager may take action on this request anytime from now.</p>
	        <p>Thank you for using $SHORTNAME.</p>
	        <p>NOTE: please DO NOT REPLY to this email.</p>
	        <p><br><strong>$SHORTNAME</strong></p>";
	        $sm->sendmail(FROM_EMAIL, SHORTNAME, [$request['pro_email']], $subject, $body);
	        // push to provider
	        $push_to_provider = $os->notifyUser("provider", $request['pro_id'], $subject);
	        // notification
	        $nh = new NotificationHandler();
	        $noti_id = $nh->log('provider', $request['pro_id'], $subject);

	        // manager
	        $subject = "Request #{$request['req_ref']} has been ONGOING for {$request['req_days']}! Please take action.";
	        $body = "<p>Hello {$request['sm_name']},</p>
	        <p>
	        <p>Request #{$request['req_ref']}(<strong>{$request['req_title']}</strong>) has been ONGOING for {$request['req_days']}. Please take action.</p>
	        </p>
	        <p>As the Service Manager in charge of this request, you can take action (e.g. reassign) immediately on this request. Please discuss with the provider ({$request['pro_company']}) to complete work on this request immediately or take action.</p>
	        <p>Thank you for using $SHORTNAME.</p>
	        <p>NOTE: please DO NOT REPLY to this email.</p>
	        <p><br><strong>$SHORTNAME</strong></p>";
	        $sm->sendmail(FROM_EMAIL, SHORTNAME, [$request['sm_email']], $subject, $body);
	        // push to provider
	        $push_to_manager = $os->notifyUser("manager", $request['sm_id'], $subject);
	        // notification
	        $nh = new NotificationHandler();
	        $noti_id = $nh->log('manager', $request['sm_id'], $subject);

	        // helpdesk
	        $subject = "ALERT! Request #{$request['req_ref']} has been ONGOING for {$request['req_days']}!";
	        $body = "<p>Hello Helpdesk,</p>
	        <p>
	        <p>Request #{$request['req_ref']}(<strong>{$request['req_title']}</strong>) has been ONGOING for {$request['req_days']}. The Service Manager is expected to take action to reassign or ensure completion of this request immediately.</p>
	        </p>
	        <p>Thank you for using $SHORTNAME.</p>
	        <p>NOTE: please DO NOT REPLY to this email.</p>
	        <p><br><strong>$SHORTNAME</strong></p>";
	        $sm->sendmail(FROM_EMAIL, SHORTNAME, HELPDESK_EMAIL, $subject, $body);
        }
        $response["message"] = "Found ".count($requests)." expiring/expired request(s)";
    } else {
    	$lg->logToFile('expiring-requests', "No expiring/expired requests found.");
    	$response["message"] = "No expiring/expired requests found.";
    }

    $lg->logToFile('expiring-requests', '--- END');
    
    echoResponse(200, $response);
});