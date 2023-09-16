<?php

namespace App\Controllers;

use App\Auth;
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

    public function limitAction()
    {
        $category = $this->route_params['category'];
        $category = str_replace('_', ' ', $category);

        echo json_encode(UserExpense::getLimit($category), JSON_UNESCAPED_UNICODE);
    }

    public function limitValueAction()
    {
        $category = $this->route_params['category'];
        $date = $this->route_params['date'];

        echo json_encode(UserExpense::getExpenseMonthlySum($category, $date), JSON_UNESCAPED_UNICODE);
    }
}
