<?php

use Siberfx\NetGsm\NetGsmChannel;
use Siberfx\NetGsm\Tests\Notification\TestNotifiable;
use Siberfx\NetGsm\Tests\Notification\TestNotification;
use Siberfx\NetGsm\Tests\Notification\TestStringNotification;

it('sends the notification to the routed numbers', function () {
    $channel = new NetGsmChannel($this->netGsm([['code' => '00', 'jobid' => '77']]));

    $jobId = $channel->send(new TestNotifiable, new TestNotification);

    expect($jobId)->toBe('77');
    expect($this->requestBody())->toBe([
        'msgheader' => 'COMPANY',
        'messages' => [
            ['msg' => 'Message content', 'no' => '5051234567'],
            ['msg' => 'Message content', 'no' => '5441234568'],
        ],
    ]);
});

it('rejects invalid messages', function () {
    $channel = new NetGsmChannel($this->netGsm([]));

    expect(fn () => $channel->send(new TestNotifiable, new TestStringNotification))
        ->toThrow(InvalidArgumentException::class, 'invalid netgsm message');
});
