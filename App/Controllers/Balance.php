<?php

namespace App\Controllers;

use App\Models\UserBalance;
use Core\View;
use App\Flash;

/**
 * Income controller
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

        $data = New UserBalance();

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

    public function dateAction()
    {

        $data = New UserBalance();

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

    public function incomeDetailsAction()
    {

        $data = New UserBalance();
        
        $incomeData = $data->getAllIncome();
        
        $countTotalIncome = $data->countTotalIncome();

        View::renderTemplate('Balance/showIncomeDetails.html', [
            'incomeData' => $incomeData,
            'countTotalIncome' => $countTotalIncome,
            'date' => $data
        ]);

    }
    
    public function incomeEditAction()
    {

        
        $data = New UserBalance();
        
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

    public function incomeUpdateAction()
    {

        $data = New UserBalance();

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

    public function incomeDeleteAction()
    {

        $data = New UserBalance();
        
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

    public function expenseDetailsAction()
    {

        $data = New UserBalance();
        
        $expenseData = $data->getAllExpense();
        
        $countTotalExpense = $data->countTotalExpense();

        View::renderTemplate('Balance/showExpenseDetails.html', [
            'expenseData' => $expenseData,
            'countTotalExpense' => $countTotalExpense,
            'date' => $data
        ]);
    }

    public function expenseEditAction()
    {
        $data = New UserBalance();

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

    public function expenseUpdateAction()
    {

        $data = New UserBalance();

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

    public function expenseDeleteAction()
    {

        $data = New UserBalance();
        
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