<?php

class AuthHandler {

	public function __construct() {
		// construct
		//Define the urls that you want to exclude from Authentication, aka public urls
        // $this->whiteList = PUBLIC_ROUTES;
	}

	/**
	* Check in db if token is valid
	* @return bool
	*/
	public function validateToken($token) {
		$db = new DbHandler();
		$token_hashed = hash('sha512', $token . FHS, false);
		$token_found = $db->getOneRecord("SELECT 1 FROM user WHERE user_token='$token_hashed' ");
		if($token_found) {
			return true;
		} else {
			return false;
		}
	}

	public function call() {
		// get token sent in request
		$token_received = $this->app->request->headers->get('Authorization');
	}
}