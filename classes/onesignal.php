<?php

class OneSignal {
	// constructor
	function __construct() {
		// construct
    }

    public function notifyUser ($apptype, $id, $message) {
    	$content = array(
			"en" => $message
			);

    	switch ($apptype) {
    		case 'customer':
    		case 'provider':
    		case 'manager':
    			$title = "Call2Fix Customer";
    			$app_id = "7f0babd8-df5f-4f3f-87df-857bb8e9300b";
    			$api_key = "ZjkwNGMzMDMtNzBkYy00OWM4LTkzMjYtMzllYmE3YmI1YThh";
    			break;

    		// case 'provider':
    		// 	$title = "Call2Fix Agent";
    		// 	$app_id = "e26d8ad0-3825-45c5-b68a-71931e34a419";
    		// 	$api_key = "MjIxYmI3MTUtOTVmOC00NzE1LTk4OGItYzE3MmE5YmM4ZjQz";
    		// 	break;
    	}

		$fields = array(
			'app_id' => $app_id,
			'filters' => array( array("field" => "tag", "key" => "id", "relation" => "=", "value" => $id),
								array("field" => "tag", "key" => "apptype", "relation" => "=", "value" => $apptype) ),
			// 'data' => array("foo" => "bar"),
			'contents' => $content
		);

		$fields = json_encode($fields);
    	// print("\nJSON sent:\n");
    	// print($fields);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
												   'Authorization: Basic '.$api_key));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);

		return $response;
    }
}