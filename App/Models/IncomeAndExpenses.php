<?php

namespace App\Models;

use App\Auth;
use PDO;
/**
 * Income model
 *
 * PHP version 7.0
 */

class IncomeAndExpenses extends \Core\Model
{
   public $user_id;
   /**
    * Amount of income
    * @var int
    */
   public $amount;

   /**
    * Date of income
    * @var int
    */
   public $date_of_income;

   /**
    * Income category
    * @var int
    */
   public $income_category_assigned_to_user_id;

   /**
    * Income commentary (optional)
    * @var string
    */
   public $income_comment;

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

      $sql = 'INSERT INTO incomes_category_assigned_to_users (`user_id`, `name`)
                SELECT :user_id, `name` 
                FROM incomes_category_default';

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue('user_id', $user->id, PDO::PARAM_INT);

      $stmt->execute();
   }

   public function findCategory()
   {
      $user = Auth::getUser();

      $sql = "SELECT id
               FROM incomes_category_assigned_to_users 
               WHERE name = :income_category_assigned_to_user_id
               AND user_id = :user_id";

      $db = static::getDB();
      
      $stmt = $db->prepare($sql);

      $stmt->execute(['income_category_assigned_to_user_id' => $this->income_category_assigned_to_user_id, 'user_id' => $user->id]);

      $result = $stmt->fetch();

      $result = (int) $result['id'];

      $this->income_category_assigned_to_user_id = $result;
   }

   /**
    * 
    */
   public function saveIncome()
   {
      
      $this->validate();
      if (empty($this->errors)) {

         static::findCategory();
         $user = Auth::getUser();
         
         $sql = 'INSERT INTO incomes (user_id, income_category_assigned_to_user_id, amount, date_of_income, income_comment)
         VALUES (:user_id, :income_category_assigned_to_user_id, :amount, :date_of_income, :income_comment)';

         $db = static::getDB();
         $stmt = $db->prepare($sql);
   
         $stmt->bindValue('user_id', $user->id, PDO::PARAM_INT);
         $stmt->bindValue('income_category_assigned_to_user_id', $this->income_category_assigned_to_user_id, PDO::PARAM_INT);
         $stmt->bindValue('amount', $this->amount, PDO::PARAM_STR);
         $stmt->bindValue('date_of_income', $this->date_of_income, PDO::PARAM_STR);
         $stmt->bindValue('income_comment', $this->income_comment, PDO::PARAM_STR);

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

      if ($this->date_of_income == '') {
         $this->errors[] = 'Date is required';
      }

   }
}
