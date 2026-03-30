<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicAcumbamail\Fieldtypes;

use Lwekuiper\StatamicAcumbamail\Facades\Acumbamail;
use Statamic\Fieldtypes\Relationship;

class AcumbamailList extends Relationship
{
    public static function handle()
    {
        return 'acumbamail_list';
    }

    public function getIndexItems($request)
    {
        if (! Acumbamail::isConfigured()) {
            abort(403, __('Acumbamail API credentials are not configured.'));
        }

        $lists = Acumbamail::getLists();

        return collect($lists)->map(fn ($list, $id) => [
            'id' => (string) $id,
            'title' => $list['name'] ?? $list['titulo'] ?? "List {$id}",
        ])->values()->toArray();
    }

    protected function toItemArray($id)
    {
        if (! $id) {
            return [];
        }

        $list = Acumbamail::getList((int) $id);

        if (! $list) {
            return [];
        }

        return [
            'id' => (string) $id,
            'title' => $list['name'] ?? $list['titulo'] ?? "List {$id}",
        ];
    }
}
