<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicAcumbamail\Http\Controllers;

use Lwekuiper\StatamicAcumbamail\Facades\Acumbamail;
use Statamic\Http\Controllers\Controller;

class GetMergeFieldsController extends Controller
{
    public function __invoke(int $list): array
    {
        if (! Acumbamail::isConfigured()) {
            return [];
        }

        $fields = Acumbamail::getMergeFields($list);

        return collect($fields)->map(fn ($type, $label) => [
            'id' => $label,
            'label' => $label,
        ])->values()->all();
    }
}
