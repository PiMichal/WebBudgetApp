<?php

namespace App\Models;

use App\Mail;
use PDO;
use \App\Token;
use Core\View;
use App\Auth;

class User extends \Core\Model
{
    public $id;
    public $username;
    public $email;
    public $password;
    public $password_confirmation;

    public $remember_token;

    public $password_reset_hash;
    public $password_reset_expires_at;
    public $password_reset_token;
    public $expiry_timestamp;

    public $activation_hash;
    public $is_active;

    public $errors = [];

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        };
    }

    public function save()
    {

        $this->validate();
        $this->validatePassword();
        if (empty($this->errors)) {

            $password = password_hash($this->password, PASSWORD_DEFAULT);

            $token = new Token();
            $hashed_token = $token->getHash();
            $this->activation_hash = $token->getValue();

            $sql = 'INSERT INTO users (username, password, email, activation_hash)
            VALUES (:username, :password, :email, :activation_hash)';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue('username', $this->username, PDO::PARAM_STR);
            $stmt->bindValue('password', $password, PDO::PARAM_STR);
            $stmt->bindValue('email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue('activation_hash', $hashed_token, PDO::PARAM_STR);

            return $stmt->execute();
        }
        return false;
    }

    public function validate()
    {
        if ($this->username == '') {
            $this->errors[] = 'Name is required';
        }

        // email address
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[] = 'Invalid email';
        }
        if (static::emailExists($this->email, $this->id ?? null)) {
            $this->errors[] = 'email already taken';
        }
    }

    public function validatePassword()
    {
        // Password
        if ($this->password != $this->password_confirmation) {
            $this->errors[] = 'Password must match confirmation';
        }

        if (strlen($this->password) < 6) {
            $this->errors[] = 'Please enter at least 6 characters for the password';
        }

        if (preg_match('/.*[a-z]+.*/i', $this->password) == 0) {
            $this->errors[] = 'Password needs at least one letter';
        }

        if (preg_match('/.*\d+.*/i', $this->password) == 0) {
            $this->errors[] = 'Password needs at least one number';
        }
    }

    public static function emailExists($email, $ignore_id = null)
    {
        $user = static::findByEmail($email);

        if ($user) {
            if ($user->id != $ignore_id) {
                return true;
            }
        }

        return false;
    }

    public static function findByEmail($email)
    {

        $sql = 'SELECT * FROM users WHERE email = :email';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public static function authenticate($email, $password)
    {
        $user = static::findByEmail($email);

        if ($user && $user->is_active) {
            if (password_verify($password, $user->password)) {
                return $user;
            }
        }

        return false;
    }

    public static function findByID($id)
    {
        $sql = 'SELECT * FROM users WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public function rememberLogin()
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->remember_token = $token->getValue();

        $this->expiry_timestamp = time() + 60 * 60 * 24 * 30;

        $sql = 'INSERT INTO remembered_logins (token_hash, user_id, expires_at)
        VALUES (:token_hash, :user_id, :expires_at)';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expiry_timestamp), PDO::PARAM_STR);

        return $stmt->execute();
    }

    public static function sendPasswordReset($email)
    {

        $username = static::findByEmail($email);


        if ($username) {

            if ($username->startPasswordReset()) {

                $username->sendPasswordResetEmail();
            }
        }
    }

    protected function startPasswordReset()
    {
        $token = new Token();
        $hashed_token = $token->getHash();

        $this->password_reset_token = $token->getValue();

        $expiry_timestamp = time() + 60 * 60 * 2;

        $sql = 'UPDATE users
                SET password_reset_hash = :token_hash,
                    password_reset_expires_at = :expires_at
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiry_timestamp), PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    protected function sendPasswordResetEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token;

        $text = View::getTemplate('Password/reset_email.html', ['url' => $url]);

        Mail::send($this->email, 'Password reset', $text);
    }

    public static function findByPasswordReset($token)
    {
        $token = new Token($token);
        $hashed_token = $token->getHash();

        $sql = 'SELECT * FROM users
                WHERE password_reset_hash = :token_hash';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        $user = $stmt->fetch();

        if ($user) {

            if (strtotime($user->password_reset_expires_at) > time()) {

                return $user;
            }
        }
    }

    public function resetPassword($password, $password_confirmation)
    {

        $this->password = $password;
        $this->password_confirmation = $password_confirmation;

        $this->validate();
        $this->validatePassword();


        if (empty($this->errors)) {

            $password = password_hash($this->password, PASSWORD_DEFAULT);

            $sql = 'UPDATE users
                    SET password = :password,
                        password_reset_hash = NULL,
                        password_reset_expires_at = NULL
                    WHERE id = :id';

            $db = static::getDB();
            $stmt = $db->prepare($sql);



            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindValue(':password', $password, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    public function sendActivationEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->activation_hash;

        $text = View::getTemplate('Signup/activation_email.html', ['url' => $url]);

        Mail::send($this->email, 'Account activation', $text);
    }

    public static function activate($value)
    {
        $token = new Token($value);
        $hashed_token = $token->getHash();

        $sql = 'UPDATE users
                SET is_active = 1,
                    activation_hash = null
                WHERE activation_hash = :hashed_token';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);

        $stmt->execute();
    }

    public function update()
    {
        $this->id = $_SESSION['user_id'];
        $this->validate();

        if (empty($this->errors) && $this->password == "") {

            $sql = 'UPDATE users
                    SET username = :username,
                    email = :email
                    WHERE id = :id';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':username', $this->username, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

            return $stmt->execute();
        }
        return false;
    }

    public function updatePassword()
    {
        $this->id = $_SESSION['user_id'];
        $this->password_confirmation = $this->password;
        $this->validate();
        $this->validatePassword();

        if (empty($this->errors)) {

            $password = password_hash($this->password, PASSWORD_DEFAULT);

            $sql = 'UPDATE users
                    SET username = :username,
                    email = :email,
                    password = :password
                    WHERE id = :id';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':username', $this->username, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue('password', $password, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

            return $stmt->execute();
        }
        return false;
    }

    public static function deleteAccount()
    {
        $user = Auth::getUser();

        $sql = "DELETE FROM `users` WHERE id = :id";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);

        $stmt->execute();
    }
}
