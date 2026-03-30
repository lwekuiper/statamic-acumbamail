<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicAcumbamail\Facades;

use Illuminate\Support\Facades\Facade;
use Lwekuiper\StatamicAcumbamail\Connectors\AcumbamailConnector;

class Acumbamail extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AcumbamailConnector::class;
    }
}
