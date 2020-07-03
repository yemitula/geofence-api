<?php require_once('lib/swift_required.php'); ?>
<?php 
//This function send an email using swiftMailer
//$emailFrom is source email
//$nameFrom is source nam
//$to is an ARRAY containing destination emails
//$cc is an ARRAY containing carbon copy emails
//$bcc is an ARRAY containing blind copy emails
//$subject is the subject of the email
//$body is the content of the email - HTML
function MySwiftMailer($emailFrom, $nameFrom, $to, $subject, $body, $cc = NULL, $bcc = NULL) {

  //Create the Transport
  $transport = Swift_MailTransport::newInstance();
  
  //Create the Mailer using your created Transport
  $mailer = Swift_Mailer::newInstance($transport);
      
  //Create a message
  $message = Swift_Message::newInstance($subject)
  ->setFrom(array($emailFrom => $nameFrom))
  ->setTo($to)
  ->setCc($cc)
  ->setBcc($bcc)
  ->setBody($body, 'text/html')
  ;
  //send the message
  $result = $mailer->send($message);  
}