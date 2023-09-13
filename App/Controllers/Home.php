<?php

namespace App\Controllers;

use App\Auth;
use \Core\View;

class Home extends Authenticated
{

    public function indexAction()
    {

        View::renderTemplate('Home/index.html', [
            'user' => Auth::getUser()
        ]);
    }
}
