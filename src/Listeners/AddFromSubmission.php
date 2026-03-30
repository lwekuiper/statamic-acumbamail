<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicAcumbamail\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Lwekuiper\StatamicAcumbamail\Data\FormConfig;
use Lwekuiper\StatamicAcumbamail\Facades\Acumbamail;
use Lwekuiper\StatamicAcumbamail\Facades\FormConfig as FormConfigFacade;
use Statamic\Events\SubmissionCreated;
use Statamic\Facades\Addon;
use Statamic\Facades\Site;
use Statamic\Forms\Submission;

class AddFromSubmission
{
    private Collection $data;

    private ?FormConfig $config;

    public function __construct()
    {
        $this->data = collect();
        $this->config = null;
    }

    public function getEmail(): string
    {
        return $this->data->get($this->config?->value('email_field') ?? 'email');
    }

    public function hasFormConfig(Submission $submission): bool
    {
        $edition = Addon::get('lwekuiper/statamic-acumbamail')->edition();

        $site = $edition === 'pro'
            ? Site::findByUrl(URL::previous()) ?? Site::default()
            : Site::default();

        $resolved = FormConfigFacade::findResolved($submission->form()->handle(), $site->handle());

        if (! $resolved) {
            return false;
        }

        if ($resolved->values()->isEmpty()) {
            return false;
        }

        $this->data = collect($submission->data());
        $this->config = $resolved;

        return true;
    }

    public function hasConsent(): bool
    {
        if (! $field = $this->config?->value('consent_field')) {
            return true;
        }

        return filter_var(
            Arr::get(Arr::wrap($this->data->get($field, false)), 0, false),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function handle(SubmissionCreated $event): void
    {
        if (! $this->hasFormConfig($event->submission)) {
            return;
        }

        if (! $this->hasConsent()) {
            return;
        }

        $listIds = $this->config->value('list_ids') ?? [];

        if (empty($listIds)) {
            return;
        }

        $edition = Addon::get('lwekuiper/statamic-acumbamail')->edition();

        $mergeFields = $edition !== 'free'
            ? $this->getMergeData()
            : ['email' => $this->getEmail()];
        $doubleOptin = (bool) ($this->config->value('double_optin') ?? false);

        foreach ($listIds as $listId) {
            Acumbamail::addSubscriber(
                (int) $listId,
                $mergeFields,
                $doubleOptin,
            );
        }
    }

    private function getMergeData(): array
    {
        $email = $this->getEmail();
        $mergeFields = $this->config->value('merge_fields') ?? [];

        $mapped = collect($mergeFields)->mapWithKeys(function ($item) {
            $value = $this->data->get($item['statamic_field']);

            if (is_array($value)) {
                $value = implode(', ', array_filter($value));
            }

            return [$item['acumbamail_field'] => (string) $value];
        })->filter(fn ($value) => $value !== '')->all();

        return array_merge(['email' => $email], $mapped);
    }
}
