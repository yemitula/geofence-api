<?php

class Messenger {

    private $db;

    /*constructor*/
    function __construct() {
        require_once 'dbHandler.php';
        require_once 'mySwiftMailer.php';
        require_once 'onesignal.php';
        require_once 'notificationHandler.php';
        require_once 'ebulksms.php';

        $this->db = new DbHandler();
        $this->sm = new mySwiftMailer();
        $this->os = new OneSignal();
        $this->nh = new NotificationHandler();
        $this->ebulk = new ebulksms();
    }

    /**
     * This function sends messages/notifications to a receiver
     * @param $short_message - a short version of the message (for PUSH, Notification and SMS)
     * @param $receiver - an array containing the user's email, id, and phone
     * @param $actions - an array containing the actions to be taken
     * @param $long_message - a long version of the message (for EMAIL)
     * @return $status, an array containing the status of each action
     */
    public function send($receiver, $short_message, $long_message = '', $actions = ['email', 'push', 'notification']) {
        // default actions
        // if(!$actions) {
        //     $actions = ['email', 'push', 'notification', 'sms'];
        // }
        // initialize status
        $status = [];
        // receiver is expected to have structure
        // $receiver = [
        //     'id' => int,
        //     'type' => string,
        //     'email' => string,
        //     'phone' => string
        // ];
        // send email?
        if(in_array('email', $actions) && $receiver && $receiver['email']) {
            // yes
            $status['email'] = $this->sm->sendmail(FROM_EMAIL, SHORTNAME, [$receiver['email']], $short_message, $long_message);
        } else {
            // no
            $status['email'] = false;
        }
        // send PUSH?
        if(in_array('push', $actions) && $receiver && $receiver['type'] && $receiver['id']) {
            // yes
            $status['push'] = $this->os->notifyUser($receiver['type'], $receiver['id'], $short_message);
        } else {
            // no
            $status['push'] = false;
        }
        // send in-app notification?
        if(in_array('notification', $actions) && $receiver && $receiver['type'] && $receiver['id']) {
            // yes
            $status['notification'] = $this->nh->log($receiver['type'], $receiver['id'], $short_message);
        } else {
            // no
            $status['notification'] = false;
        }
        // send SMS
        if(in_array('sms', $actions) && $receiver && $receiver['phone']) {
            // yes
            $status['sms'] = $this->ebulk->sendSMS($short_message, [$receiver['phone']]);
        } else {
            // no
            $status['sms'] = false;
        }

        return $status;
    }

}
