<?php

namespace App\Controllers;

use Core\View;
use App\Models\UserIncome;
use App\Flash;

class Income extends Authenticated
{
    public function newAction()
    {   
        View::renderTemplate('Income/new.html', [
            'category' => UserIncome::incomeCategory(),
            'date_of_income' => UserIncome::currentDate()
        ]);
    }

    public function addAction()
    {
        if (UserIncome::saveIncome()) {
            Flash::addMessage('Income added');
            View::renderTemplate('Home/index.html');
        } else {
            View::renderTemplate('Income/new.html', [
                'errors' => UserIncome::validate(),
                'category' => UserIncome::incomeCategory(),
            ]);
        }
    }
}
