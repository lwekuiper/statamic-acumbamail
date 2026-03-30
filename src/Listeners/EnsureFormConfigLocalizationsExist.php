<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicAcumbamail\Listeners;

use Lwekuiper\StatamicAcumbamail\Facades\FormConfig;
use Statamic\Events\FormSaved;
use Statamic\Facades\Addon;

class EnsureFormConfigLocalizationsExist
{
    public function handle(FormSaved $event): void
    {
        if (Addon::get('lwekuiper/statamic-acumbamail')->edition() !== 'pro') {
            return;
        }

        FormConfig::ensureLocalizationsExist($event->form->handle());
    }
}
