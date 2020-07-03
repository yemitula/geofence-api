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
            case 'customer':
            {
                $cn_id = $this->db->insertToTable(
                    [ $id, $message, $now ],
                    [ 'cn_customer_id', 'cn_message', 'cn_time' ],
                    'customer_notification'
                );
                return $cn_id;
            }
            break;

            case 'provider':
            {
                $pn_id = $this->db->insertToTable(
                    [ $id, $message, $now ],
                    [ 'pn_provider_id', 'pn_message', 'pn_time' ],
                    'provider_notification'
                );
                return $pn_id;
            }
            break;

            case 'manager':
            {
                $mn_id = $this->db->insertToTable(
                    [ $id, $message, $now ],
                    [ 'mn_smanager_id', 'mn_message', 'mn_time' ],
                    'service_manager_notification'
                );
                return $mn_id;
            }
            break;

            case 'artisan':
            {
                $an_id = $this->db->insertToTable(
                    [ $id, $message, $now ],
                    [ 'an_artisan_id', 'an_message', 'an_time' ],
                    'artisan_notification'
                );
                return $an_id;
            }
            break;
        }
    }

    public function read($usertype, $id) {
        switch ($usertype) {
            case 'customer':
            {
                $read_count = $this->db->updateInTable(
                    "customer_notification",
                    [ 'cn_read'=>"1" ],
                    [ 'cn_customer_id'=>$id ]
                );
            }
            break;

            case 'provider':
            {
                $read_count = $this->db->updateInTable(
                    "provider_notification",
                    [ 'pn_read'=>"1" ],
                    [ 'pn_provider_id'=>$id ]
                );
            }
            break;

            case 'manager':
            {
                $read_count = $this->db->updateInTable(
                    "service_manager_notification",
                    [ 'mn_read'=>"1" ],
                    [ 'mn_smanager_id'=>$id ]
                );
            }
            break;

            case 'artisan':
            {
                $read_count = $this->db->updateInTable(
                    "artisan_notification",
                    [ 'an_read'=>"1" ],
                    [ 'an_artisan_id'=>$id ]
                );
            }
            break;
        }
        return $read_count;
    }
}