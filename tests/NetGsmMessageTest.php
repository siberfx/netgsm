<?php

use Illuminate\Support\Carbon;
use Siberfx\NetGsm\Sms\NetGsmOtpMessage;
use Siberfx\NetGsm\Sms\NetGsmSmsMessage;

it('can be created with a message', function () {
    expect(NetGsmSmsMessage::create('Hello')->getMessage())->toBe('Hello');
    expect((new NetGsmSmsMessage)->setMessage('Hello')->getMessage())->toBe('Hello');
});

it('uses the header from defaults unless set', function () {
    $message = new NetGsmSmsMessage('Hello', ['header' => 'DEFAULT']);

    expect($message->getHeader())->toBe('DEFAULT');
    expect($message->setHeader('COMPANY')->getHeader())->toBe('COMPANY');
});

it('normalizes recipients', function () {
    expect((new NetGsmSmsMessage)->setRecipients('5051234567, 5441234568')->getRecipients())
        ->toBe(['5051234567', '5441234568']);
    expect((new NetGsmSmsMessage)->setRecipients([5051234567, '5441234568'])->getRecipients())
        ->toBe(['5051234567', '5441234568']);
    expect((new NetGsmSmsMessage)->setRecipients(5051234567)->getRecipients())
        ->toBe(['5051234567']);
});

it('builds the rest v2 sms payload', function () {
    $message = (new NetGsmSmsMessage('Merhaba'))
        ->setHeader('COMPANY')
        ->setRecipients(['5051234567', '5441234568'])
        ->setStartDate(Carbon::create(2026, 10, 6, 14, 30))
        ->setEndDate(Carbon::create(2026, 10, 7, 9, 5))
        ->setEncoding('TR')
        ->setIysFilter(NetGsmSmsMessage::IYS_FILTER_COMMERCIAL_INDIVIDUAL)
        ->setPartnerCode('PARTNER');

    expect($message->body())->toBe([
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
    ]);
});

it('omits empty optional fields', function () {
    $message = (new NetGsmSmsMessage('Hi'))->setHeader('COMPANY')->setRecipients('5051234567');

    expect($message->body())->toBe([
        'msgheader' => 'COMPANY',
        'messages' => [['msg' => 'Hi', 'no' => '5051234567']],
    ]);
});

it('keeps informational iys filter zero', function () {
    $message = (new NetGsmSmsMessage('Hi'))->setRecipients('5051234567')->setIysFilter(0);

    expect($message->body()['iysfilter'])->toBe('0');
});

it('sends a different text to each recipient', function () {
    $message = (new NetGsmSmsMessage('Default text'))
        ->setRecipients('5051234567')
        ->addMessage('5441234568', 'Hello Ayşe')
        ->addMessage(5321234567, 'Hello Mehmet');

    expect($message->getRecipients())->toBe(['5051234567', '5441234568', '5321234567']);
    expect($message->body()['messages'])->toBe([
        ['msg' => 'Default text', 'no' => '5051234567'],
        ['msg' => 'Hello Ayşe', 'no' => '5441234568'],
        ['msg' => 'Hello Mehmet', 'no' => '5321234567'],
    ]);
});

it('overrides the text when adding a message for an existing recipient', function () {
    $message = (new NetGsmSmsMessage('Default text'))
        ->setRecipients(['5051234567', '5441234568'])
        ->addMessage('5051234567', 'Personal text');

    expect($message->getRecipients())->toBe(['5051234567', '5441234568']);
    expect($message->getMessages())->toBe([
        '5051234567' => 'Personal text',
        '5441234568' => 'Default text',
    ]);
});

it('builds the otp payload for a single recipient', function () {
    $message = (new NetGsmOtpMessage('Code: 1234'))
        ->setHeader('COMPANY')
        ->setRecipients(['5051234567', '5441234568']);

    expect($message->getUrl())->toBe('sms/rest/v2/otp');
    expect($message->body())->toBe([
        'msgheader' => 'COMPANY',
        'msg' => 'Code: 1234',
        'no' => '5051234567',
    ]);
});
