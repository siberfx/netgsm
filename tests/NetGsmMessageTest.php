<?php

namespace Siberfx\NetGsm\Tests;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Siberfx\NetGsm\Sms\NetGsmOtpMessage;
use Siberfx\NetGsm\Sms\NetGsmSmsMessage;

class NetGsmMessageTest extends TestCase
{
    #[Test]
    public function it_can_be_created_with_a_message(): void
    {
        $this->assertSame('Hello', NetGsmSmsMessage::create('Hello')->getMessage());
        $this->assertSame('Hello', (new NetGsmSmsMessage)->setMessage('Hello')->getMessage());
    }

    #[Test]
    public function it_uses_the_header_from_defaults_unless_set(): void
    {
        $message = new NetGsmSmsMessage('Hello', ['header' => 'DEFAULT']);

        $this->assertSame('DEFAULT', $message->getHeader());
        $this->assertSame('COMPANY', $message->setHeader('COMPANY')->getHeader());
    }

    #[Test]
    public function it_normalizes_recipients(): void
    {
        $this->assertSame(['5051234567', '5441234568'], (new NetGsmSmsMessage)->setRecipients('5051234567, 5441234568')->getRecipients());
        $this->assertSame(['5051234567', '5441234568'], (new NetGsmSmsMessage)->setRecipients([5051234567, '5441234568'])->getRecipients());
        $this->assertSame(['5051234567'], (new NetGsmSmsMessage)->setRecipients(5051234567)->getRecipients());
    }

    #[Test]
    public function it_builds_the_rest_v2_sms_payload(): void
    {
        $message = (new NetGsmSmsMessage('Merhaba'))
            ->setHeader('COMPANY')
            ->setRecipients(['5051234567', '5441234568'])
            ->setStartDate(Carbon::create(2026, 10, 6, 14, 30))
            ->setEndDate(Carbon::create(2026, 10, 7, 9, 5))
            ->setEncoding('TR')
            ->setIysFilter(NetGsmSmsMessage::IYS_FILTER_COMMERCIAL_INDIVIDUAL)
            ->setPartnerCode('PARTNER');

        $this->assertSame([
            'msgheader' => 'COMPANY',
            'encoding' => 'TR',
            'iysfilter' => '11',
            'partnercode' => 'PARTNER',
            'startdate' => '061020261430',
            'stopdate' => '071020260905',
            'messages' => [
                ['msg' => 'Merhaba', 'no' => '5051234567'],
                ['msg' => 'Merhaba', 'no' => '5441234568'],
            ],
        ], $message->body());
    }

    #[Test]
    public function it_omits_empty_optional_fields(): void
    {
        $message = (new NetGsmSmsMessage('Hi'))->setHeader('COMPANY')->setRecipients('5051234567');

        $this->assertSame([
            'msgheader' => 'COMPANY',
            'messages' => [['msg' => 'Hi', 'no' => '5051234567']],
        ], $message->body());
    }

    #[Test]
    public function it_keeps_informational_iys_filter_zero(): void
    {
        $message = (new NetGsmSmsMessage('Hi'))->setRecipients('5051234567')->setIysFilter(0);

        $this->assertSame('0', $message->body()['iysfilter']);
    }

    #[Test]
    public function it_builds_the_otp_payload_for_a_single_recipient(): void
    {
        $message = (new NetGsmOtpMessage('Code: 1234'))
            ->setHeader('COMPANY')
            ->setRecipients(['5051234567', '5441234568']);

        $this->assertSame('sms/rest/v2/otp', $message->getUrl());
        $this->assertSame([
            'msgheader' => 'COMPANY',
            'msg' => 'Code: 1234',
            'no' => '5051234567',
        ], $message->body());
    }
}
