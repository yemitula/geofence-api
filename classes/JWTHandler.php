<?php

/* Class handles JWT creation and validation */

class JWTHandler {
    public function createUserToken($user) {
        $tomorrow = strtotime('+24 hour');

        // create the payload
        $payload = array(
            "iss" => SITE_URL,
            "aud" => SITE_URL,
            "iat" => time(),
            "nbf" => time(),
            "exp" => $tomorrow,
            "user" => $user
        );

        // generate jwt
        $token = \Firebase\JWT\JWT::encode($payload, JWT_KEY);
        
        \Firebase\JWT\JWT::$leeway = 60; // $leeway in seconds

        if($token) {
            return $token;
        } else {
            return false;
        }
    }

    public function getUserFromToken($token) {
        $payload = \Firebase\JWT\JWT::decode($token, JWT_KEY, ['HS256']);
        if($payload) {
            $user = $payload->user;
            return $user;
        } else {
            return false;
        }
    }

    public function extractUserFromAuth() {
        // check if Auth is set in the request
        $headers = apache_request_headers();
        // var_dump($headers); die;
        if(isset($headers['Authorization'])) {
            // Auth present
            $auth_header = $headers['Authorization'];
            // extract JWT token
            list($jwt) = sscanf( $auth_header, 'Bearer %s');
            if($jwt) {
                // return token
                return $this->getUserFromToken($jwt);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}