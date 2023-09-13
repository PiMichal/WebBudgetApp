<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\UserExpense;
use App\Models\UserIncome;
use \Core\View;

class Signup extends \Core\Controller
{
    public function newAction()
    {
        View::renderTemplate('Signup/new.html');
    }

    public function createAction()
    {

        $user = new User($_POST);

        if ($user->save()) {

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

    public function successAction()
    {
        View::renderTemplate('Signup/success.html');
    }

    public function activateAction()
    {
        User::activate($this->route_params['token']);

        $this->redirect('/signup/activated');
    }

    public function activatedAction()
    {
        View::renderTemplate('Signup/activated.html');
    }
}
