<?php

namespace App\Controllers;

use App\Auth;
use \Core\View;
use App\Models\User;
use App\Flash;
use App\Models\UserExpense;
use App\Models\UserIncome;

/**
 * Setting controller
 *
 * PHP version 7.0
 */
class Settings extends Authenticated
{

    /**
     * Show the setting page
     *
     * @return void
     */
    public function settingsAction()
    {
        View::renderTemplate('Settings/settings.html', []);
    }

    /**
     * View the edit page for user data
     *
     * @return void
     */
    public function accountAction()
    {
        View::renderTemplate('Settings/editAccount.html', [
            'user' => Auth::getUser()
        ]);
    }

    /**
     * Update user account details
     *
     * @return void
     */
    public function updateAction()
    {
        $user = new User($_POST);

        if ($_POST["password"] == "" && $user->update()) {

            Flash::addMessage('The data has been successfully updated');
            View::renderTemplate('Settings/settings.html', [
                'user' => $user
            ]);
        } else if ($_POST["password"] != "" && $user->updatePassword()) {
            Flash::addMessage('The data has been successfully updated');
            View::renderTemplate('Settings/settings.html', [
                'user' => $user
            ]);
        } else {
            Flash::addMessage('Data update failed', Flash::WARNING);
            View::renderTemplate('Settings/editAccount.html', [
                'user' => $user
            ]);
        }
    }

    /**
     * View the add category page for income
     *
     * @return void
     */
    public function incomeAddAction()
    {
        View::renderTemplate('Settings/incomeAdd.html', []);
    }

    /**
     * Save a new category of income
     *
     * @return void
     */
    public function incomeSaveAction()
    {
        $new_category = ucfirst(strtolower($_POST["new_category"]));
        $income = new UserIncome();

        if ($income->categoryValidate($new_category)) {
            $income->incomeAddCategory($new_category);
            Flash::addMessage('Category added');
            View::renderTemplate('Settings/settings.html', []);
        } else {
            Flash::addMessage('Category not added', Flash::WARNING);
            View::renderTemplate('Settings/incomeAdd.html', [
                'user' => $income,
                'category' => $_POST["new_category"]
            ]);
        }
    }

    /**
     * View the income category renaming page
     *
     * @return void
     */
    public function incomeRenameAction()
    {
        View::renderTemplate('Settings/renameIncome.html', [
            'category' => UserIncome::incomeCategory()
        ]);
    }

    /**
     * Save the new income category name
     *
     * @return void
     */
    public function incomeRenameSaveAction()
    {
        $new_category = ucfirst(strtolower($_POST["new_category"]));
        $income = new UserIncome();

        if ($income->categoryValidate($new_category)) {
            $income->incomeRename($new_category);
            Flash::addMessage('Category renamed');
            View::renderTemplate('Settings/settings.html', []);
        } else {
            Flash::addMessage('The name of the category has not been changed', Flash::WARNING);
            View::renderTemplate('Settings/renameIncome.html', [
                'user' => $income,
                'selected_category' => $_POST["income_name"],
                'new_category' => $_POST["new_category"],
                'category' => UserIncome::incomeCategory()
            ]);
        }
    }

    /**
     * View page to remove income categories
     *
     * @return void
     */
    public function incomeDeleteAction()
    {
        View::renderTemplate('Settings/deleteIncome.html', [
            'category' => UserIncome::incomeCategory()
        ]);
    }

    /**
     * Deleting the selected income category
     *
     * @return void
     */
    public function removeIncomeCategoryAction()
    {

        if (count(UserIncome::incomeCategory()) > 1) {
            UserIncome::categoryDelete();
            Flash::addMessage('Category deleted');
            View::renderTemplate('Settings/settings.html', [
                'category' => UserIncome::incomeCategory()
            ]);
        } else {
            Flash::addMessage('At least one category must remain!', Flash::INFO);
            View::renderTemplate('Settings/deleteIncome.html', [
                'category' => UserIncome::incomeCategory()
            ]);
        }
    }

    /**
     * View the add category page for expense
     *
     * @return void
     */
    public function expenseAddAction()
    {
        View::renderTemplate('Settings/expenseAdd.html', []);
    }

    /**
     * Save a new category of expense
     *
     * @return void
     */
    public function expenseSaveAction()
    {
        $new_category = ucfirst(strtolower($_POST["new_category"]));

        $expense = new UserExpense();

        if ($expense->categoryValidate($new_category)) {
            $expense->expenseAddCategory($new_category);
            Flash::addMessage('Category added');
            View::renderTemplate('Settings/settings.html', []);
        } else {
            Flash::addMessage('Category not added', Flash::WARNING);
            View::renderTemplate('Settings/expenseAdd.html', [
                'user' => $expense,
                'category' => $_POST["new_category"]
            ]);
        }
    }

    /**
     * View the expense category renaming page
     *
     * @return void
     */
    public function expenseRenameAction()
    {
        View::renderTemplate('Settings/renameExpense.html', [
            'category' => UserExpense::expenseCategory()
        ]);
    }

