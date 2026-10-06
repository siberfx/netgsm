<?php

namespace Siberfx\NetGsm\Iys;

use Siberfx\NetGsm\Iys\Requests\Add;
use Siberfx\NetGsm\Iys\Requests\Search;

/**
 * @see https://www.netgsm.com.tr/dokuman/#i%CC%87ys
 */
class NetGsmIys extends AbstractNetGsmIys
{
    /**
     * Queues an address to be added to IYS. Call it multiple times for bulk inserts.
     */
    public function addAddress(Add $request): static
    {
        $this->url = $request->getUrl();
        $this->refId = $request->getRefId() ?? $this->refId;
        $this->body[] = $request->body();

        return $this;
    }

    public function searchAddress(Search $request): static
    {
        $this->url = $request->getUrl();
        $this->refId = $request->getRefId();
        $this->body = [$request->body()];

        return $this;
    }
}
