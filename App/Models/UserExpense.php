<?php

namespace App\Models;

use App\Auth;
use PDO;
/**
 * Income model
 *
 * PHP version 7.0
 */

class UserExpense extends \Core\Model
{
   public $user_id;
   /**
    * Amount of expense
    * @var int
    */
   public $amount;

   /**
    * Date of expense
    * @var int
    */
   public $date_of_expense;

   /**
    * Expense category
    * @var int
    */
   public $expense_category_assigned_to_user_id;

   /**
    * Expense payment method
    * @var int
    */
    public $payment_method_assigned_to_user_id;

   /**
    * Expense commentary (optional)
    * @var string
    */
   public $expense_comment;

   /**
    * Error messages
    * 
    * @var array
    */
   public $errors = [];

   /**
    * Class constructor
    * 
    * @param array $data Initial property values
    * 
    * @return void
    */
   public function __construct($data = [])
   {
      foreach ($data as $key => $value) {
         $this->$key = $value;
      };
   }

   /**
    * Validate current property values, adding valitation error messages to the errors array property
    * 
    * @return void
    */
    public function validate()
    {
       // username
       if ($this->amount == '') {
          $this->errors[] = 'Amount is required';
       }
 
       if ($this->date_of_expense == '') {
          $this->errors[] = 'Date is required';
       }
    }

   /**
    * Copy default categories during registration
    * 
    * @return void
    */

   public static function createDefault($email)
   {
      $user = User::findByEmail($email);

      $sql = 'INSERT INTO expenses_category_assigned_to_users (`user_id`, `name`)
                SELECT :user_id, `name` 
                FROM expenses_category_default';

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue('user_id', $user->id, PDO::PARAM_INT);

      $stmt->execute();

      static::createDefaultPaymentMethod($email);
      
   }

   /**
    * Copy default payment method during registration
    * 
    * @return void
    */

   private static function createDefaultPaymentMethod($email)
   {
      $user = User::findByEmail($email);

      $sql = 'INSERT INTO payment_methods_assigned_to_users (`user_id`, `name`)
                SELECT :user_id, `name` 
                FROM payment_methods_default';

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue('user_id', $user->id, PDO::PARAM_INT);

      $stmt->execute();
   }

   /**
    * Saving the entered expenses in the database
    *
    * @return array
    */
   public function saveExpense()
   {
      $this->validate();
      if (empty($this->errors)) {

         $user = Auth::getUser();
         
         $sql = 'INSERT INTO expenses (user_id, expense_category_assigned_to_user_id, payment_method_assigned_to_user_id, amount, date_of_expense, expense_comment)
         VALUES (:user_id, 
         (SELECT id FROM expenses_category_assigned_to_users WHERE name = :expense_category_assigned_to_user_id AND user_id = :user_id),
         (SELECT id FROM payment_methods_assigned_to_users WHERE name = :payment_method_assigned_to_user_id AND user_id = :user_id), 
         :amount, 
         :date_of_expense, 
         :expense_comment)';

         $db = static::getDB();
         $stmt = $db->prepare($sql);

         $stmt->bindValue('user_id', $user->id, PDO::PARAM_INT);
         $stmt->bindValue('expense_category_assigned_to_user_id', $this->expense_category_assigned_to_user_id, PDO::PARAM_STR);
         $stmt->bindValue('payment_method_assigned_to_user_id', $this->payment_method_assigned_to_user_id, PDO::PARAM_STR);
         $stmt->bindValue('amount', $this->amount, PDO::PARAM_STR);
         $stmt->bindValue('date_of_expense', $this->date_of_expense, PDO::PARAM_STR);
         $stmt->bindValue('expense_comment', $this->expense_comment, PDO::PARAM_STR);

         return $stmt->execute();
      }
      return false;
   }

