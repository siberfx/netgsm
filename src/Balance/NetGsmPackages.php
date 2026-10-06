<?php

namespace Siberfx\NetGsm\Balance;

use GuzzleHttp\Exception\GuzzleException;
use Siberfx\NetGsm\Exceptions\AbstractNetGsmException;

class NetGsmPackages extends AbstractNetGsmBalance
{
    /**
     * Returns the packages and their remaining amounts on the NetGsm account.
     *
     * @return array<int, array{amount: int, name: string|null}>
     *
     * @throws AbstractNetGsmException
     * @throws GuzzleException
     */
    public function getPackages(): array
    {
        $balance = $this->query(self::TYPE_PACKAGE)['balance'] ?? [];

        if (! is_array($balance)) {
            return [];
        }

        return array_map(fn (array $package) => [
            'amount' => (int) ($package['amount'] ?? 0),
            'name' => $package['balance_name'] ?? null,
        ], array_values($balance));
    }
}
