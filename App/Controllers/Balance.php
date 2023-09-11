<?php

namespace App\Controllers;

use App\Models\UserBalance;
use Core\View;
use App\Flash;

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

        $data = new UserBalance();

        $incomeData = $data->getIncome();

        $countTotalIncome = $data->countTotalIncome();

        $expenseData = $data->getExpense();
        $countTotalExpense = $data->countTotalExpense();

        $calculatedBalance = $data->grandTotal();

        View::renderTemplate('Balance/show.html', [
            'incomeData' => $incomeData,
            'expenseData' => $expenseData,
            'countTotalIncome' => $countTotalIncome,
            'countTotalExpense' => $countTotalExpense,
            'calculatedBalance' => $calculatedBalance,
            'date' => $data
        ]);
    }

    /**
     * Displaying data from a specific period
     * 
     * @return void
     */
    public function dateAction()
    {

        $data = new UserBalance();

        if ($data->getIncome() || $data->getExpense()) {

            $incomeData = $data->getIncome();
            $countTotalIncome = $data->countTotalIncome();

            $expenseData = $data->getExpense();
            $countTotalExpense = $data->countTotalExpense();

            $calculatedBalance = $data->grandTotal();

            View::renderTemplate('Balance/show.html', [
                'incomeData' => $incomeData,
                'expenseData' => $expenseData,
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

        $data = new UserBalance();

        $incomeData = $data->getAllIncome();

        $countTotalIncome = $data->countTotalIncome();

        View::renderTemplate('Balance/showIncomeDetails.html', [
            'incomeData' => $incomeData,
            'countTotalIncome' => $countTotalIncome,
            'date' => $data
        ]);
    }

    /**
     * Edit income details
     * 
     * @return void
     */
    public function incomeEditAction()
    {


        $data = new UserBalance();

        $id = $_POST["edit"];
        $incomeData = $data->getAllIncome();
        $category = $data->incomeCategory();

        $countTotalIncome = $data->countTotalIncome();

        View::renderTemplate('Balance/incomeEdit.html', [
            'incomeData' => $incomeData,
            'countTotalIncome' => $countTotalIncome,
            'id' => $id,
            'date' => $data,
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

        $data = new UserBalance();

        $data->incomeUpdate();

        $incomeData = $data->getAllIncome();

        $countTotalIncome = $data->countTotalIncome();

        Flash::addMessage('Successfully updated data');
        View::renderTemplate('Balance/showIncomeDetails.html', [
            'incomeData' => $incomeData,
            'countTotalIncome' => $countTotalIncome,
            'editNumber' => $_POST["id"],
            'date' => $data
        ]);
    }

    /**
     * Delete the selected income item
     * 
     * @return void
     */
    public function incomeDeleteAction()
    {

        $data = new UserBalance();

        $data->incomeDelete();

        $incomeData = $data->getAllIncome();

        $countTotalIncome = $data->countTotalIncome();
        Flash::addMessage('Successful deletion of data', Flash::INFO);
        View::renderTemplate('Balance/showIncomeDetails.html', [
            'incomeData' => $incomeData,
            'countTotalIncome' => $countTotalIncome,
            'date' => $data
        ]);
    }

    /**
     * Show detailed expense data
     * 
     * @return void
     */
    public function expenseDetailsAction()
    {

        $data = new UserBalance();

        $expenseData = $data->getAllExpense();

        $countTotalExpense = $data->countTotalExpense();

        View::renderTemplate('Balance/showExpenseDetails.html', [
            'expenseData' => $expenseData,
            'countTotalExpense' => $countTotalExpense,
            'date' => $data
        ]);
    }

    /**
     * Edit expense details
     * 
     * @return void
     */
    public function expenseEditAction()
    {
        $data = new UserBalance();

        $id = $_POST["edit"];
        $expenseData = $data->getAllExpense();
        $category = $data->expenseCategory();
        $paymentMethods = $data->paymentMethods();
        $countTotalExpense = $data->countTotalExpense();

        View::renderTemplate('Balance/expenseEdit.html', [
            'expenseData' => $expenseData,
            'countTotalExpense' => $countTotalExpense,
            'id' => $id,
            'date' => $data,
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

        $data = new UserBalance();

        $data->expenseUpdate();

        $expenseData = $data->getAllExpense();

        $countTotalExpense = $data->countTotalExpense();

        Flash::addMessage('Successfully updated data');
        View::renderTemplate('Balance/showExpenseDetails.html', [
            'expenseData' => $expenseData,
            'countTotalExpense' => $countTotalExpense,
            'editNumber' => $_POST["id"],
            'date' => $data
        ]);
    }

    /**
     * Delete the selected expense item
     * 
     * @return void
     */
    public function expenseDeleteAction()
    {

        $data = new UserBalance();

        $data->expenseDelete();

        $expenseData = $data->getAllExpense();

        $countTotalExpense = $data->countTotalExpense();

        Flash::addMessage('Successful deletion of data', Flash::INFO);
        View::renderTemplate('Balance/showExpenseDetails.html', [
            'expenseData' => $expenseData,
            'countTotalExpense' => $countTotalExpense,
            'date' => $data
        ]);
    }
}
