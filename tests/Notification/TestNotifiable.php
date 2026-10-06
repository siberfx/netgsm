<?php

namespace Siberfx\NetGsm\Tests\Notification;

use Illuminate\Notifications\Notifiable;

class TestNotifiable
{
    use Notifiable;

    public function routeNotificationForNetGsm(): string
    {
        return '5051234567, 5441234568';
    }
}
