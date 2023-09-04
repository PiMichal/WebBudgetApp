<?php

namespace App\Controllers;

use App\Auth;
use \Core\View;

/**
 * Settings controller
 *
 * PHP version 7.0
 */
class Settings extends Authenticated
{

    /**
     * Show the index page
     *
     * @return void
     */
    public function settingsAction()
    {

        View::renderTemplate('Settings/settings.html', [
            
        ]);

    }


}
