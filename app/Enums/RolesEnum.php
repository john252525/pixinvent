<?php

namespace App\Enums;

enum RolesEnum: string
{
    case USER = 'user';
    case PREMIUM = 'premium';
    case ADMIN = 'admin';
    case ALL = 'all';

    // extra helper to allow for greater customization of displayed values, without disclosing the name/value data directly
    public function label(): string
    {
        return match ($this) {
            static::USER => 'Пользователь',
            static::PREMIUM => 'Премиум пользователь',
            static::ADMIN => 'Администратор',
            static::ALL => 'Супер-Администратор',
        };
    }
}
