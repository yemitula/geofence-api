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

// check for overdue requests, update the status and send notifications
$app->get('/updateOverdueRequests', function() use ($app) {
	$response = array();
    $db = new DbHandler();
    $lg = new Logger();

    $lg->logToFile('overdue-requests', '--- BEGIN - Checking for overdue requests...');

    // get all requests that are overdue

    $query = "SELECT * FROM request
	    LEFT JOIN service ON req_service_id=svc_id
	    LEFT JOIN service_category ON svc_scat_id=scat_id
	    LEFT JOIN property ON req_property_id = ppty_id
	    LEFT JOIN customer ON ppty_customer_id = cust_id
	    LEFT JOIN provider ON req_assignee_id = pro_id
	    LEFT JOIN service_manager ON req_smanager_id = sm_id
	    WHERE req_status = 'ONGOING' AND req_sla_expected < CURDATE() ";

    $requests = $db->getRecordset($query);

    if($requests) {
    	$lg->logToFile('overdue-requests', "Found ".count($requests)." overdue request(s)");
    	$os = new OneSignal();
    	$sm = new mySwiftMailer();
    	$nh = new NotificationHandler();
        foreach ($requests as $request) {
        	// set req_state to Overdue
        	$udpate_request = $db->updateInTable(
	            "request", /*table*/
	            [ 'req_state'=>'Overdue' ], /*columns*/
	            [ 'req_id'=>$request['req_id'] ] /*where clause*/
	        );
	        // notifications
	        $SHORTNAME = SHORTNAME;
	        // provider
	        $subject = "Request #{$request['req_ref']} is OVERDUE!";
	        $body = "<p>Hello {$request['pro_company']},</p>
	        <p>
	        <p>Request #{$request['req_ref']}(<strong>{$request['req_title']}</strong>) is OVERDUE (the expected SLA - {$request['req_sla_expected']} - has elapsed).</p>
	        </p>
	        <p>Please complete work on this request immediately to avoid penalties.</p>
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
	        $subject = "Request #{$request['req_ref']} is OVERDUE!";
	        $body = "<p>Hello {$request['sm_name']},</p>
	        <p>
	        <p>Request #{$request['req_ref']}(<strong>{$request['req_title']}</strong>) is OVERDUE (the expected SLA - {$request['req_sla_expected']} - has elapsed).</p>
	        </p>
	        <p>Please discuss with the provider ({$request['pro_company']}) to complete work on this request immediately.</p>
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
	        $subject = "Request #{$request['req_ref']} is OVERDUE!";
	        $body = "<p>Hello Helpdesk,</p>
	        <p>
	        <p>Request #{$request['req_ref']}(<strong>{$request['req_title']}</strong>) is OVERDUE (the expected SLA - {$request['req_sla_expected']} - has elapsed).</p>
	        </p>
	        <p>Thank you for using $SHORTNAME.</p>
	        <p>NOTE: please DO NOT REPLY to this email.</p>
	        <p><br><strong>$SHORTNAME</strong></p>";
	        $sm->sendmail(FROM_EMAIL, SHORTNAME, HELPDESK_EMAIL, $subject, $body);
        }
        $response["message"] = "Found ".count($requests)." overdue request(s)";
    } else {
    	$lg->logToFile('overdue-requests', "No overdue requests found.");
    	$response["message"] = "No overdue requests found.";
    }

    $lg->logToFile('overdue-requests', '--- END');
    
    echoResponse(200, $response);
});

