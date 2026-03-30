<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicAcumbamail\Fieldtypes;

use Statamic\Fields\Fieldtype;

class AcumbamailMergeFields extends Fieldtype
{
    protected $component = 'acumbamail_merge_fields';

    public static function handle()
    {
        return 'acumbamail_merge_fields';
    }
}
