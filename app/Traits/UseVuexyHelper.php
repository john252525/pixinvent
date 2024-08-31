<?php

namespace App\Traits;

trait UseVuexyHelper
{

    public function getUserData(string $token): array
    {
        return [
            'userAbilityRules' => [
                [
                    'action' => 'manage',
                    'subject' => 'all'
                ]
            ],
            'accessToken' => $token,
            'userData' => [
                'id' => $this->id,
                'fullName' => 'Иванов Иван Иванович',
                'name' => $this->name,
                'avatar' => '/images/avatars/avatar-1.png',
                'email' => $this->email,
                'role' => 'admin',
            ]
        ];
    }
}
