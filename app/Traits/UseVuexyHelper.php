<?php

namespace App\Traits;

use App\Http\Integrations\ExternalTokenGate\ExternalTokenConnector;
use App\Http\Integrations\ExternalTokenGate\Requests\ExternalTokenGate;
use App\Models\LogErrors;

trait UseVuexyHelper
{
    public function getUserData(?string $token = null): array
    {
        if (! $this->external_token) {
            $connector = new ExternalTokenConnector;
            $request = new ExternalTokenGate(user_id: $this->id);

            try {
                $response = $connector->send($request);

                if ($response->ok()) {
                    $external_token = $response->json('value');
                    $this->update([
                        'external_token' => $external_token,
                    ]);
                } else {
                    $this->logErrors()->create([
                        'type' => 'get_external_token_response',
                        'errors' => $response->json('message'),
                    ]);
                }
            } catch (\Exception $e) {
                $this->logErrors()->create([
                    'type' => 'get_external_token_error',
                    'errors' => $e->getMessage(),
                ]);
            }
        }

        $userAbilityRules = $this->getAbilities();

        $subjects = array_column($userAbilityRules, 'subject');

        $notifications = [];

        if (! $this->email_verified_at) {
            $notifications[] = [
                'id' => 1,
                'icon' => 'mdi:email-check-outline',
                'title' => 'Требуется подтверждение почты',
                'subtitle' => 'Нажмите на это сообщение что бы подтвердить почту',
                'time' => $this->created_at->calendar(),
                'isSeen' => false,
                'to' => ['name' => 'verification-email'],
            ];
        }

        $role = match (true) {
            in_array('all', $subjects) => 'sadmin',
            in_array('admin', $subjects) => 'admin',
            in_array('accounts', $subjects) => 'accounts',
            in_array('settings', $subjects) => 'settings',
            default => 'user',
        };

        return [
            'userAbilityRules' => $userAbilityRules,
            'accessToken' => $token ?? request()->bearerToken(),
            'userData' => [
                'id' => $this->id,
                'fullName' => $this->email,
                'name' => $this->name,
                'avatar' => '/images/avatars/avatar-1.png',
                'email' => $this->email,
                'role' => $role,
                'balance' => $this->balance,
            ],
            'settings' => [
                'email_verified_at' => $this->email_verified_at,
            ],
            'notifications' => $notifications,
        ];
    }

    public function getAbilities($role = null): array
    {
        $roles = $this->roles;
        $abilities = [];

        $host = request()->getHost();

        $dynamicRoles = match ($host) {
            config('app.domains.settings') => ['settings'],
            config('app.domains.accounts') => ['accounts'],
            config('app.domains.binder') => ['accounts', 'binder'],
            config('app.domains.reserved') => ['reserved'],
            default => [],
        };
        if (! count($dynamicRoles)) {
            LogErrors::create([
                'type' => 'host_not_found',
                'user_id' => $this->id,
                'errors' => [request()->getHost(), request()->host()],
            ]);
        }

        foreach ($dynamicRoles as $dynamicRole) {
            $abilities[] = [
                'subject' => $dynamicRole,
                'action' => 'read',
            ];
        }

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
