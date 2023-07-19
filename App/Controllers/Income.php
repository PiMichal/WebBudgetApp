<?php

namespace App\Controllers;

use Core\View;
use App\Models\IncomeAndExpenses;
use App\Flash;

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

        /**
     * Adding income
     * 
     * @return void
     */
    public function addAction()
    {   
        $income = new IncomeAndExpenses($_POST);

        if ($income->saveIncome()) {
            Flash::addMessage('Income added');
            View::renderTemplate('Home/index.html');

        } else {

            View::renderTemplate('Income/new.html', [
                'income' => $income
            ]);
            
        }

    }
}