<?php

namespace App\Controllers;

use App\Auth;
use \Core\View;
use App\Models\User;
use App\Flash;
use App\Models\UserExpense;
use App\Models\UserIncome;

class Settings extends Authenticated
{
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

        if (empty(UserIncome::categoryValidate($new_category))) {

            UserIncome::incomeAddCategory($new_category);
            Flash::addMessage('Category added');
            View::renderTemplate('Settings/settings.html', []);
        } else {

            Flash::addMessage('Category not added', Flash::WARNING);
            View::renderTemplate('Settings/incomeAdd.html', [
                'errors' => UserIncome::categoryValidate($new_category),
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

        if (empty(UserIncome::categoryValidate($new_category))) {
            UserIncome::incomeRename($new_category);
            Flash::addMessage('Category renamed');
            View::renderTemplate('Settings/settings.html', []);
        } else {
            Flash::addMessage('The name of the category has not been changed', Flash::WARNING);
            View::renderTemplate('Settings/renameIncome.html', [
                'errors' => UserIncome::categoryValidate($new_category),
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

    public function incomeDeleteInfoAction()
    {
        View::renderTemplate('Settings/deleteIncomeInfo.html', [
            'category' => $_POST["income_name"]
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

        if (empty(UserExpense::categoryValidate($new_category))) {
            UserExpense::expenseAddCategory($new_category);
            Flash::addMessage('Category added');
            View::renderTemplate('Settings/settings.html', []);
        } else {
            Flash::addMessage('Category not added', Flash::WARNING);
            View::renderTemplate('Settings/expenseAdd.html', [
                'errors' => UserExpense::categoryValidate($new_category),
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

        if (empty(UserExpense::categoryValidate($new_category))) {
            UserExpense::expenseRename($new_category);
            Flash::addMessage('Category renamed');
            View::renderTemplate('Settings/settings.html', []);
        } else {
            Flash::addMessage('The name of the category has not been changed', Flash::WARNING);
            View::renderTemplate('Settings/renameExpense.html', [
                'errors' => UserExpense::categoryValidate($new_category),
                'selected_category' => $_POST["expense_name"],
                'new_category' => $_POST["new_category"],
                'category' => UserExpense::expenseCategory()
            ]);
        }
    }

    public function limitCategoryAction()
    {
        View::renderTemplate('Settings/limitCategory.html', [
            'category' => UserExpense::expenseCategory()
        ]);
    }

    public function setLimitCategoryAction()
    {
        UserExpense::expenseSetLimit();

        Flash::addMessage("The limit for the category '$_POST[category]' has been set");
        View::renderTemplate('Settings/settings.html', [
        ]);
    }

    public function expenseDeleteAction()
    {
        View::renderTemplate('Settings/deleteExpense.html', [
            'category' => UserExpense::expenseCategory()
        ]);
    }

    public function expenseDeleteInfoAction()
    {
        View::renderTemplate('Settings/deleteExpenseInfo.html', [
            'category' => $_POST["expense_name"]
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
            View::renderTemplate('Settings/deleteExpenses.html', [
                'category' => UserExpense::expenseCategory()
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

        if (empty(UserExpense::categoryValidate($new_payment_method))) {
            UserExpense::addAPaymentMethod($new_payment_method);
            Flash::addMessage('Payment method added');
            View::renderTemplate('Settings/settings.html', []);
        } else {
            Flash::addMessage('Payment method not added', Flash::WARNING);
            View::renderTemplate('Settings/paymentMethodAdd.html', [
                'errors' => UserExpense::categoryValidate($new_payment_method),
                'payment_methods' => $_POST["new_payment_method"]
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

        if (empty(UserExpense::categoryValidate($new_payment_method))) {
            UserExpense::paymentMethodRename($new_payment_method);
            Flash::addMessage('Payment method renamed');
            View::renderTemplate('Settings/settings.html', []);
        } else {
            Flash::addMessage('The name of the payment method has not been changed', Flash::WARNING);
            View::renderTemplate('Settings/paymentMethodRename.html', [
                'errors' => UserExpense::categoryValidate($new_payment_method),
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

    public function removePaymentMethodInfoAction()
    {
        View::renderTemplate('Settings/paymentMethodDeleteInfo.html', [
            'payment_method' => $_POST["payment_methods"]
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

    public function deleteAccountInfoAction()
    {
        View::renderTemplate('Settings/deleteAccount.html');
    }

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
