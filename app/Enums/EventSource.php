<?php

namespace App\Enums;

enum EventSource: string
{
    case Web = 'web';
    case Whatsapp = 'whatsapp';
}
