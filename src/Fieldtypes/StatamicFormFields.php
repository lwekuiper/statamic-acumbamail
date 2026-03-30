<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicAcumbamail\Fieldtypes;

use Statamic\Fields\Fieldtype;

class StatamicFormFields extends Fieldtype
{
    protected $component = 'statamic_form_fields';

    public function preload(): array
    {
        return [
            'form' => request()->route('form')?->handle(),
        ];
    }
}
