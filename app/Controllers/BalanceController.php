<?php

namespace App\Controllers;

use App\Models\UsersModel;

class BalanceController extends BaseController
{
    public function __construct()
    {
        $this->usersModel = new UsersModel();
    }

    public function getBalance(int $user_id = 0)
    {
        $errors = [];
        $user = $this->usersModel->find($user_id);
        if (!isset($user)) {
            $errors[] = "User does not exist.";
            return json_encode($errors);
        } else {
            $user = json_encode($user);
            return $user;
        }
    }

    public function increaseBalance(int $user_id = 0, float $amount = 0)
    {
        $errors = [];
        if ($amount <= 0) {
            $errors[] = "Invalid amount.";
            return json_encode($errors);
        }
        $user = $this->usersModel->find($user_id);
        if (!isset($user)) {
            $user = new \App\Entities\User();
            $user->setUserId($user_id);
            $user->setBalance($amount);
            $this->usersModel->save($user);
        } else {
            $updatedBalance = $user->getBalance() + $amount;
            $user->setBalance($updatedBalance);
            $this->usersModel->save($user);
        }

        $user = $this->usersModel->find($user_id);
        return json_encode($user);
    }

    public function decreaseBalance(int $user_id = 0, float $amount = 0)
    {
        $errors = [];
        $user = $this->usersModel->find($user_id);
        if (!isset($user)) {
            $errors[] = "User does not exist.";
            return json_encode($errors);
        }
        if ($amount <= 0) {
            $errors[] = "Invalid amount.";
            return json_encode($errors);
        }
        if ($amount > $user->getBalance()) {
            $errors[] = "Insufficient funds.";
            return json_encode($errors);
        }
        $updatedBalance = $user->getBalance() - $amount;
        $user->setBalance($updatedBalance);
        $this->usersModel->save($user);

        $user = $this->usersModel->find($user_id);
        return json_encode($user);
    }
}