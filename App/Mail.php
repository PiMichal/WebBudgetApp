<?php

namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail
{

    public static function send($to, $subject, $text)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = Config::PM_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = Config::PM_USERNAME;
            $mail->Password   = Config::PM_PASSWORD;
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom(Config::PM_USERNAME, "BudgetApp");
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $text;

            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