// process pending provider payouts
$app->get('/processProviderPayouts', function() use ($app) {
	$response = array();
    $db = new DbHandler();
    $lg = new Logger();

    $lg->logToFile('provider-payouts', '--- BEGIN - Checking for pending provider payouts...');

    // get all provider payouts that are pending
    $query = "SELECT * FROM provider_payout
	    LEFT JOIN request ON ppy_request_id = req_id
	    LEFT JOIN provider ON ppy_provider_id = pro_id
	    LEFT JOIN bank_code ON pro_account_bank = bank_name
	    WHERE ppy_status = 'PENDING' ";

    $payouts = $db->getRecordset($query);

    if($payouts) {
    	$lg->logToFile('provider-payouts', "Found ".count($payouts)." provider pending payout(s)");
    	$response["message"] = "Found ".count($payouts)." provider pending payout(s)";
    	$os = new OneSignal();
    	$sm = new mySwiftMailer();
    	$nh = new NotificationHandler();
        foreach ($payouts as $i => $payout) {
        	if($payout['pro_id'] && $payout['ppy_amount'] && $payout['bank_code']) {
        		$lg->logToFile('provider-payouts', "($i) - Payout #{$payout['ppy_id']}: N{$payout['ppy_amount']} to {$payout['pro_company']} for Request #{$payout['req_ref']}. ");
        		$curl = curl_init();

        		// create transfer recipient
		        curl_setopt_array($curl, array(
		          CURLOPT_URL => "https://api.paystack.co/transferrecipient",
		          CURLOPT_RETURNTRANSFER => true,
		          CURLOPT_CUSTOMREQUEST => "POST",
		          CURLOPT_POSTFIELDS => json_encode([
		            'type'=>'nuban',
		            'name'=>$payout['pro_account_name'],
		            'account_number'=>$payout['pro_account_number'],
		            'bank_code'=>$payout['bank_code']
		          ]),
		          CURLOPT_HTTPHEADER => [
		            "authorization: Bearer ".PAYSTACK_TEST_SECRET,
		            "content-type: application/json",
		            "cache-control: no-cache"
		          ],
		        ));

		        $curl_response = curl_exec($curl);
		        $err = curl_error($curl);

		        if($err) {
		            // api request failed
		            $api_error = 'Curl returned error: ' . $err;
		            $lg->logToFile('provider-payouts', "- ERROR: Recipient creation failed - Gateway Request Failure - $api_error ");
		        } else {
		            // api request successful
		            $tranx = json_decode($curl_response);

		            if(!$tranx->status) {
		                // there was an error from the API
		                $lg->logToFile('provider-payouts', "- ERROR: Recipient creation failed - API Status NOT set. ");
		            } else {
		                // recipient successfully created
		                $recipient_code = $db->purify($tranx->data->recipient_code);
		                $lg->logToFile('provider-payouts', "- SUCCESS: Recipient creation successful! - Recipient Code ($recipient_code) ");

		                // initiate transfer to recipient
		                curl_setopt_array($curl, array(
		                  CURLOPT_URL => "https://api.paystack.co/transfer",
		                  CURLOPT_RETURNTRANSFER => true,
		                  CURLOPT_CUSTOMREQUEST => "POST",
		                  CURLOPT_POSTFIELDS => json_encode([
		                    'source'=>'balance',
		                    'amount'=>($payout['ppy_amount'] * 100), //in kobo
		                    'reason'=> "Payout #{$payout['ppy_id']}: N{$payout['ppy_amount']} to {$payout['pro_company']} for Request #{$payout['req_ref']}.",
		                    'recipient'=>$recipient_code
		                  ]),
		                  CURLOPT_HTTPHEADER => [
		                    "authorization: Bearer ".PAYSTACK_TEST_SECRET,
		                    "content-type: application/json",
		                    "cache-control: no-cache"
		                  ],
		                ));

		                $curl_response = curl_exec($curl);
		                $err = curl_error($curl);

		                if($err) {
		                    // api request failed
		                    $api_error = 'CURL returned error: ' . $err;
		                    $lg->logToFile('provider-payouts', "- ERROR: Transfer initiation failed - Gateway Request Failure - $api_error ");
		                } else {
		                    // transfer request successful
		                    $tranx = json_decode($curl_response);
		                    $response['$tranx'] = $tranx;

		                    if(!$tranx->status) {
		                        // error from API
		                        $lg->logToFile('provider-payouts', "- ERROR: Transfer initiation failed - API Error - {$tranx->message} ");
		                    } else {
		                        // transfer initiated successfully
		                        $transfer_code = $db->purify($tranx->data->transfer_code);

		                        $lg->logToFile('provider-payouts', "- SUCCESS: Transfer initiation successful! - Transfer Code ($transfer_code) ");
		                        // update db and send notifications
		                        $udpate_payout = $db->updateInTable(
						            "provider_payout", /*table*/
						            [ 'ppy_status'=>'INITIATED', 'ppy_time_processed'=>date('Y-m-d H:i:s'), 'ppy_account_number'=>$payout['pro_account_number'], 'ppy_bank_code'=>$payout['bank_code'], 'ppy_recipient_code'=>$recipient_code, 'ppy_transfer_code'=>$transfer_code, 'ppy_reason'=>$db->purify($tranx->data->reason), 'ppy_integration'=>$db->purify($tranx->data->integration), 'ppy_reference'=>$db->purify($tranx->data->reference), 'ppy_message'=>$db->purify($tranx->message)  ], /*columns*/
						            [ 'ppy_id'=>$payout['ppy_id'] ] /*where clause*/
						        );
		                    }
		                }
		            }
		        }
		        $lg->logToFile('provider-payouts', "-------");
        	}
        }
    } else {
    	$lg->logToFile('provider-payouts', "No pending provider payouts found.");
    	$response["message"] = "No pending provider payouts found.";
    }

    $lg->logToFile('provider-payouts', '--- END');
    
    echoResponse(200, $response);
});