   public static function getExpense($start_date, $end_date)
   {
      $user = Auth::getUser();

        $sql = "SELECT name AS expense_name, SUM(amount) AS expense_amount, date_of_expense, expense_comment 
                   FROM expenses, expenses_category_assigned_to_users 
                   WHERE expenses.user_id = :userId
                   AND expenses.expense_category_assigned_to_user_id = expenses_category_assigned_to_users.id
                   AND expenses.date_of_expense BETWEEN :startDate AND :endDate
                   GROUP BY expense_category_assigned_to_user_id
                   ORDER BY date_of_expense DESC";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':userId', $user->id, PDO::PARAM_INT);
        $stmt->bindValue(':startDate', $start_date, PDO::PARAM_STR);
        $stmt->bindValue(':endDate', $end_date, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetchAll();

   }

   public static function getAllExpense($start_date, $end_date)
   {
      $user = Auth::getUser();

      $sql = "SELECT expenses.id, expenses_category_assigned_to_users.name AS expense_name, amount AS expense_amount, date_of_expense, expense_comment, payment_methods_assigned_to_users.name AS payment_methods 
              FROM expenses, expenses_category_assigned_to_users, payment_methods_assigned_to_users
              WHERE expenses.user_id = :userId
              AND expenses.expense_category_assigned_to_user_id = expenses_category_assigned_to_users.id
              AND expenses.payment_method_assigned_to_user_id = payment_methods_assigned_to_users.id
              AND expenses.date_of_expense BETWEEN :startDate AND :endDate
              ORDER BY expenses.date_of_expense DESC";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':userId', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':startDate', $start_date, PDO::PARAM_STR);
      $stmt->bindValue(':endDate', $end_date, PDO::PARAM_STR);

      $stmt->execute();

      return $stmt->fetchAll();
   }

   public static function countTotalExpense($start_date, $end_date)
   {
      $user = Auth::getUser();

      $sql = "SELECT SUM(amount) AS expense_sum
           FROM expenses, expenses_category_assigned_to_users 
           WHERE expenses.expense_category_assigned_to_user_id = expenses_category_assigned_to_users.id
           AND expenses.user_id = :userId 
           AND expenses.date_of_expense BETWEEN :startDate AND :endDate";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':userId', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':startDate', $start_date, PDO::PARAM_STR);
      $stmt->bindValue(':endDate', $end_date, PDO::PARAM_STR);

      $stmt->execute();

      return $stmt->fetchColumn();

   }

   public static function expenseUpdate($data)
   {
      $user = Auth::getUser();
      $sql = "UPDATE expenses
              SET expense_category_assigned_to_user_id = (SELECT id FROM expenses_category_assigned_to_users WHERE name = :expense_category_assigned_to_user_id AND user_id = :user_id), 
              amount = :amount, 
              date_of_expense = :date_of_expense, 
              expense_comment = :expense_comment,
              payment_method_assigned_to_user_id = (SELECT id FROM payment_methods_assigned_to_users WHERE name = :payment_method_assigned_to_user_id AND user_id = :user_id)
              WHERE id = :id";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':expense_category_assigned_to_user_id', $data["expense_category_assigned_to_user_id"], PDO::PARAM_STR);
      $stmt->bindValue(':amount', $data["expense_amount"], PDO::PARAM_STR);
      $stmt->bindValue(':date_of_expense', $data["date_of_expense"], PDO::PARAM_STR);
      $stmt->bindValue(':expense_comment', $data["expense_comment"], PDO::PARAM_STR);
      $stmt->bindValue(':payment_method_assigned_to_user_id', $data["payment_method_assigned_to_user_id"], PDO::PARAM_STR);
      $stmt->bindValue(':id', $data["id"], PDO::PARAM_INT);

      $stmt->execute();
   }

   public static function expenseCategory()
   {
      $user = Auth::getUser();


      $sql = "SELECT expenses_category_assigned_to_users.name AS expense_name
              FROM expenses_category_assigned_to_users
              WHERE expenses_category_assigned_to_users.user_id = :userId";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':userId', $user->id, PDO::PARAM_INT);

      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }

   public static function paymentMethods()
   {
      $user = Auth::getUser();


      $sql = "SELECT payment_methods_assigned_to_users.name AS payment_methods
              FROM payment_methods_assigned_to_users
              WHERE payment_methods_assigned_to_users.user_id = :userId";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':userId', $user->id, PDO::PARAM_INT);

      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }

   public static function expenseDelete($data)
   {
      $sql = "DELETE FROM expenses
             WHERE id = :id";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':id', $data["delete"], PDO::PARAM_INT);

      $stmt->execute();
   }

}
