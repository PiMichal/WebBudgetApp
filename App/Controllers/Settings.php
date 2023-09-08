<?php

namespace App\Controllers;

use App\Auth;
use \Core\View;
use App\Models\User;
use App\Flash;
use App\Models\UserIncome;

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

    public function incomeAddAction()
    {
        View::renderTemplate('Settings/incomeAdd.html', [
        ]);

    }

    public function incomeSaveAction()
    {
        $income = new UserIncome();

        if ($income->categoryValidate($_POST["new_category"])) {
            $income->incomeAddCategory($_POST["new_category"]);
            Flash::addMessage('Category added');
            View::renderTemplate('Settings/settings.html', [
            ]);

        } else {
            Flash::addMessage('Category not added', Flash::WARNING);
            View::renderTemplate('Settings/incomeAdd.html', [
                'user' => $income,
                'category' => $_POST["new_category"]
            ]);
        }
        

    }

}
