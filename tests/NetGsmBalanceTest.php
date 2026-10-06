<?php

namespace Siberfx\NetGsm\Tests;

use PHPUnit\Framework\Attributes\Test;
use Siberfx\NetGsm\Exceptions\NetGsmException;

class NetGsmBalanceTest extends TestCase
{
    #[Test]
    public function it_returns_the_remaining_credit(): void
    {
        $netgsm = $this->netGsm([['code' => '00', 'balance' => '2,7']]);

        $this->assertSame('2,7', $netgsm->getCredit());
        $this->assertSame('/balance', $this->request()->getUri()->getPath());
        $this->assertSame(['usercode' => 'user', 'password' => 'secret', 'stip' => 2], $this->requestBody());
    }

    #[Test]
    public function it_returns_the_available_packages(): void
    {
        $netgsm = $this->netGsm([[
            'balance' => [
                ['amount' => 1000, 'balance_name' => 'Adet Flash Sms'],
                ['amount' => '953', 'balance_name' => 'Adet OTP Sms'],
            ],
        ]]);

        $this->assertSame([
            ['amount' => 1000, 'name' => 'Adet Flash Sms'],
            ['amount' => 953, 'name' => 'Adet OTP Sms'],
        ], $netgsm->getAvailablePackages()->all());
        $this->assertSame(1, $this->requestBody()['stip']);
    }

    #[Test]
    public function it_throws_on_balance_errors(): void
    {
        $netgsm = $this->netGsm([['code' => '30', 'description' => 'invalid credentials']]);

        $this->expectException(NetGsmException::class);
        $this->expectExceptionMessage('Invalid username and password or user does not have API access');

        $netgsm->getCredit();
    }
}
