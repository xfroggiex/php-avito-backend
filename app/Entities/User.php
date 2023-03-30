<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    public function setBalance(float $amount)
    {
        $this->attributes['balance'] = $amount;

        return $this;
    }

    public function getBalance()
    {
        return $this->attributes['balance'];
    }

    public function setUserId(int $user_id)
    {
        $this->attributes['user_id'] = $user_id;

        return $this;
    }

    public function getUserId()
    {
        return $this->attributes['user_id'];
    }
}