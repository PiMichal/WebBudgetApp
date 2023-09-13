<?php

namespace App\Models;

use App\Auth;
use PDO;

class UserIncome extends \Core\Model
{
   public $user_id;
   public $amount;
   public $date_of_income;
   public $income_category_assigned_to_user_id;
   public $income_comment;

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

      if ($this->date_of_income == '') {
         $this->errors[] = 'Date is required';
      }
   }

   public function findByCategory($category)
   {
      $user = Auth::getUser();

      $sql = 'SELECT COUNT(*) AS category
               FROM incomes_category_assigned_to_users 
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

   public function categoryValidate($category)
   {

      if (static::findByCategory($category)) {
         $this->errors[] = 'Category already exists';
         return false;
      }

      if ($category == '') {
         $this->errors[] = 'Category is required';
         return false;
      }

      if (!ctype_alpha($category)) {
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

      $sql = 'INSERT INTO incomes_category_assigned_to_users (`user_id`, `name`)
                SELECT :user_id, `name` 
                FROM incomes_category_default';

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue('user_id', $user->id, PDO::PARAM_INT);

      $stmt->execute();
   }

   public function saveIncome()
   {

      $this->validate();
      if (empty($this->errors)) {

         $user = Auth::getUser();

         $sql = 'INSERT INTO incomes (user_id, income_category_assigned_to_user_id, amount, date_of_income, income_comment)
         VALUES (:user_id, 
         (SELECT id FROM incomes_category_assigned_to_users WHERE name = :income_category_assigned_to_user_id AND user_id = :user_id), 
         :amount, 
         :date_of_income, 
         :income_comment)';

         $db = static::getDB();
         $stmt = $db->prepare($sql);

         $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
         $stmt->bindValue(':income_category_assigned_to_user_id', $this->income_category_assigned_to_user_id, PDO::PARAM_STR);
         $stmt->bindValue(':amount', $this->amount, PDO::PARAM_STR);
         $stmt->bindValue(':date_of_income', $this->date_of_income, PDO::PARAM_STR);
         $stmt->bindValue(':income_comment', $this->income_comment, PDO::PARAM_STR);

         return $stmt->execute();
      }
      return false;
   }

   public function getIncome()
   {
      $this->dateSetting();

      $user = Auth::getUser();

      $sql = "SELECT incomes.id, incomes_category_assigned_to_users.name AS income_name, SUM(amount) AS income_amount, date_of_income, income_comment  
                    FROM incomes, incomes_category_assigned_to_users 
                    WHERE incomes.user_id = :userId
                    AND incomes.income_category_assigned_to_user_id = incomes_category_assigned_to_users.id
                    AND incomes.date_of_income BETWEEN :startDate AND :endDate
                    GROUP BY income_category_assigned_to_user_id";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':userId', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':startDate', $this->start_date, PDO::PARAM_STR);
      $stmt->bindValue(':endDate', $this->end_date, PDO::PARAM_STR);

      $stmt->execute();

      return $stmt->fetchAll();
   }

   public function getAllIncome()
   {
      $this->dateSetting();

      $user = Auth::getUser();

      $sql = "SELECT incomes.id, incomes_category_assigned_to_users.name AS income_name, amount AS income_amount, date_of_income, income_comment  
              FROM incomes, incomes_category_assigned_to_users 
              WHERE incomes.user_id = :userId
              AND incomes.income_category_assigned_to_user_id = incomes_category_assigned_to_users.id
              AND incomes.date_of_income BETWEEN :startDate AND :endDate
              ORDER BY incomes.date_of_income DESC";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':userId', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':startDate', $this->start_date, PDO::PARAM_STR);
      $stmt->bindValue(':endDate', $this->end_date, PDO::PARAM_STR);

      $stmt->execute();

      return $stmt->fetchAll();
   }

   public function countTotalIncome()
   {
      $this->dateSetting();

      $user = Auth::getUser();

      $sql = "SELECT SUM(amount) AS income_sum
              FROM incomes, incomes_category_assigned_to_users
              WHERE incomes.income_category_assigned_to_user_id = incomes_category_assigned_to_users.id
              AND incomes.user_id = :userId 
              AND incomes.date_of_income BETWEEN :startDate AND :endDate";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':userId', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':startDate', $this->start_date, PDO::PARAM_STR);
      $stmt->bindValue(':endDate', $this->end_date, PDO::PARAM_STR);

      $stmt->execute();

      return $stmt->fetchColumn();
   }

   public static function incomeUpdate()
   {
      $user = Auth::getUser();

      $sql = "UPDATE incomes
              SET income_category_assigned_to_user_id = (SELECT id FROM incomes_category_assigned_to_users WHERE name = :income_category_assigned_to_user_id AND user_id = :user_id), 
              amount = :amount, 
              date_of_income = :date_of_income, 
              income_comment = :income_comment
              WHERE id = :id";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':income_category_assigned_to_user_id', $_POST["income_category_assigned_to_user_id"], PDO::PARAM_STR);
      $stmt->bindValue(':amount', $_POST["income_amount"], PDO::PARAM_STR);
      $stmt->bindValue(':date_of_income', $_POST["date_of_income"], PDO::PARAM_STR);
      $stmt->bindValue(':income_comment', $_POST["income_comment"], PDO::PARAM_STR);
      $stmt->bindValue(':id', $_POST["id"], PDO::PARAM_INT);

      $stmt->execute();
   }

   public static function incomeCategory()
   {
      $user = Auth::getUser();

      $sql = "SELECT name AS income_name
              FROM incomes_category_assigned_to_users 
              WHERE user_id = :userId";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':userId', $user->id, PDO::PARAM_INT);

      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }

   public static function incomeAddCategory($category)
   {
      $user = Auth::getUser();

      $sql = "INSERT INTO incomes_category_assigned_to_users (user_id, name)
               VALUES (:user_id, :name)";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':name', $category, PDO::PARAM_STR);

      $stmt->execute();
   }

   public static function incomeRename($new_category)
   {

      $user = Auth::getUser();

      $sql = "UPDATE incomes_category_assigned_to_users
              SET name = :new_category
              WHERE user_id = :user_id
              AND
              name = :name";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':new_category', $new_category, PDO::PARAM_STR);
      $stmt->bindValue(':name', $_POST['income_name'], PDO::PARAM_STR);

      $stmt->execute();
   }

   public static function categoryDelete()
   {
      $user = Auth::getUser();

      $sql = "DELETE FROM incomes_category_assigned_to_users
             WHERE user_id = :user_id
             AND
             name = :category";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':category', $_POST["income_name"], PDO::PARAM_STR);

      $stmt->execute();
   }

   public static function incomeDelete()
   {
      $sql = "DELETE FROM incomes
             WHERE id = :id";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':id', $_POST["delete"], PDO::PARAM_INT);

      $stmt->execute();
   }

   public static function deleteAccount()
   {
      $user = Auth::getUser();

      $sql = "DELETE FROM `incomes_category_assigned_to_users` WHERE user_id = :user_id;
      DELETE FROM `incomes` WHERE user_id = :user_id;";

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

      $this->date_of_income = date('Y-m-d');
   }
}
