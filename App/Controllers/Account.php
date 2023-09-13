<?php

namespace App\Controllers;

use \App\Models\User;

class Account extends \Core\Controller
{
    public function validateEmailAction()
    {
        $is_valid = !User::emailExists($_GET['email']);

        header('Content-Type: applicaion/json');
        echo json_encode($is_valid);
    }
}
