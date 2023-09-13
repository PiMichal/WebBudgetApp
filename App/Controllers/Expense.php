<?php

namespace App\Controllers;

use Core\View;
use App\Models\UserExpense;
use App\Flash;

class Expense extends Authenticated
{
    public function newAction()
    {

        View::renderTemplate('Expense/new.html', [
            'category' => UserExpense::expenseCategory(),
            'payment_methods' => UserExpense::paymentMethods(),
            'date_of_expense' => UserExpense::currentDate()
        ]);
    }

    public function addAction()
    {
        if (UserExpense::saveExpense()) {
            Flash::addMessage('Expense added');
            View::renderTemplate('Home/index.html');
        } else {
            View::renderTemplate('Expense/new.html', [
                'errors' => UserExpense::validate(),
                'category' => UserExpense::expenseCategory(),
                'payment_methods' => UserExpense::paymentMethods(),
            ]);
        }
    }
}
