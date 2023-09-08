<?php

namespace App\Controllers;

use App\Auth;
use \Core\View;
use App\Models\User;
use App\Flash;
use App\Models\UserExpense;
use App\Models\UserIncome;

/**
 * Home controller
 *
 * PHP version 7.0
 */
class Settings extends Authenticated
{

    /**
     * Show the index page
     *
     * @return void
     */
    public function settingsAction()
    {
        View::renderTemplate('Settings/settings.html', []);
    }

    public function accountAction()
    {
        View::renderTemplate('Settings/editAccount.html', [
            'user' => Auth::getUser()
        ]);
    }

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

    public function incomeAddAction()
    {
        View::renderTemplate('Settings/incomeAdd.html', []);
    }

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

    public function incomeRenameAction()
    {   
        View::renderTemplate('Settings/renameIncome.html', [
            'category' => UserIncome::incomeCategory()
        ]);
    }

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

    public function incomeDeleteAction()
    {   
        View::renderTemplate('Settings/deleteIncome.html', [
            'category' => UserIncome::incomeCategory()
        ]);
    }

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

    public function expenseAddAction()
    {
        View::renderTemplate('Settings/expenseAdd.html', []);
    }

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

    public function expenseRenameAction()
    {   
        View::renderTemplate('Settings/renameExpense.html', [
            'category' => UserExpense::expenseCategory()
        ]);
    }

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

    public function expenseDeleteAction()
    {   
        View::renderTemplate('Settings/deleteExpense.html', [
            'category' => UserExpense::expenseCategory()
        ]);
    }

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

    public function paymentMethodEditAction()
    {
        View::renderTemplate('Settings/paymentMethodAdd.html', []);
    }

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

    public function paymentMethodRenameAction()
    {   
        View::renderTemplate('Settings/paymentMethodRename.html', [
            'payment_methods' => UserExpense::paymentMethods()
        ]);
    }

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

    public function paymentMethodDeleteAction()
    {   
        View::renderTemplate('Settings/deletePaymentMethod.html', [
            'payment_methods' => UserExpense::paymentMethods()
        ]);
    }

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

    
}
