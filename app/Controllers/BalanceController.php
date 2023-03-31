<?php

namespace App\Controllers;

use App\Entities\User;
use App\Models\UsersModel;
use CodeIgniter\API\ResponseTrait;
use http\Exception\InvalidArgumentException;

class BalanceController extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        $this->usersModel = new UsersModel();
    }

    public function validateAmount(float $amount): bool
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Invalid amount.');
        }
        return true;
    }

    public function validateUser($user): bool
    {
        if ($user === null) {
            throw new InvalidArgumentException('User does not exist.');
        }
        return true;
    }

    public function getBalance(int $userId = 0)
    {
        $user = $this->usersModel->find($userId);

        try {
            $this->validateUser($user);
        } catch (InvalidArgumentException $e) {
            echo $e->getMessage();
        }

        return $this->respond($user);
    }

    public function increaseBalance(int $userId = 0, float $amount = 0)
    {
        try {
            $this->validateAmount($amount);
        } catch (InvalidArgumentException $e) {
            echo $e->getMessage();
        }

        $user = $this->usersModel->find($userId);

        if (!$this->validateUser($user)) {
            $user = new User();
            $user->setUserId($userId);
            $user->setBalance($amount);
            $this->usersModel->save($user);
        } else {
            $updatedBalance = $user->getBalance() + $amount;
            $user->setBalance($updatedBalance);
            $this->usersModel->save($user);
        }

        return $this->respond($user);
    }

    public function decreaseBalance(int $userId = 0, float $amount = 0)
    {
        $user = $this->usersModel->find($userId);

        try {
            $this->validateUser($user);
            $this->validateAmount($amount);
        } catch (InvalidArgumentException $e) {
            echo $e->getMessage();
        }

        if ($amount > $user->getBalance()) {
            return $this->failValidationErrors('Insufficient funds.');
        }

        $updatedBalance = $user->getBalance() - $amount;
        $user->setBalance($updatedBalance);
        $this->usersModel->save($user);

        return $this->respond($user);
    }

    public function transferBalance(int $senderId = 0, int $recipientId = 0, float $amount = 0)
    {
        $sender = $this->usersModel->find($senderId);
        $recipient = $this->usersModel->find($recipientId);

        if ($sender === null) {
            return $this->failNotFound('Sender user does not exist.');
        }

        if ($recipient === null) {
            return $this->failNotFound('Recipient user does not exist.');
        }

        try {
            $this->validateAmount($amount);
        } catch (InvalidArgumentException $e) {
            echo $e->getMessage();
        }

        if ($amount > $sender->getBalance()) {
            return $this->failValidationErrors('Insufficient funds.');
        }

        $senderUpdatedBalance = $sender->getBalance() - $amount;
        $recipientUpdatedBalance = $recipient->getBalance() + $amount;

        $sender->setBalance($senderUpdatedBalance);
        $this->usersModel->save($sender);

        $recipient->setBalance($recipientUpdatedBalance);
        $this->usersModel->save($recipient);

        return $this->respond([$sender, $recipient]);
    }
}