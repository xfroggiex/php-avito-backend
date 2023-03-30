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

    public function transferBalance(int $sender_id = 0, int $recipient_id = 0, float $amount = 0)
    {
        $errors = [];

        $sender = $this->usersModel->find($sender_id);
        $recipient = $this->usersModel->find($recipient_id);

        if (!isset($sender)) {
            $errors[] = "Sender user does not exist.";
            return json_encode($errors);
        } elseif (!isset($recipient)) {
            $errors[] = "Recipient user does not exist.";
            return json_encode($errors);
        }

        if ($amount <= 0) {
            $errors[] = "Invalid amount.";
            return json_encode($errors);
        }

        if ($amount > $sender->getBalance()) {
            $errors[] = "Insufficient funds.";
            return json_encode($errors);
        }

        $senderUpdatedBalance = $sender->getBalance() - $amount;
        $recipientUpdatedBalance = $recipient->getBalance() + $amount;
        $sender->setBalance($senderUpdatedBalance);
        $this->usersModel->save($sender);
        $recipient->setBalance($recipientUpdatedBalance);
        $this->usersModel->save($recipient);

        $users = $this->usersModel->find([$sender_id, $recipient_id]);
        return json_encode($users);
    }
}