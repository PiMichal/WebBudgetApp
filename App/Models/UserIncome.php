<?php

namespace App\Models;

use App\Auth;
use PDO;

/**
 * Income model
 *
 * PHP version 7.0
 */

class UserIncome extends \Core\Model
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
    * Start date
    * @var string
    */
    public $start_date;

    /**
     * End date
     * @var string
     */
    public $end_date;
    
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
      if ($this->amount == '') {
         $this->errors[] = 'Amount is required';
      }

      if ($this->date_of_income == '') {
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

   /**
    * Saving the entered incomes in the database
    *
    * @return array
    */
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

   /**
    * Display of user incomes over a specified period
    * 
    * @return array
    */
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

   /**
    * View detailed user incomes over a specified period
    * 
    * @return array
    */
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

   /**
    * Calculation of the sum of the user's incomes in a given period
    * 
    * @return string
    */
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

   /**
    * Incomes update
    * 
    * @return void
    */
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

   /**
    * 
    * Display of income categories
    * 
    * @return array
    */
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

   /**
    * Save a new category of income
    * 
    * @return void
    */
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

   /**
    * Save the new income category name
    *
    * @return void
    */
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

   /**
    * Deleting the selected income category
    *
    * @return void
    */
   public static function categoryDelete()
   {
      static::updateTheDeletedCategory();

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

   /**
    * Change a deleted category to an existing one
    *
    * @return void
    */
   public static function updateTheDeletedCategory()
   {
      $user = Auth::getUser();

      $sql = "UPDATE incomes
              SET income_category_assigned_to_user_id = (SELECT id FROM incomes_category_assigned_to_users WHERE user_id = :user_id 
              AND name != :removed_category LIMIT 1)
              WHERE user_id = :user_id
              AND income_category_assigned_to_user_id = (SELECT id FROM incomes_category_assigned_to_users WHERE user_id = :user_id 
              AND name = :removed_category)";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
      $stmt->bindValue(':removed_category', $_POST["income_name"], PDO::PARAM_STR);

      $stmt->execute();
   }

   /**
    * Remove of incomes
    *
    * @return void
    */
   public static function incomeDelete()
   {
      $sql = "DELETE FROM incomes
             WHERE id = :id";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':id', $_POST["delete"], PDO::PARAM_INT);

      $stmt->execute();
   }

   /**
    * Delete incomes and categories for the selected user
    *
    * @return void
    */
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

        $this->date_of_income = date('Y-m-d');
    }
}
