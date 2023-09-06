<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case VISA = 'visa';
    case MASTERCARD = 'mastercard';
    case CASH = 'cash';
    case INVOICE = 'invoice';

    public static function getOptions(): array
    {
        return [
            self::VISA->value => 'Visa',
            self::MASTERCARD->value => 'Mastercard',
            self::CASH->value => 'Cash',
            self::INVOICE->value => 'Invoice',
        ];
    }
}
