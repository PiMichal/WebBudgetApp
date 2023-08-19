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

    $this->validate();

        if ($this->start_date && $this->end_date) {

            $user = Auth::getUser();

            $sql = "SELECT name AS income_name, SUM(amount) AS income_amount 
                    FROM incomes, incomes_category_assigned_to_users 
                    WHERE incomes.user_id = :userId
                    AND incomes.income_category_assigned_to_user_id = incomes_category_assigned_to_users.id
                    AND incomes.date_of_income BETWEEN :startDate AND :endDate
                    GROUP BY income_category_assigned_to_user_id";
    
            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':userId', $user->id , PDO::PARAM_INT);
            $stmt->bindValue(':startDate', $this->start_date, PDO::PARAM_STR);
            $stmt->bindValue(':endDate', $this->end_date, PDO::PARAM_STR);
            
            $stmt->execute();
    
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $data;
        }

        return false;

    }

    public function countTotalIncome()
    {
      
      $user = Auth::getUser();

      $sql = "SELECT SUM(amount) AS income_sum
              FROM incomes, incomes_category_assigned_to_users
              WHERE incomes.income_category_assigned_to_user_id = incomes_category_assigned_to_users.id
              AND incomes.user_id = :userId 
              AND incomes.date_of_income BETWEEN :startDate AND :endDate";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':userId', $user->id , PDO::PARAM_INT);
      $stmt->bindValue(':startDate', $this->start_date, PDO::PARAM_STR);
      $stmt->bindValue(':endDate', $this->end_date, PDO::PARAM_STR);

      $stmt->execute();

      $sum = $stmt->fetchColumn();
      

      return $sum;
    }



   public function getExpense()
   {

    $this->validate();

       if ($this->start_date && $this->end_date) {

           $user = Auth::getUser(); 

           $sql = "SELECT name AS expense_name, SUM(amount) AS expense_amount 
                   FROM expenses, expenses_category_assigned_to_users 
                   WHERE expenses.user_id = :userId
                   AND expenses.expense_category_assigned_to_user_id = expenses_category_assigned_to_users.id
                   AND expenses.date_of_expense BETWEEN :startDate AND :endDate
                   GROUP BY expense_category_assigned_to_user_id
                   ORDER BY date_of_expense DESC";
   
           $db = static::getDB();
           $stmt = $db->prepare($sql);

           $stmt->bindValue(':userId', $user->id , PDO::PARAM_INT);
           $stmt->bindValue(':startDate', $this->start_date, PDO::PARAM_STR);
           $stmt->bindValue(':endDate', $this->end_date, PDO::PARAM_STR);
           
           $stmt->execute();
   
           $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

           return $data;
       }

       return false;

   }

   public function countTotalExpense()
   {
     
     $user = Auth::getUser();

     $sql = "SELECT SUM(amount) AS expense_sum
             FROM expenses, expenses_category_assigned_to_users 
             WHERE expenses.expense_category_assigned_to_user_id = expenses_category_assigned_to_users.id
             AND expenses.user_id = :userId 
             AND expenses.date_of_expense BETWEEN :startDate AND :endDate";

     $db = static::getDB();
     $stmt = $db->prepare($sql);

     $stmt->bindValue(':userId', $user->id , PDO::PARAM_INT);
     $stmt->bindValue(':startDate', $this->start_date, PDO::PARAM_STR);
     $stmt->bindValue(':endDate', $this->end_date, PDO::PARAM_STR);

     $stmt->execute();

     $sum = $stmt->fetchColumn();

     return $sum;
   }

       /**
    * Validate current property values, adding valitation error messages to the errors array property
    * 
    * @return void
    */
    public function validate()
    {
        
       if ($this->start_date == '' || $this->end_date == '') {
          $this->start_date = date('Y-m-01');
          $this->end_date = date('Y-m-t');
 
       } else if (isset($_POST["start_date"]) && isset($_POST["end_date"])) {
          $this->start_date = $_POST['start_date'];
          $this->end_date = $_POST['end_date'];
       } 

    }

    public function grandTotal()
    {

        $sum = $this->countTotalIncome() - $this->countTotalExpense();

        return $sum;
    }

   }


