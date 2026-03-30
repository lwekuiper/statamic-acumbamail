<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicAcumbamail\Facades;

use Illuminate\Support\Facades\Facade;
use Lwekuiper\StatamicAcumbamail\Stache\FormConfigRepository;

class FormConfig extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FormConfigRepository::class;
    }
}
