<?php

namespace App\Controllers;

use App\Auth;
use \Core\View;
use App\Models\User;
use App\Flash;

/**
 * Home controller
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

    public function accountAction()
    {
        View::renderTemplate('Settings/editAccount.html', [
            'user' => Auth::getUser()
        ]);

    }

    public function updateAction()
    {   
        $user = new User($_POST);

        if($_POST["password"] == "" && $user->update()) {

            Flash::addMessage('The data has been successfully updated');
            View::renderTemplate('Settings/settings.html', [
                'user' => $user
            ]);

        } else if ($_POST["password"] != "" && $user->updatePassword()){
            Flash::addMessage('The data has been successfully updated');
            View::renderTemplate('Settings/settings.html', [
                'user' => $user
            ]);

        } else {
            Flash::addMessage('Data update failed', Flash::WARNING);
            View::renderTemplate('Settings/editAccount.html', [
                'user' => $user
            ]);
        }
    }

}
