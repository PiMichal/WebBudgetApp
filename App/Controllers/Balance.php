<?php

namespace App\Controllers;

use Core\View;
use App\Flash;
use App\Models\UserExpense;
use App\Models\UserIncome;

class Balance extends Authenticated
{

    public function newAction()
    {
        if (!empty(UserIncome::getIncome()) && !empty(UserExpense::getExpense())) {

            $calculatedBalance = UserIncome::countTotalIncome() - UserExpense::countTotalExpense();

            View::renderTemplate('Balance/show.html', [
                'incomeData' => UserIncome::getIncome(),
                'expenseData' => UserExpense::getExpense(),
                'countTotalIncome' => UserIncome::countTotalIncome(),
                'countTotalExpense' => UserExpense::countTotalExpense(),
                'calculatedBalance' => $calculatedBalance,
                'date' => UserIncome::dateSetting()
            ]);
        } else if (!empty(UserIncome::getIncome()) && empty(UserExpense::getExpense())) {
            View::renderTemplate('Balance/showExpenseEmpty.html', [
                'incomeData' => UserIncome::getIncome(),
                'countTotalIncome' => UserIncome::countTotalIncome(),
                'date' => UserIncome::dateSetting()
            ]);
        } else if (empty(UserIncome::getIncome()) && !empty(UserExpense::getExpense())) {

            View::renderTemplate('Balance/showIncomeEmpty.html', [
                'expenseData' => UserExpense::getExpense(),
                'countTotalExpense' => UserExpense::countTotalExpense(),
                'date' => UserExpense::dateSetting()
            ]);
        } else {
            View::renderTemplate('Balance/showEmpty.html', [
                'date' => UserExpense::dateSetting()
            ]);
        }
    }

    public function dateAction()
    {

        if (!empty(UserIncome::getIncome()) && !empty(UserExpense::getExpense())) {

            $calculatedBalance = UserIncome::countTotalIncome() - UserExpense::countTotalExpense();

            View::renderTemplate('Balance/show.html', [
                'incomeData' => UserIncome::getIncome(),
                'expenseData' => UserExpense::getExpense(),
                'countTotalIncome' => UserIncome::countTotalIncome(),
                'countTotalExpense' => UserExpense::countTotalExpense(),
                'calculatedBalance' => $calculatedBalance,
                'date' => $_POST
            ]);
        } else if (!empty(UserIncome::getIncome()) && empty(UserExpense::getExpense())) {

            View::renderTemplate('Balance/showExpenseEmpty.html', [
                'incomeData' => UserIncome::getIncome(),
                'countTotalIncome' => UserIncome::countTotalIncome(),
                'date' => $_POST
            ]);
        } else if (empty(UserIncome::getIncome()) && !empty(UserExpense::getExpense())) {

            View::renderTemplate('Balance/showIncomeEmpty.html', [
                'expenseData' => UserExpense::getExpense(),
                'countTotalExpense' => UserExpense::countTotalExpense(),
                'date' => $_POST
            ]);
        } else {

            View::renderTemplate('Balance/showEmpty.html', [
                'date' => $_POST
            ]);
        }
    }

    public function incomeDetailsAction()
    {
        View::renderTemplate('Balance/showIncomeDetails.html', [
            'incomeData' => UserIncome::getAllIncome(),
            'countTotalIncome' => UserIncome::countTotalIncome(),
            'date' => $_POST
        ]);
    }

    public function incomeEditAction()
    {
        View::renderTemplate('Balance/incomeEdit.html', [
            'incomeData' => UserIncome::getAllIncome(),
            'countTotalIncome' => UserIncome::countTotalIncome(),
            'id' => $_POST["edit"],
            'date' => $_POST,
            'category' => UserIncome::incomeCategory()
        ]);
    }

    public function incomeUpdateAction()
    {
        UserIncome::incomeUpdate();

        Flash::addMessage('Successfully updated data');
        View::renderTemplate('Balance/showIncomeDetails.html', [
            'incomeData' => UserIncome::getAllIncome(),
            'countTotalIncome' => UserIncome::countTotalIncome(),
            'editNumber' => $_POST["id"],
            'date' => $_POST
        ]);
    }

    public function incomeDeleteAction()
    {
        UserIncome::incomeDelete();

        if (empty(UserIncome::getAllIncome())) {
            Flash::addMessage('Successful deletion of data', Flash::INFO);
            $this->redirect('/balance/new');
        } else {
            Flash::addMessage('Successful deletion of data', Flash::INFO);
            View::renderTemplate('Balance/showIncomeDetails.html', [
                'incomeData' => UserIncome::getAllIncome(),
                'countTotalIncome' => UserIncome::countTotalIncome(),
                'date' => $_POST
            ]);
        }
    }

    public function expenseDetailsAction()
    {
        View::renderTemplate('Balance/showExpenseDetails.html', [
            'expenseData' => UserExpense::getAllExpense(),
            'countTotalExpense' => UserExpense::countTotalExpense(),
            'date' => $_POST
        ]);
    }

    public function expenseEditAction()
    {
        View::renderTemplate('Balance/expenseEdit.html', [
            'expenseData' => UserExpense::getAllExpense(),
            'countTotalExpense' => UserExpense::countTotalExpense(),
            'id' => $_POST["edit"],
            'date' => $_POST,
            'category' => UserExpense::expenseCategory(),
            'paymentMethods' => UserExpense::paymentMethods()
        ]);
    }

    public function expenseUpdateAction()
    {
        UserExpense::expenseUpdate();

        Flash::addMessage('Successfully updated data');
        View::renderTemplate('Balance/showExpenseDetails.html', [
            'expenseData' => UserExpense::getAllExpense(),
            'countTotalExpense' => UserExpense::countTotalExpense(),
            'editNumber' => $_POST["id"],
            'date' => $_POST
        ]);
    }

    public function expenseDeleteAction()
    {
        UserExpense::expenseDelete();

        if (empty(UserExpense::getAllExpense())) {
            Flash::addMessage('Successful deletion of data', Flash::INFO);
            $this->redirect('/balance/new');
        } else {
            Flash::addMessage('Successful deletion of data', Flash::INFO);
            View::renderTemplate('Balance/showExpenseDetails.html', [
                'expenseData' => UserExpense::getAllExpense(),
                'countTotalExpense' => UserExpense::countTotalExpense(),
                'date' => $_POST
            ]);
        }
    }
}
