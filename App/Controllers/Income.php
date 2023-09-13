<?php

namespace App\Controllers;

use Core\View;
use App\Models\UserIncome;
use App\Flash;

class Income extends Authenticated
{
    public function newAction()
    {
        $date = new UserIncome();
        $date->dateSetting();

        View::renderTemplate('Income/new.html', [
            'category' => UserIncome::incomeCategory(),
            'income' => $date
        ]);
    }

    public function addAction()
    {

        $income = new UserIncome($_POST);

        if ($income->saveIncome()) {
            Flash::addMessage('Income added');
            View::renderTemplate('Home/index.html');
        } else {

            View::renderTemplate('Income/new.html', [
                'category' => UserIncome::incomeCategory(),
                'income' => $income
            ]);
        }
    }
}
