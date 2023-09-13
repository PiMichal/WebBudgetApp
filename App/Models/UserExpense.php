<?php

namespace App\Models;

use App\Auth;
use PDO;

class UserExpense extends \Core\Model
{
   public $user_id;
   public $amount;
   public $date_of_expense;
   public $expense_category_assigned_to_user_id;
   public $payment_method_assigned_to_user_id;
   public $expense_comment;

   public $start_date;
   public $end_date;

   public $errors = [];


   public function __construct($data = [])
   {
      foreach ($data as $key => $value) {
         $this->$key = $value;
      };
   }

   public function validate()
   {

      if ($this->amount == '') {
         $this->errors[] = 'Amount is required';
      }

      if ($this->date_of_expense == '') {
         $this->errors[] = 'Date is required';
      }
   }

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

   public function getExpense()
   {
      $this->dateSetting();

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
      $stmt->bindValue(':startDate', $this->start_date, PDO::PARAM_STR);
      $stmt->bindValue(':endDate', $this->end_date, PDO::PARAM_STR);

      $stmt->execute();

      return $stmt->fetchAll();
   }

   public function getAllExpense()
   {
      $this->dateSetting();

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
      $stmt->bindValue(':startDate', $this->start_date, PDO::PARAM_STR);
      $stmt->bindValue(':endDate', $this->end_date, PDO::PARAM_STR);

      $stmt->execute();

      return $stmt->fetchAll();
   }

   public function countTotalExpense()
   {
      $this->dateSetting();
      $user = Auth::getUser();

      $sql = "SELECT SUM(amount) AS expense_sum
           FROM expenses, expenses_category_assigned_to_users 
           WHERE expenses.expense_category_assigned_to_user_id = expenses_category_assigned_to_users.id
           AND expenses.user_id = :userId 
           AND expenses.date_of_expense BETWEEN :startDate AND :endDate";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':userId', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':startDate', $this->start_date, PDO::PARAM_STR);
      $stmt->bindValue(':endDate', $this->end_date, PDO::PARAM_STR);

      $stmt->execute();

      return $stmt->fetchColumn();
   }

   public static function expenseUpdate()
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
      $stmt->bindValue(':expense_category_assigned_to_user_id', $_POST["expense_category_assigned_to_user_id"], PDO::PARAM_STR);
      $stmt->bindValue(':amount', $_POST["expense_amount"], PDO::PARAM_STR);
      $stmt->bindValue(':date_of_expense', $_POST["date_of_expense"], PDO::PARAM_STR);
      $stmt->bindValue(':expense_comment', $_POST["expense_comment"], PDO::PARAM_STR);
      $stmt->bindValue(':payment_method_assigned_to_user_id', $_POST["payment_method_assigned_to_user_id"], PDO::PARAM_STR);
      $stmt->bindValue(':id', $_POST["id"], PDO::PARAM_INT);

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

   public static function categoryDelete()
   {

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

   public static function expenseDelete()
   {
      $sql = "DELETE FROM expenses
             WHERE id = :id";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':id', $_POST["delete"], PDO::PARAM_INT);

      $stmt->execute();
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

   public function dateSetting()
   {

      if (isset($_POST["start_date"]) && isset($_POST["end_date"])) {
         $this->start_date = $_POST['start_date'];
         $this->end_date = $_POST['end_date'];
      } else {
         $this->start_date = date('Y-m-01');
         $this->end_date = date('Y-m-t');
      }

      $this->date_of_expense = date('Y-m-d');
   }
}
