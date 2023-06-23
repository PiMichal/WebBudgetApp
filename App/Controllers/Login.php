<?php

namespace App\Controllers;

use App\Models\User;
use Core\View;

/**
 * Login controller
 * 
 * PHP version 7.0
 * 
 */
class Login extends \Core\Controller
{
    /**
     * Show the login page
     * 
     * @return void
     */
    public function newAction()
    {
        View::renderTemplate('Login/new.html');
    }

    /**
     * Log in user
     * 
     * @return void
     */
    public function createAction()
    {
        $user = User::authenticate($_POST['email'], $_POST['password']);

        if ($user) {

            $this->redirect('/home/index');

        } else {
            View::renderTemplate('Login/new.html', [
                'email' => $_POST['email'],
            ]);
        }

    }


}

