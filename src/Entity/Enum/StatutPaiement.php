<?php

namespace App\Entity\Enum;

enum StatutPaiement: string
{
    case UNPAID = 'Unpaid';
    case PAID = 'Paid';
}
