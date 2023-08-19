<?php

namespace App\Controllers;

use App\Models\UserBalance;
use Core\View;

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
        
        $incomeData = $data->getIncome();

        $countTotalIncome = $data->countTotalIncome();

        View::renderTemplate('Balance/showIncomeDetails.html', [
            'incomeData' => $incomeData,
            'countTotalIncome' => $countTotalIncome,
            'date' => $data
        ]);

    }

}