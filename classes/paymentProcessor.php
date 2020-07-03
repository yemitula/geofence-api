<?php

class PaymentProcessor {

    private $db;

    /*constructor*/
    function __construct() {
        require_once 'dbHandler.php';
        require_once 'mySwiftMailer.php';
        require_once 'messenger.php';
        require_once 'onesignal.php';
        require_once 'notificationHandler.php';

        $this->db = new DbHandler();
        $this->sm = new mySwiftMailer();
        $this->messenger = new Messenger();
        $this->os = new OneSignal();
        $this->nh = new NotificationHandler();
    }

    /**
     * This function gets the payer information for a payment.
     * @param $pay_id, id of the payment
     * @return $payer, which is an array containing the payer type, id, name, email, phone
     */
    public function getPayer($pay_id) {
        // get payment
        // $payment = $this->db->getOneRecord("SELECT * FROM payment WHERE pay_id = '$pay_id'");
        $customer = $this->db->getOneRecord("SELECT * FROM customer WHERE cust_id='{$pay_id}'");
        $payer = [
            'id' => $customer['cust_id'],
            'name' => $customer['cust_firstname'] . ' ' . $customer['cust_surname'],
            'email' => $customer['cust_email'],
            'phone' => $customer['cust_phone'],
        ];

        return $payer;
    }

    /**
     * This function processes a purchase when a payment has been successful
     * @param $pay_id, id of the payment
     * @return true, when processing was successful
     */
    public function processPurchase($pay_id) {
        // get payment details (including notice)
        $payment = $this->db->getOneRecord("SELECT * FROM payment
            LEFT JOIN notice ON pay_notice_id = not_id
            WHERE pay_id = '$pay_id'
        ");
        // mark notice as paid
        $update_request = $this->db->updateInTable(
            "notice", /*table*/
            [ 'not_status' => 'BILLED', 'not_payment_id' => $pay_id, 'not_time_updated' => date("Y-m-d H:i:s") ], /*columns*/
            ['not_id' => $payment['pay_notice_id']]  /*where clause*/
        );

        if ($update_request > 0) {
            // send notification?
            // get payer
            // $payer = $this->getPayer($pay_id);
            
            // return true
            return true;
        } else {
            return false;
        }
    }

    /**
     * This function processes a subscription when a payment has been successful
     * @param $pay_id, id of the payment
     * @return true, when processing was successful
     */
    public function processSubscription($pay_id) {
        // get payment details (including notice)
        $payment = $this->db->getOneRecord("SELECT * FROM payment
            LEFT JOIN notice ON pay_notice_id = not_id
            WHERE pay_id = '$pay_id'
        ");
        // check for active subscription
        $subscription = $this->db->getOneRecord("SELECT * FROM customer_subscription
            WHERE csub_customer_id = '{$payment['pay_customer_id']}'
            AND csub_date_end >= CURDATE()
            ORDER BY csub_date_end DESC
        ");
        if($subscription) {
            $date_start = $subscription['csub_date_end']; //set start date as end date of current subscription
        } else {
            $date_start = date("Y-m-d");
        }
        //set end date as number of months + start date
        $date_end = date('Y-m-d', strtotime("+{$payment['pay_sub_months']} months", strtotime($date_start)));
        // create the new subscription
        $csub_time = date("Y-m-d H:i:s");
        $csub_id = $this->db->insertToTable(
            [ $payment['pay_customer_id'], $payment['pay_sub_type'], $payment['pay_sub_months'], $date_start, $date_end, $payment['pay_amount'], $payment['pay_id'], $csub_time ],
            [ 'csub_customer_id', 'csub_type', 'csub_months', 'csub_date_start', 'csub_date_end', 'csub_amount_paid', 'csub_payment_id', 'csub_time' ],
            'customer_subscription'
        );
        if($csub_id) {
            // subscription created
            if($payment['pay_notice_id']) {
                // mark notice as paid
                $update_request = $this->db->updateInTable(
                    "notice", /*table*/
                    [ 'not_status' => 'BILLED', 'not_payment_id' => $pay_id, 'not_time_updated' => date("Y-m-d H:i:s") ], /*columns*/
                    ['not_id' => $payment['pay_notice_id']]  /*where clause*/
                );
                // send subscription notification to customer
                // get payer
                // $payer = $this->getPayer($pay_id);

                // TODO - send notification

                return true;
            }
        } else {
            // subscription creation failed
            return false;
        }
    }

}