// auto - close completed requests that have tarried for 72 hours or more
$app->get('/autoCloseRequests', function() use ($app) {
	$response = array();
    $db = new DbHandler();
    $lg = new Logger();

    $lg->logToFile('close-requests', '--- BEGIN - Checking for completed requests that need to be closed...');

    // get all requests that are have been completed for 72 hours or more

    $query = "SELECT *, TIMESTAMPDIFF(HOUR, req_time_completed, NOW() ) AS completed_hours FROM request
    	LEFT JOIN property ON req_property_id = ppty_id
        LEFT JOIN customer ON ppty_customer_id = cust_id
        LEFT JOIN provider ON req_assignee_id = pro_id
	    WHERE req_status = 'COMPLETED'
	    AND TIMESTAMPDIFF(HOUR, req_time_completed, NOW() ) >= 72 ";

    $requests = $db->getRecordset($query);

    if($requests) {
    	$response["message"] = "Found ".count($requests)." completed request(s) due for closure";
    	$lg->logToFile('close-requests', $response['message']);
    	$settings = $db->getOneRecord("SELECT * FROM settings WHERE id = '0'");
    	foreach ($requests as $i => $request) {
        	// close the request
        	$total = $request['req_total'];

	        if($request['req_billing_method'] == 'QUOTE') {
	            // get quote
	            $quote = $db->getOneRecord("SELECT * FROM request_quote WHERE rqt_request_id = '$req_id' AND rqt_status = 'PAID' ");
	            $call2fix_share = $quote['rqt_scharge'];
	            $agent_share = $total - $call2fix_share - $quote['rqt_vat'];
	            // deduct credit funding if applicable
	            if($request['req_credit_amount'] && $request['req_credit_amount'] > 0 && $request['req_credit_status'] == 'CREDITED') {
	               $agent_share -= ($request['req_credit_amount'] + $request['req_credit_interest']);
	               $request['req_credit_status'] = 'PAID';
	            }

	        } else {
	            $call2fix_share = SCHARGE_PERCENT/100 * $total;
	            // $agent_share = $settings['agent_percent']/100 * $total;
	            $agent_share = $total - $call2fix_share;
	        }

	        $retention = RETENTION_PERCENT/100 * $agent_share;
	        $agent_due = $agent_share - $retention;

	        // record earning for agent
	        $earn_id = $db->insertToTable(
	            [ $request['pro_id'], $request['pro_company'], $agent_share, $agent_due, $retention, date("Y-m-d H:i:s") ],
	            [ 'earn_earner_id', 'earn_earner_name', 'earn_total', 'earn_due', 'earn_retained', 'earn_date' ],
	            'provider_earning'
	        );

	        // record payout for agent
	        // send money to provider's account or queue it up
	        $ppy_id = $db->insertToTable(
	            [ $request['pro_id'], $request['req_id'], $agent_due, date("Y-m-d H:i:s") ],
	            [ 'ppy_provider_id', 'ppy_request_id', 'ppy_amount', 'ppy_time_created' ],
	            'provider_payout'
	        );

	        $extra_message = "<p>You earned <strong>₦$agent_share</strong> for this Request. <strong>₦$agent_due</strong> will be processed to you within the hour, while <strong>₦$retention</strong> has been held in retention for <em>30 days</em>.</p>";
	        
	        // record income for call2fix
	        $inc_quote_id = isset($quote) ? $quote['rqt_id'] : null;
	        $inc_id = $db->insertToTable(
	            [ $request['req_id'], $inc_quote_id, date("Y-m-d H:i:s"), $call2fix_share, "Income from Request (#{$request['req_ref']})" ],
	            [ 'inc_request_id', 'inc_quote_id', 'inc_time', 'inc_amount', 'inc_description' ],
	            'income'
	        );

	        // update request status
	        $udpate_request = $db->updateInTable(
	            "request", /*table*/
	            [ 'req_status' => 'CLOSED', 'req_time_closed' => date("Y-m-d H:i:s"), 'req_credit_status'=>$request['req_credit_status'] ], /*columns*/
	            [ 'req_id'=>$request['req_id'] ] /*where clause*/
	        );

	        if($request['req_zendesk_id']) {
	            // attempt to close request on zendesk using curl
	            $ch = curl_init();

	            curl_setopt($ch, CURLOPT_URL, "https://alphamead.zendesk.com/api/v2/tickets/{$request['req_zendesk_id']}.json");
	            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
	                'ticket'=> [
	                    'status' => 'closed'
	                ]
	              ]) );
	            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
	            curl_setopt($ch, CURLOPT_USERPWD, ZENDESK_AGENT);

	            $headers = array();
	            $headers[] = "Content-Type: application/json";
	            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	            $result = curl_exec($ch);
	            if (curl_errno($ch)) {
	                $response['zendesk_curl_error'] = 'Error:' . curl_error($ch);
	            }
	            curl_close ($ch);
	        }

	        if($udpate_request >= 0) {
	            // request timeline
	            $rt_id = $db->insertToTable(
	                [ $request['req_id'], date("Y-m-d H:i:s"), "CRON (System) automatically CLOSED this request after {$request['completed_hours']} hours. " ],
	                [ 'rtl_request_id', 'rtl_time', 'rtl_action' ],
	                'request_timeline'
	            );

	            $lg->logToFile('close-requests', "- Closed Request #{$request['req_ref']}");

	            // notify helpdesk
	            $SHORTNAME = SHORTNAME;
	            $subject = "Request (#{$request['req_ref']}) has been automatically CLOSED on $SHORTNAME";
	            $body = "<p>Dear Helpdesk,</p>
	            <p><strong>Request (#{$request['req_ref']})</strong> (<strong>{$request['req_title']}</strong>) has been automatically CLOSED on $SHORTNAME.</p>
	            <p>Thank you for using $SHORTNAME.</p>
	            <p><br><strong>$SHORTNAME App</strong></p>";
	            $admin_email_sent = $sm->sendmail(FROM_EMAIL, SHORTNAME, [HELPDESK_EMAIL], $subject, $body);

	            // notify assignee
	            $SHORTNAME = SHORTNAME;
	            $subject = "Request (#{$request['req_ref']}) has been automatically CLOSED on $SHORTNAME";
	            $body = "<p>Dear {$request['pro_company']},</p>
	            <p>Well Done! <strong>Request (#{$request['req_ref']})</strong> (<strong>{$request['req_title']}</strong>) has been automatically <strong>CLOSED</strong> on $SHORTNAME.</p>
	            $extra_message
	            <p>Thank you for using $SHORTNAME.</p>
	            <p><br><strong>$SHORTNAME App</strong></p>";
	            $assignee_email_sent = $sm->sendmail(FROM_EMAIL, SHORTNAME, [$request['pro_email']], $subject, $body);
	            // push to assignee
	            $response['push_to_assignee'] = $os->notifyUser("provider", $request['pro_id'], $subject);
	            // record notification
	            $nh = new NotificationHandler();
	            $noti_id = $nh->log('provider', $request['pro_id'], $subject);

	            $smanager = $db->getOneRecord("SELECT * FROM service_manager WHERE sm_id = '{$request['req_smanager_id']}'");
	            if($smanager) {
	                // notification to SM
	                // email to SM
	                $subject = "Request (#{$request['req_ref']}) has been automatically CLOSED on $SHORTNAME";
	                $body = "<p>Dear {$smanager['sm_name']},</p>
	                <p><strong>Request (#{$request['req_ref']})</strong> (<strong>{$request['req_title']}</strong>) has been automatically <strong>CLOSED</strong> on $SHORTNAME.</p>
	                <p>Thank you for using $SHORTNAME.</p>
	                <p><br><strong>$SHORTNAME App</strong></p>";
	                $sm_email_sent = $sm->sendmail(FROM_EMAIL, SHORTNAME, [$smanager['sm_email']], $subject, $body);
	                // push to smanager
	                $response['push_to_sm'] = $os->notifyUser("manager", $smanager['sm_id'], $subject);
	                // record notification
	                $noti_id = $nh->log('manager', $smanager['sm_id'], $subject);
	            }
	        } else {
	            $lg->logToFile('close-requests', "- Something went wrong whilte trying to close #{$request['req_ref']}");
	        }
        }
    } else {
    	$response["message"] = "No completed requests found overdue for closure.";
    	$lg->logToFile('close-requests', $response['message']);
    }

    $lg->logToFile('close-requests', '--- END');
    
    echoResponse(200, $response);
});