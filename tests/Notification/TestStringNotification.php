<?php

namespace Siberfx\NetGsm\Tests\Notification;

use Illuminate\Notifications\Notification;

class TestStringNotification extends Notification
{
    public function toNetGsm(mixed $notifiable): string
    {
        return 'Test by string';
    }
}
