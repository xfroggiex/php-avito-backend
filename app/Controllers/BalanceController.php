<?php

namespace App\Controllers;

use App\Entities\User;
use App\Models\UsersModel;
use CodeIgniter\API\ResponseTrait;
use InvalidArgumentException;

class BalanceController extends BaseController
{
    use ResponseTrait;

    private UsersModel $usersModel;

    public function __construct()
    {
        $this->usersModel = new UsersModel();
    }

    public function validateAmount(float $amount): bool
    {
        if ($amount <= 0) {
            return false;
        }
        return true;
    }

    public function validateUser($user): bool
    {
        if ($user === null) {
            return false;
        }
        return true;
    }

    public function getBalance(int $userId = 0)
    {
        $user = $this->usersModel->find($userId);

        if (!$this->validateUser($user)) {
            return $this->respond(['error' => 'User does not exist.'], 404);
        }

        return $this->respond($user);
    }

    public function increaseBalance(int $userId = 0, float $amount = 0)
    {
        if (!$this->validateAmount($amount)) {
            return $this->respond(['error' => 'Invalid amount.'], 422);
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

        if (!$this->validateAmount($amount)) {
            return $this->respond(['error' => 'Invalid amount.'], 422);
        } elseif(!$this->validateUser($user)) {
            return $this->respond(['error' => 'User does not exist.'], 404);
        }

        if ($amount > $user->getBalance()) {
            return $this->respond(['error' => 'Insufficient funds.'], 422);
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
            return $this->respond(['error' => 'Sender user does not exist.'], 404);
        }

        if ($recipient === null) {
            return $this->respond(['error' => 'Recipient user does not exist.'], 404);
        }

        if (!$this->validateAmount($amount)) {
            return $this->respond(['error' => 'Invalid amount.'], 422);
        }

        if ($amount > $sender->getBalance()) {
            return $this->respond(['error' => 'Insufficient funds.'], 422);
        }

        $senderUpdatedBalance = $sender->getBalance() - $amount;
        $recipientUpdatedBalance = $recipient->getBalance() + $amount;

        $sender->setBalance($senderUpdatedBalance);
        $this->usersModel->save($sender);

        $recipient->setBalance($recipientUpdatedBalance);
        $this->usersModel->save($recipient);

        return $this->respond([$this->usersModel->find($senderId), $this->usersModel->find($recipientId)]);
    }
}