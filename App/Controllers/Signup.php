<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\UserExpense;
use App\Models\UserIncome;
use \Core\View;
/**
 * Signup controller
 *
 * PHP version 7.0
 */
class Signup extends \Core\Controller
{

    /**
     * Show the signup page
     *
     * @return void
     */
    public function newAction()
    {
        View::renderTemplate('Signup/new.html');
    }

    /**
     * Sign up a new user
     * 
     * @return void
     */
    public function createAction()
    {
        
        $user = new User($_POST);

        if ($user->save()){
            
            UserIncome::createDefault($_POST['email']);
            UserExpense::createDefault($_POST['email']);
            $user->sendActivationEmail();

            $this->redirect('/signup/success');

        } else {
            View::renderTemplate('Signup/new.html', [
                'user' => $user
            ]);
        }

        
    }

    /**
     * Show the signup success page
     * 
     * @return void
     */
    public function successAction()
    {
        View::renderTemplate('Signup/success.html');
    }

    /**
     * Activate a new account
     * 
     * @return void
     */
    public function activateAction()
    {
        User::activate($this->route_params['token']);

        $this->redirect('/signup/activated');
    }

    /**
     * Show the activation success page
     * 
     * @return void
     */
    public function activatedAction()
    {
        View::renderTemplate('Signup/activated.html');
    }
}
