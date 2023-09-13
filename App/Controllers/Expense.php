<?php

namespace App\Controllers;

use Core\View;
use App\Models\UserExpense;
use App\Flash;

class Expense extends Authenticated
{
    public function newAction()
    {
        $date = new UserExpense();
        $date->dateSetting();

        View::renderTemplate('Expense/new.html', [
            'category' => UserExpense::expenseCategory(),
            'payment_methods' => UserExpense::paymentMethods(),
            'expense' => $date
        ]);
    }

    public function addAction()
    {
        $expense = new UserExpense($_POST);

        if ($expense->saveExpense()) {
            Flash::addMessage('Expense added');
            View::renderTemplate('Home/index.html');
        } else {

            View::renderTemplate('Expense/new.html', [
                'category' => UserExpense::expenseCategory(),
                'payment_methods' => UserExpense::paymentMethods(),
                'expense' => $expense
            ]);
        }
    }
}
