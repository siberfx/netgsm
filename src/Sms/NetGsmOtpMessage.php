<?php

namespace Siberfx\NetGsm\Sms;

use Siberfx\NetGsm\NetGsmErrors;

/**
 * @see https://www.netgsm.com.tr/dokuman/#otp-sms
 */
class NetGsmOtpMessage extends AbstractNetGsmMessage
{
    protected string $url = 'sms/rest/v2/otp';

    protected array $errorCodes = [
        '20' => NetGsmErrors::MESSAGE_TOO_LONG,
        '30' => NetGsmErrors::CREDENTIALS_INCORRECT,
        '40' => NetGsmErrors::SENDER_INCORRECT,
        '41' => NetGsmErrors::SENDER_INCORRECT,
        '50' => NetGsmErrors::RECEIVER_INCORRECT,
        '51' => NetGsmErrors::RECEIVER_INCORRECT,
        '52' => NetGsmErrors::RECEIVER_INCORRECT,
        '60' => NetGsmErrors::OTP_ACCOUNT_NOT_DEFINED,
        '70' => NetGsmErrors::PARAMETERS_INCORRECT,
        '100' => NetGsmErrors::SYSTEM_ERROR,
        '101' => NetGsmErrors::SYSTEM_ERROR,
    ];

    /**
     * OTP messages are delivered to a single recipient.
     */
    public function body(): array
    {
        return [
            'msgheader' => $this->getHeader(),
            'msg' => (string) $this->message,
            'no' => $this->recipients[0] ?? null,
        ];
    }
}
