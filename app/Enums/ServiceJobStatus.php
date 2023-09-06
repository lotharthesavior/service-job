<?php

namespace App\Enums;

enum ServiceJobStatus: string
{
    case DISPATCHED = 'dispatched';
    case COMPLETED = 'completed';
    case PAID = 'paid';

    public static function getOptions(): array
    {
        return [
            self::DISPATCHED->value => 'Dispatched',
            self::COMPLETED->value => 'Completed',
            self::PAID->value => 'Paid',
        ];
    }
}
