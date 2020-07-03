<?php

class pushHandler {

    function __construct() {     
        // nothing?
    }

    //$tokens is an array of tokens to notify, when empty, we notify all users
    //$message is the message we are putting in the notification
    function createPushNotification($tokens = [], $message) {
        $data = [
            "tokens" => $tokens,
            "profile" => "learnova_dev",
            "notification" => [
                "message" => $message
            ]
        ];

        $data_string = json_encode($data);

        // die($data_string);

        $ch = curl_init('https://api.ionic.io/push/notifications');               
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                         
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                   
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);                     
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                               
            'Content-Type: application/json',
            'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiIzOWNkZDZjNS1lN2I5LTRkNTUtYjg0Ny02NDI5YjEwYjBhMjIifQ.LUBR0JgLO0HzPAm0A21_KJ7Lzoljm6gRBUXfAZQI6eg',                                              
            'Content-Length: ' . strlen($data_string))
        );                                
        
        $result = curl_exec($ch);

        if($result) {
            return $result;
        } else {
            echo(curl_error($ch));
        }
    }

}