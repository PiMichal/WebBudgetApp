<?php

namespace App\Controllers;

use Core\View;

/**
 * Income controller
 * 
 * PHP version 7.0
 */

class Income extends Authenticated
{
    /**
     * Show the income page
     * 
     * @return void
     */
    public function newAction()
    {
        View::renderTemplate('Income/new.html');
    }
}