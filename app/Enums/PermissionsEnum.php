<?php

namespace App\Enums;

enum PermissionsEnum: string
{
    case READ = 'read';
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case MANAGE = 'manage';

    // extra helper to allow for greater customization of displayed values, without disclosing the name/value data directly
    public function label(): string
    {
        return match ($this) {
            static::READ => 'просмотр',
            static::CREATE => 'создание',
            static::UPDATE => 'обновление',
            static::DELETE => 'удаление',
            static::MANAGE => 'управление',
        };
    }

}
