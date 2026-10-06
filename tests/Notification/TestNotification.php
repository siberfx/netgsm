<?php

namespace Siberfx\NetGsm\Tests\Notification;

use Illuminate\Notifications\Notification;
use Siberfx\NetGsm\Sms\NetGsmSmsMessage;

class TestNotification extends Notification
{
    public function toNetGsm(mixed $notifiable): NetGsmSmsMessage
    {
        return (new NetGsmSmsMessage('Message content'))->setHeader('COMPANY');
    }
}
