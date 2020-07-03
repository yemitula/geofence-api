<?php

class mySwiftMailer {

    function __construct() {     
        require_once('libs/swiftMailer/lib/swift_required.php');
    }

    //$emailFrom is source email
    //$nameFrom is source nam
    //$to is an ARRAY containing destination emails
    //$cc is an ARRAY containing carbon copy emails
    //$bcc is an ARRAY containing blind copy emails
    //$subject is the subject of the email
    //$body is the content of the email - HTML
    function sendmail($emailFrom, $nameFrom, $to, $subject, $body, $attachments = NULL, $cc = NULL, $bcc = NULL) {
        switch(MAILER_TYPE) {
            case 'MAIL':
            //Create the Transport
            $transport = Swift_MailTransport::newInstance();

            //Create the Mailer using your created Transport
            $mailer = Swift_Mailer::newInstance($transport);
            break;

            case 'SMTP':
            //Create the Transport
            $transport = (new Swift_SmtpTransport(SMTP_SERVER, SMTP_PORT, SMTP_ENCRYPTION))
            ->setUsername(SMTP_EMAIL)
            ->setPassword(SMTP_PWD);

            //Create the Mailer using your created Transport
            $mailer = new Swift_Mailer($transport);
            break;
        }

        //Create a message
        $message = Swift_Message::newInstance($subject)
        ->setFrom(array($emailFrom => $nameFrom))
        ->setTo($to)
        ->setCc($cc)
        ->setBcc($bcc)
        ->setBody($body, 'text/html')
        ;
        if($attachments && count($attachments) > 0) {
            foreach ($attachments as $attachment) {
                $message->attach(Swift_Attachment::fromPath($attachment));
            }
        }
        //send the message
        $result = $mailer->send($message);

        return $result;
    }

}

?>
