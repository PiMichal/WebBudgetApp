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
    * Assign the appropriate number to the name and return the value
    *
    * @return int
    */
   public function findCategory()
   {
      $user = Auth::getUser();

      $sql = "SELECT id
               FROM expenses_category_assigned_to_users 
               WHERE name = :expense_category_assigned_to_user_id
               AND user_id = :user_id";

      $db = static::getDB();
      
      $stmt = $db->prepare($sql);

      $stmt->execute(['expense_category_assigned_to_user_id' => $this->expense_category_assigned_to_user_id, 'user_id' => $user->id]);

      $result = $stmt->fetch();

      $result = (int) $result['id'];

      $this->expense_category_assigned_to_user_id = $result;

      static::findPaymentMethod();
   }

   /**
    * Assign the appropriate number to the name and return the value
    *
    * @return int
    */
   private function findPaymentMethod()
   {
      $user = Auth::getUser();

      $sql = "SELECT id
               FROM payment_methods_assigned_to_users 
               WHERE name = :payment_method_assigned_to_user_id
               AND user_id = :user_id";

      $db = static::getDB();
      
      $stmt = $db->prepare($sql);

      $stmt->execute(['payment_method_assigned_to_user_id' => $this->payment_method_assigned_to_user_id, 'user_id' => $user->id]);

      $result = $stmt->fetch();

      $result = (int) $result['id'];

      $this->payment_method_assigned_to_user_id = $result;
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

         static::findCategory();
         $user = Auth::getUser();
         
         $sql = 'INSERT INTO expenses (user_id, expense_category_assigned_to_user_id, payment_method_assigned_to_user_id, amount, date_of_expense, expense_comment)
         VALUES (:user_id, :expense_category_assigned_to_user_id, :payment_method_assigned_to_user_id, :amount, :date_of_expense, :expense_comment)';

         $db = static::getDB();
         $stmt = $db->prepare($sql);

         $stmt->bindValue('user_id', $user->id, PDO::PARAM_INT);
         $stmt->bindValue('expense_category_assigned_to_user_id', $this->expense_category_assigned_to_user_id, PDO::PARAM_INT);
         $stmt->bindValue('payment_method_assigned_to_user_id', $this->payment_method_assigned_to_user_id, PDO::PARAM_INT);
         $stmt->bindValue('amount', $this->amount, PDO::PARAM_STR);
         $stmt->bindValue('date_of_expense', $this->date_of_expense, PDO::PARAM_STR);
         $stmt->bindValue('expense_comment', $this->expense_comment, PDO::PARAM_STR);

         return $stmt->execute();
      }
      return false;
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
}
