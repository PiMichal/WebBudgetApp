<?php

namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Mail
 * 
 * PHP version 7.0
 */
class Mail
{

    /**
     * Sens a message
     * 
     * @param string $to Recipient
     * @param string $subject Subject
     * @param string $text Text-only content of the message
     * @param string $html HTML content of the message
     * 
     * @return mixed
     */
    public static function send ($to, $subject, $text)
    {
        $mail = new PHPMailer(true);

        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                  //Enable verbose debug output
            $mail->isSMTP();                                          //Send using SMTP
            $mail->Host       = Config::PM_HOST;                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                 //Enable SMTP authentication
            $mail->Username   = Config::PM_USERNAME;                   //SMTP username
            $mail->Password   = Config::PM_PASSWORD;                   //SMTP password
            $mail->SMTPSecure = 'tls';                                //Enable implicit TLS encryption
            $mail->Port       = 587;                                  //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        
            //Recipients
            $mail->setFrom(Config::PM_USERNAME, "BudgetApp");
            $mail->addAddress($to);                                      //Add a recipient
        
            //Content
            $mail->isHTML(true);                                       //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $text;
        
            $mail->send();
            //echo 'Message has been sent';

        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}