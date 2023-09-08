<?php

namespace App\Controllers;

use Core\View;
use App\Models\UserIncome;
use App\Flash;
use App\Models\UserBalance;

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
        View::renderTemplate('Income/new.html', [
            'category' => UserIncome::incomeCategory()
        ]);
    }

    /**
     * Adding income
     * 
     * @return void
     */
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