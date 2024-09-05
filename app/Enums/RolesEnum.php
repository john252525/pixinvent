<?php

namespace App\Enums;

enum RolesEnum: string
{
    case ALL = 'all';
    case USER = 'user';
    case ADMIN = 'admin';
    case DOMAIN1 = 'domain1';
    case DOMAIN2 = 'domain2';
    case DOMAIN3 = 'domain3';

    // extra helper to allow for greater customization of displayed values, without disclosing the name/value data directly
    public function label(): string
    {
        return match ($this) {
            self::ALL => 'Супер-Администратор',
            self::ADMIN => 'Администратор',
            self::USER => 'Пользователь',
            self::DOMAIN1 => 'Пользователь первого домена',
            self::DOMAIN2 => 'Пользователь второго домена',
            self::DOMAIN3 => 'Пользователь третьего домена',
        };
    }
}
