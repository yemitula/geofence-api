<?php

class SessionHandlr {

	// constructor
	function __construct($session_id = "") {
		// start session
		if (!isset($_SESSION)) {
            if(!empty($session_id)) session_id($session_id);
            session_start();
        }
	}

	/*function creates a new user session based on supplied user details
        used in signup, login and verify user*/
    public function createSession($session_var, $user, $logintype = "DEFAULT") {
        // $user['user_last_auth'] = $logintype; move this to calling script
        // create session variables
        $_SESSION[$session_var] = $user;
        return true;
    }

    /* function gets the details of the current session */
    public function getSession($session_var){
        $sess = array();
        if(isset($_SESSION[$session_var]))
        {
            $sess[$session_var] = $_SESSION[$session_var];
            return $sess;
        } else {
            return false;
        }
    }

    /* function to destroy the current session */
    public function destroySession($session_var){
        if(isset($_SESSION[$session_var]))
        {
            unset($_SESSION[$session_var]);
            /*$info='info';
            if(isSet($_COOKIE[$info]))
            {
                setcookie ($info, '', time() - $cookie_time);
            }*/
            $msg="Logged Out Successfully...";
        } else {
            $msg = "Not logged in...";
        }
        return $msg;
    }
}