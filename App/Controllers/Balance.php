<?php

namespace App\Controllers;

use App\Models\UserBalance;
use Core\View;
use App\Flash;
use App\Models\UserExpense;
use App\Models\UserIncome;

/**
 * Balance controller
 * 
 * PHP version 7.0
 */

class Balance extends Authenticated
{
    /**
     * Show the balance page
     * 
     * @return void
     */
    public function newAction()
    {
        $incomeData = new UserIncome();
        $expenseData = new UserExpense();

        $income = $incomeData->getIncome();

        $countTotalIncome = $incomeData->countTotalIncome();

        $expense = $expenseData->getExpense();
        $countTotalExpense = $expenseData->countTotalExpense();

        $calculatedBalance = $incomeData->countTotalIncome() - $expenseData->countTotalExpense();

        View::renderTemplate('Balance/show.html', [
            'incomeData' => $income,
            'expenseData' => $expense,
            'countTotalIncome' => $countTotalIncome,
            'countTotalExpense' => $countTotalExpense,
            'calculatedBalance' => $calculatedBalance,
            'date' => $incomeData
        ]);
    }

    /**
     * Displaying data from a specific period
     * 
     * @return void
     */
    public function dateAction()
    {
        $incomeData = new UserIncome();
        $expenseData = new UserExpense();

        if ($incomeData->getIncome() || $expenseData->getExpense()) {

            $income = $incomeData->getIncome();
            $countTotalIncome = $incomeData->countTotalIncome();

            $expense = $expenseData->getExpense();
            $countTotalExpense = $expenseData->countTotalExpense();

            $calculatedBalance = $incomeData->countTotalIncome() - $expenseData->countTotalExpense();

            View::renderTemplate('Balance/show.html', [
                'incomeData' => $income,
                'expenseData' => $expense,
                'countTotalIncome' => $countTotalIncome,
                'countTotalExpense' => $countTotalExpense,
                'calculatedBalance' => $calculatedBalance,
                'date' => $_POST
            ]);
        } else {

            View::renderTemplate('Balance/show.html', [
                'date' => $_POST
            ]);
        }
    }

    /**
     * Show detailed income data
     * 
     * @return void
     */
    public function incomeDetailsAction()
    {
        $incomeData = new UserIncome();

        $income = $incomeData->getAllIncome();

        $countTotalIncome = $incomeData->countTotalIncome();

        View::renderTemplate('Balance/showIncomeDetails.html', [
            'incomeData' => $income,
            'countTotalIncome' => $countTotalIncome,
            'date' => $incomeData
        ]);
    }

    /**
     * Edit income details
     * 
     * @return void
     */
    public function incomeEditAction()
    {
        $incomeData = new UserIncome();

        $id = $_POST["edit"];
        $income = $incomeData->getAllIncome();
        $category = $incomeData->incomeCategory();

        $countTotalIncome = $incomeData->countTotalIncome();

        View::renderTemplate('Balance/incomeEdit.html', [
            'incomeData' => $income,
            'countTotalIncome' => $countTotalIncome,
            'id' => $id,
            'date' => $incomeData,
            'category' => $category
        ]);
    }

    /**
     * Update the selected income item
     * 
     * @return void
     */
    public function incomeUpdateAction()
    {

        $incomeData = new UserIncome();

        $incomeData->incomeUpdate();

        $income = $incomeData->getAllIncome();

        $countTotalIncome = $incomeData->countTotalIncome();

        Flash::addMessage('Successfully updated data');
        View::renderTemplate('Balance/showIncomeDetails.html', [
            'incomeData' => $income,
            'countTotalIncome' => $countTotalIncome,
            'editNumber' => $_POST["id"],
            'date' => $incomeData
        ]);
    }

    /**
     * Delete the selected income item
     * 
     * @return void
     */
    public function incomeDeleteAction()
    {

        $incomeData = new UserIncome();

        $incomeData->incomeDelete();

        $income = $incomeData->getAllIncome();

        $countTotalIncome = $incomeData->countTotalIncome();
        Flash::addMessage('Successful deletion of data', Flash::INFO);
        View::renderTemplate('Balance/showIncomeDetails.html', [
            'incomeData' => $income,
            'countTotalIncome' => $countTotalIncome,
            'date' => $incomeData
        ]);
    }

    /**
     * Show detailed expense data
     * 
     * @return void
     */
    public function expenseDetailsAction()
    {
        $expenseData = new UserExpense();

        $expense = $expenseData->getAllExpense();

        $countTotalExpense = $expenseData->countTotalExpense();

        View::renderTemplate('Balance/showExpenseDetails.html', [
            'expenseData' => $expense,
            'countTotalExpense' => $countTotalExpense,
            'date' => $expenseData
        ]);
    }

    /**
     * Edit expense details
     * 
     * @return void
     */
    public function expenseEditAction()
    {
        $expenseData = new UserExpense();

        $id = $_POST["edit"];
        $expense = $expenseData->getAllExpense();
        $category = $expenseData->expenseCategory();
        $paymentMethods = $expenseData->paymentMethods();
        $countTotalExpense = $expenseData->countTotalExpense();

        View::renderTemplate('Balance/expenseEdit.html', [
            'expenseData' => $expense,
            'countTotalExpense' => $countTotalExpense,
            'id' => $id,
            'date' => $expenseData,
            'category' => $category,
            'paymentMethods' => $paymentMethods
        ]);
    }

    /**
     * Update the selected expense item
     * 
     * @return void
     */
    public function expenseUpdateAction()
    {

        $expenseData = new UserExpense();

        $expenseData->expenseUpdate();

        $expense = $expenseData->getAllExpense();

        $countTotalExpense = $expenseData->countTotalExpense();

        Flash::addMessage('Successfully updated data');
        View::renderTemplate('Balance/showExpenseDetails.html', [
            'expenseData' => $expense,
            'countTotalExpense' => $countTotalExpense,
            'editNumber' => $_POST["id"],
            'date' => $expenseData
        ]);
    }

    /**
     * Delete the selected expense item
     * 
     * @return void
     */
    public function expenseDeleteAction()
    {

        $expenseData = new UserExpense();

        $expenseData->expenseDelete();

        $expense = $expenseData->getAllExpense();

        $countTotalExpense = $expenseData->countTotalExpense();

        Flash::addMessage('Successful deletion of data', Flash::INFO);
        View::renderTemplate('Balance/showExpenseDetails.html', [
            'expenseData' => $expense,
            'countTotalExpense' => $countTotalExpense,
            'date' => $expenseData
        ]);
    }
}
