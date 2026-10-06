<?php

namespace Siberfx\NetGsm\Exceptions;

class InvalidConfiguration extends AbstractNetGsmException
{
    public static function configurationNotSet(): static
    {
        return new static('In order to send notification via netgsm you need to add credentials in the `credentials` key of `config.netgsm`.');
    }
}
