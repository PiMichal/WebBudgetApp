<?php

namespace App\Controllers;

use App\Auth;
use \Core\View;

/**
 * Home controller
 *
 * PHP version 7.0
 */
class Home extends Authenticated
{

    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
        
        View::renderTemplate('Home/index.html', [
            'user' => Auth::getUser()
        ]);

    }


}
