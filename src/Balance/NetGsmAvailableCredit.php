<?php

namespace Siberfx\NetGsm\Balance;

use GuzzleHttp\Exception\GuzzleException;
use Siberfx\NetGsm\Exceptions\AbstractNetGsmException;

class NetGsmAvailableCredit extends AbstractNetGsmBalance
{
    /**
     * Returns the remaining credit (TL) of the NetGsm account.
     *
     * @throws AbstractNetGsmException
     * @throws GuzzleException
     */
    public function getCredit(): ?string
    {
        $balance = $this->query(self::TYPE_CREDIT)['balance'] ?? null;

        if (is_array($balance)) {
            $balance = $balance[0]['amount'] ?? null;
        }

        return $balance === null ? null : (string) $balance;
    }
}
