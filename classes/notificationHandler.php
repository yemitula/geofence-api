<?php

class NotificationHandler {

    private $db;

    /*constructor*/
	function __construct() {
        require_once 'dbHandler.php';
        $this->db = new DbHandler();
    }

	/**
	* function logs new notification
	* @return true or error
	*/
    public function log($usertype, $id, $message) {
        $now = date("Y-m-d H:i:s");
        switch ($usertype) {
            case 'admin':
            {
                $en_id = $this->db->insertToTable(
                    [ $id, $message, $now ],
                    [ 'en_fence_exit_id', 'en_message', 'en_time' ],
                    'exit_notification'
                );
                return $en_id;
            }
            break;
        }
    }

    public function read($usertype, $id) {
        switch ($usertype) {
            case 'admin':
            {
                $read_count = $this->db->updateInTable(
                    "exit_notification",
                    [ 'en_read'=>"1" ],
                    [ 'en_fence_exit_id'=>$id ]
                );
            }
            break;
        }
        return $read_count;
    }
}