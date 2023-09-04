<?php

namespace App\Controllers;

use Core\View;
use App\Models\UserExpense;
use App\Flash;

/**
 * Expenses controller
 * 
 * PHP version 7.0
 */

class Expense extends Authenticated
{
    /**
     * Show the expenses page
     * 
     * @return void
     */
    public function newAction()
    {
        View::renderTemplate('Expense/new.html');
    }

    /**
     * Adding expenses
     * 
     * @return void
     */
    public function addAction()
    {   
        $expense = new UserExpense($_POST);

        if ($expense->saveExpense()) {
            Flash::addMessage('Expense added');
            View::renderTemplate('Home/index.html');

        } else {

            View::renderTemplate('Expense/new.html', [
                'expense' => $expense
            ]);
            
        }

    }
}