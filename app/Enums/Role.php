<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Finance = 'finance';
    case Secretary = 'secretary';
    case Advisor = 'advisor';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Finance => 'Treasurer',
            self::Secretary => 'Secretary',
            self::Advisor => 'Advisor',
            self::Member => 'Member',
        };
    }
}
