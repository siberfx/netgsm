<?php

namespace Siberfx\NetGsm;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Notifications\Notification;
use InvalidArgumentException;
use Siberfx\NetGsm\Exceptions\AbstractNetGsmException;
use Siberfx\NetGsm\Sms\AbstractNetGsmMessage;

class NetGsmChannel
{
    public function __construct(protected NetGsm $netgsm) {}

    /**
     * Send the given notification.
     *
     * @throws AbstractNetGsmException
     * @throws GuzzleException
     */
    public function send(mixed $notifiable, Notification $notification): ?string
    {
        $message = $notification->toNetGsm($notifiable);

        if (! $message instanceof AbstractNetGsmMessage) {
            throw new InvalidArgumentException((string) trans('netgsm::errors.invalid_netgsm_message'));
        }

        if (! $message->getRecipients()) {
            $phone = $notifiable->routeNotificationFor('NetGsm', $notification);

            if (! $phone) {
                return null;
            }

            $message->setRecipients($phone);
        }

        return $this->netgsm->sendSms($message);
    }
}
