<?php

namespace App\Traits;


trait UseVuexyHelper
{
    public function getUserData(?string $token = null): array
    {
        return [
            'userAbilityRules' => $this->getAbilities(),
            'accessToken' => $token ?? request()->bearerToken(),
            'userData' => [
                'id' => $this->id,
                'fullName' => $this->email,
                'name' => $this->name,
                'avatar' => '/images/avatars/avatar-1.png',
                'email' => $this->email,
                'role' => $this->roles[0]->name === 'all' ? 'sadmin' : $this->roles[0]->name,
                'balance' => $this->balance,
            ],
        ];
    }

    public function getAbilities($role = null): array
    {
        $roles = $this->roles;
        $abilities = [];

        $role = match (request()->getHost()) {
            env('DOMAIN_APP1') => 'domain1',
            env('DOMAIN_APP2') => 'domain2',
            env('DOMAIN_APP3') => 'domain3',
        };

        $abilities[] = [
            'subject' => $role,
            'action' => 'read',
        ];

        foreach ($roles as $role) {
            $permissions = $role->permissions;
            foreach ($permissions as $permission) {
                $abilities[] = [
                    'subject' => $role->name,
                    'action' => $permission->name,
                ];
            }
        }

        return $abilities; //TODO: add more abilities
    }
}
