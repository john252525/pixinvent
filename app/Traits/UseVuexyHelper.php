<?php

namespace App\Traits;

trait UseVuexyHelper
{
    public function getUserData(string $token): array
    {
        return [
            'userAbilityRules' => $this->getAbilities(),
            'accessToken' => $token,
            'userData' => [
                'id' => $this->id,
                'fullName' => $this->email,
                'name' => $this->name,
                'avatar' => '/images/avatars/avatar-1.png',
                'email' => $this->email,
                'role' => $this->roles[0]->name === 'all' ? 'sadmin' : $this->roles[0]->name,
            ],
        ];
    }

    public function getAbilities($role = null): array
    {
        $roles = $this->roles;
        $abilities = [];
        foreach ($roles as $role) {
            $permissions = $role->permissions;
            foreach ($permissions as $permission) {
                $abilities[] = [
                    'subject' => /*$role->name === 'admin'? 'all' : */ $role->name,
                    'action' => $permission->name,
                ];
            }
        }

        return $abilities; //TODO: add more abilities
    }
}
