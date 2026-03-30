<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicAcumbamail\Facades;

use Illuminate\Support\Facades\Facade;
use Lwekuiper\StatamicAcumbamail\Data\AddonConfig as AddonConfigData;

class AddonConfig extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AddonConfigData::class;
    }
}