    /**
     * Save the new expense category name
     *
     * @return void
     */
    public function expenseRenameSaveAction()
    {
        $new_category = ucfirst(strtolower($_POST["new_category"]));
        $expense = new UserExpense();

        if ($expense->categoryValidate($new_category)) {
            $expense->expenseRename($new_category);
            Flash::addMessage('Category renamed');
            View::renderTemplate('Settings/settings.html', []);
        } else {
            Flash::addMessage('The name of the category has not been changed', Flash::WARNING);
            View::renderTemplate('Settings/renameExpense.html', [
                'user' => $expense,
                'selected_category' => $_POST["expense_name"],
                'new_category' => $_POST["new_category"],
                'category' => UserExpense::expenseCategory()
            ]);
        }
    }

    /**
     * View page to remove expense categories
     *
     * @return void
     */
    public function expenseDeleteAction()
    {
        View::renderTemplate('Settings/deleteExpense.html', [
            'category' => UserExpense::expenseCategory()
        ]);
    }

    /**
     * Deleting the selected expense category
     *
     * @return void
     */
    public function removeExpenseCategoryAction()
    {

        if (count(UserExpense::expenseCategory()) > 1) {
            UserExpense::categoryDelete();
            Flash::addMessage('Category deleted');
            View::renderTemplate('Settings/settings.html', [
                'category' => UserExpense::expenseCategory()
            ]);
        } else {
            Flash::addMessage('At least one category must remain!', Flash::INFO);
            View::renderTemplate('Settings/deleteIncome.html', [
                'category' => UserIncome::incomeCategory()
            ]);
        }
    }

    /**
     * View the page for adding payment methods
     *
     * @return void
     */
    public function paymentMethodEditAction()
    {
        View::renderTemplate('Settings/paymentMethodAdd.html', []);
    }

    /**
     * Save new payment method
     *
     * @return void
     */
    public function paymentMethodSaveAction()
    {
        $new_payment_method = ucfirst(strtolower($_POST["new_payment_method"]));

        $expense = new UserExpense();

        if ($expense->categoryValidate($new_payment_method)) {
            $expense->addAPaymentMethod($new_payment_method);
            Flash::addMessage('Payment method added');
            View::renderTemplate('Settings/settings.html', []);
        } else {
            Flash::addMessage('Payment method not added', Flash::WARNING);
            View::renderTemplate('Settings/paymentMethodAdd.html', [
                'user' => $expense,
                'category' => $_POST["new_payment_method"]
            ]);
        }
    }

    /**
     * View the payment method name change page
     *
     * @return void
     */
    public function paymentMethodRenameAction()
    {
        View::renderTemplate('Settings/paymentMethodRename.html', [
            'payment_methods' => UserExpense::paymentMethods()
        ]);
    }

    /**
     * Save the name of the new payment method
     *
     * @return void
     */
    public function paymentMethodRenameSaveAction()
    {
        $new_payment_method = ucfirst(strtolower($_POST["new_payment_method"]));
        $expense = new UserExpense();

        if ($expense->categoryValidate($new_payment_method)) {
            $expense->paymentMethodRename($new_payment_method);
            Flash::addMessage('Payment method renamed');
            View::renderTemplate('Settings/settings.html', []);
        } else {
            Flash::addMessage('The name of the payment method has not been changed', Flash::WARNING);
            View::renderTemplate('Settings/paymentMethodRename.html', [
                'user' => $expense,
                'selected_payment_methods' => $_POST["payment_methods"],
                'new_payment_method' => $_POST["new_payment_method"],
                'payment_methods' => UserExpense::paymentMethods()
            ]);
        }
    }

    /**
     * View the page to remove the payment method
     *
     * @return void
     */
    public function paymentMethodDeleteAction()
    {
        View::renderTemplate('Settings/deletePaymentMethod.html', [
            'payment_methods' => UserExpense::paymentMethods()
        ]);
    }

    /**
     * Deleting the selected payment method
     *
     * @return void
     */
    public function removePaymentMethodAction()
    {

        if (count(UserExpense::paymentMethods()) > 1) {
            UserExpense::paymentMethodDelete();
            Flash::addMessage('Payment method deleted');
            View::renderTemplate('Settings/settings.html', [
                'payment_methods' => UserExpense::paymentMethods()
            ]);
        } else {
            Flash::addMessage('At least one payment method must remain!', Flash::INFO);
            View::renderTemplate('Settings/deletePaymentMethod.html', [
                'payment_methods' => UserExpense::paymentMethods()
            ]);
        }
    }

    /**
     * View the page to delete the account
     *
     * @return void
     */
    public function deleteAccountInfoAction()
    {

        View::renderTemplate('Settings/deleteAccount.html');
    }

    /**
     * Deleting a user account
     *
     * @return void
     */
    public function deleteAccountAction()
    {
        UserExpense::deleteAccount();
        UserIncome::deleteAccount();
        User::deleteAccount();

        Auth::logout();
        Flash::addMessage('The account has been deleted');
        View::renderTemplate('Login/new.html');
    }
}
