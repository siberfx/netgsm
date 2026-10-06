<?php

namespace Siberfx\NetGsm\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Siberfx\NetGsm\NetGsmChannel;
use Siberfx\NetGsm\Tests\Notification\TestNotifiable;
use Siberfx\NetGsm\Tests\Notification\TestNotification;
use Siberfx\NetGsm\Tests\Notification\TestStringNotification;

class NetGsmChannelTest extends TestCase
{
    #[Test]
    public function it_sends_the_notification_to_the_routed_numbers(): void
    {
        $channel = new NetGsmChannel($this->netGsm([['code' => '00', 'jobid' => '77']]));

        $jobId = $channel->send(new TestNotifiable, new TestNotification);

        $this->assertSame('77', $jobId);
        $this->assertSame([
            'msgheader' => 'COMPANY',
            'messages' => [
                ['msg' => 'Message content', 'no' => '5051234567'],
                ['msg' => 'Message content', 'no' => '5441234568'],
            ],
        ], $this->requestBody());
    }

    #[Test]
    public function it_rejects_invalid_messages(): void
    {
        $channel = new NetGsmChannel($this->netGsm([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid netgsm message');

        $channel->send(new TestNotifiable, new TestStringNotification);
    }
}
