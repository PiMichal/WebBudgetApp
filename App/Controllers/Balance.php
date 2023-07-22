<?php

namespace App\Controllers;

use Core\View;

/**
 * Income controller
 * 
 * PHP version 7.0
 */

class Balance extends Authenticated
{
    /**
     * Show the balance page
     * 
     * @return void
     */
    public function newAction()
    {
        View::renderTemplate('Balance/show.html');
    }


}