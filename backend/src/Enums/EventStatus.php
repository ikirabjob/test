<?php

declare(strict_types=1);

namespace App\Enums;

enum EventStatus: string
{
    case REGISTERED = 'registered';
    case ATTENDED = 'attended';
    case CANCELLED = 'cancelled';
}
