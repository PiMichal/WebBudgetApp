<?php

namespace App\Models;


/**
 * Balance model
 *
 * PHP version 7.0
 */

use App\Auth;
use PDO;

class UserBalance extends \Core\Model
{
    public $start_date;
    public $end_date;

    public function getIncome()
    {

        $this->dateSetting();

        return UserIncome::getIncome($this->start_date, $this->end_date);
    }

    public function getAllIncome()
    {

        $this->dateSetting();

        return UserIncome::getAllIncome($this->start_date, $this->end_date);
    }

    public function countTotalIncome()
    {
        return UserIncome::countTotalIncome($this->start_date, $this->end_date);
    }

    public function incomeUpdate()
    {
        UserIncome::incomeUpdate($_POST);
    }

    public function incomeCategory()
    {
        return UserIncome::incomeCategory();
    }

    public function incomeDelete()
    {
        UserIncome::incomeDelete($_POST);
    }

    public function getExpense()
    {

        $this->dateSetting();

        return UserExpense::getExpense($this->start_date, $this->end_date);
    }

    public function getAllExpense()
    {

        $this->dateSetting();

        return UserExpense::getAllExpense($this->start_date, $this->end_date);
    }

    public function countTotalExpense()
    {
        return UserExpense::countTotalExpense($this->start_date, $this->end_date);
    }

    public function expenseUpdate()
    {
        UserExpense::expenseUpdate($_POST);
    }

    public function expenseCategory()
    {

        return UserExpense::expenseCategory();
    }

    public function paymentMethods()
    {

        return UserExpense::paymentMethods();
    }

    public function expenseDelete()
    {
        UserExpense::expenseDelete($_POST);
    }

    /**
     * Setting the right date
     * 
     * @return void
     */
    public function dateSetting()
    {

        if (isset($_POST["start_date"]) && isset($_POST["end_date"])) {
            $this->start_date = $_POST['start_date'];
            $this->end_date = $_POST['end_date'];
        } else {
            $this->start_date = date('Y-m-01');
            $this->end_date = date('Y-m-t');
        }
    }

    public function grandTotal()
    {

        $sum = $this->countTotalIncome() - $this->countTotalExpense();

        return $sum;
    }
}
