<?php

namespace App\Traits;

use App\Http\Integrations\ExternalTokenGate\ExternalTokenConnector;
use App\Http\Integrations\ExternalTokenGate\Requests\ExternalTokenGate;

trait UseVuexyHelper
{
    public function getUserData(?string $token = null): array
    {
        if (! $this->external_token) {
            $connector = new ExternalTokenConnector;
            $request = new ExternalTokenGate;

            $response = $connector->send($request);

            if ($response->ok()) {
                $external_token = $response->json('value');
                $this->update([
                    'external_token' => $external_token,
                ]);
            } else {
                \Log::alert('External token gate failed');
            }
        }

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
