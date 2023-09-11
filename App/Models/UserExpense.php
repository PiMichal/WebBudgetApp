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
    * See if a user record already exists with the specified category
    * 
    * @return boolean True if a record already exists with specified category, false otherwise
    */
   public function findByCategory($category)
   {
      $user = Auth::getUser();

      $sql = 'SELECT COUNT(*) AS category
               FROM expenses_category_assigned_to_users 
               WHERE name = :category
               AND user_id = :user_id';

      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindParam('category', $category, PDO::PARAM_STR);
      $stmt->bindParam('user_id', $user->id, PDO::PARAM_INT);

      $stmt->execute();

      $category = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($category['category'] == 0) {
         return false;
      } else {
         return true;
      }
   }

   /**
    * See if a user record already exists with the specified payment method
    * 
    * @return boolean True if a record already exists with specified payment method, false otherwise
    */
   public function findByPaymentMethod($payment_method)
   {
      $user = Auth::getUser();

      $sql = 'SELECT COUNT(*) AS payment_method
               FROM payment_methods_assigned_to_users 
               WHERE name = :payment_method
               AND user_id = :user_id';

      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindParam('payment_method', $payment_method, PDO::PARAM_STR);
      $stmt->bindParam('user_id', $user->id, PDO::PARAM_INT);

      $stmt->execute();

      $payment_method = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($payment_method['payment_method'] == 0) {
         return false;
      } else {
         return true;
      }
   }

   /**
    * Validate current property values, adding valitation error messages to the errors array property
    * 
    * @return void
    */
   public function categoryValidate($category)
   {
      // category

      if (static::findByCategory($category)) {
         $this->errors[] = 'Category already exists';
         return false;
      }

      if (static::findByPaymentMethod($category)) {
         $this->errors[] = 'Payment method already exists';
         return false;
      }

      if ($category == '') {
         $this->errors[] = 'Category is required';
         return false;
      }

      if (!ctype_alpha(str_replace(' ', '', $category))) {
         $this->errors[] = "The string $category does not consist of all letters";
         return false;
      }

      if (strlen($category) > 50) {
         $this->errors[] = 'Too many characters - maximum 50 characters';
         return false;
      }

      return true;
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
    * @return void
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

   /**
    * Display of user expenses over a specified period
    * 
    * @return array
    */
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

   /**
    * View detailed user expenses over a specified period
    * 
    * @return array
    */
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

   /**
    * Calculation of the sum of the user's expenses in a given period
    * 
    * @return string
    */
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

   /**
    * Expense update
    * 
    * @return void
    */
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

   /**
    * Display of expense categories
    * 
    * @return array
    */
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

   /**
    * Save a new category of expense
    * 
    * @return void
    */
   public static function expenseAddCategory($category)
   {
      $user = Auth::getUser();

      $sql = "INSERT INTO expenses_category_assigned_to_users (user_id, name)
               VALUES (:user_id, :name)";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':name', $category, PDO::PARAM_STR);

      $stmt->execute();
   }

   /**
    * Save the new expense category name
    *
    * @return void
    */
   public static function expenseRename($new_category)
   {

      $user = Auth::getUser();

      $sql = "UPDATE expenses_category_assigned_to_users
              SET name = :new_category
              WHERE user_id = :user_id
              AND
              name = :name";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':new_category', $new_category, PDO::PARAM_STR);
      $stmt->bindValue(':name', $_POST['expense_name'], PDO::PARAM_STR);

      $stmt->execute();
   }

   /**
    * Deleting the selected expense category
    *
    * @return void
    */
   public static function categoryDelete()
   {
      static::updateTheDeletedCategory();

      $user = Auth::getUser();

      $sql = "DELETE FROM expenses_category_assigned_to_users
             WHERE user_id = :user_id
             AND
             name = :category";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':category', $_POST["expense_name"], PDO::PARAM_STR);

      $stmt->execute();
   }

   /**
    * Change a deleted category to an existing one
    *
    * @return void
    */
   public static function updateTheDeletedCategory()
   {
      $user = Auth::getUser();

      $sql = "UPDATE expenses
              SET expense_category_assigned_to_user_id = (SELECT id FROM expenses_category_assigned_to_users WHERE user_id = :user_id 
              AND name != :removed_category LIMIT 1)
              WHERE user_id = :user_id
              AND expense_category_assigned_to_user_id = (SELECT id FROM expenses_category_assigned_to_users WHERE user_id = :user_id 
              AND name = :removed_category)";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':removed_category', $_POST["expense_name"], PDO::PARAM_STR);

      $stmt->execute();
   }

   /**
    * Remove of expenses
    *
    * @return void
    */
   public static function expenseDelete($data)
   {
      $sql = "DELETE FROM expenses
             WHERE id = :id";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':id', $data["delete"], PDO::PARAM_INT);

      $stmt->execute();
   }

   /**
    * Remove payment method
    *
    * @return void
    */
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

   /**
    * Save new payment method
    *
    * @return void
    */
   public static function addAPaymentMethod($category)
   {
      $user = Auth::getUser();

      $sql = "INSERT INTO payment_methods_assigned_to_users (user_id, name)
               VALUES (:user_id, :name)";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':name', $category, PDO::PARAM_STR);

      $stmt->execute();
   }

   /**
    * Save the name of the new payment method
    *
    * @return void
    */
   public static function paymentMethodRename($new_payment_method)
   {
      $user = Auth::getUser();

      $sql = "UPDATE payment_methods_assigned_to_users
              SET name = :new_payment_method
              WHERE user_id = :user_id
              AND
              name = :name";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':new_payment_method', $new_payment_method, PDO::PARAM_STR);
      $stmt->bindValue(':name', $_POST['payment_methods'], PDO::PARAM_STR);

      $stmt->execute();
   }

   /**
    * Deleting the selected payment method
    *
    * @return void
    */
   public static function paymentMethodDelete()
   {
      static::updateRemovedPaymentMethod();

      $user = Auth::getUser();

      $sql = "DELETE FROM payment_methods_assigned_to_users
             WHERE user_id = :user_id
             AND
             name = :payment_method";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':payment_method', $_POST["payment_methods"], PDO::PARAM_STR);

      $stmt->execute();
   }

   /**
    * Change a deleted payment method to an existing one
    *
    * @return void
    */
   public static function updateRemovedPaymentMethod()
   {
      $user = Auth::getUser();

      $sql = "UPDATE expenses
              SET payment_method_assigned_to_user_id = (SELECT id FROM payment_methods_assigned_to_users WHERE user_id = :user_id 
              AND name != :removed_payment_method LIMIT 1)
              WHERE user_id = :user_id
              AND payment_method_assigned_to_user_id = (SELECT id FROM payment_methods_assigned_to_users WHERE user_id = :user_id 
              AND name = :removed_payment_method)";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':removed_payment_method', $_POST["payment_methods"], PDO::PARAM_STR);

      $stmt->execute();
   }

   /**
    * Delete expenses, categories and payment methods for the selected user
    *
    * @return void
    */
   public static function deleteAccount()
   {
      $user = Auth::getUser();

      $sql = "DELETE FROM `expenses_category_assigned_to_users` WHERE user_id = :user_id;
      DELETE FROM `payment_methods_assigned_to_users` WHERE user_id = :user_id;
      DELETE FROM `expenses` WHERE user_id = :user_id;";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);

      $stmt->execute();
   }
}
