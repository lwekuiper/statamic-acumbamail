<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicAcumbamail\Http\Controllers;

use Illuminate\Http\Request;
use Lwekuiper\StatamicAcumbamail\Facades\AddonConfig;
use Lwekuiper\StatamicAcumbamail\Facades\FormConfig;
use Statamic\Facades\Addon;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form as FormFacade;
use Statamic\Facades\Site;
use Statamic\Fields\Blueprint as BlueprintContract;
use Statamic\Forms\Form;
use Statamic\Http\Controllers\CP\CpController;

class FormConfigController extends CpController
{
    public function index(Request $request)
    {
        $this->authorize('view acumbamail');

        [$site, $edition] = $this->getAddonContext($request);

        $urlParams = $edition === 'pro' ? ['site' => $site] : [];

        $forms = FormFacade::all();

        $formConfigs = $forms->map(function ($form) use ($urlParams, $site) {
            $localConfig = FormConfig::find($form->handle(), $site);
            $resolved = FormConfig::findResolved($form->handle(), $site);

            $resolvedValues = $resolved?->values() ?? collect();
            $resolvedListIds = $resolvedValues->get('list_ids', []);

            $hasLocalData = $localConfig !== null && ! $localConfig->data()->isEmpty();
            $hasValues = $resolvedValues->filter()->isNotEmpty();

            return [
                'title' => $form->title(),
                'edit_url' => cp_route('acumbamail.form-config.edit', ['form' => $form->handle(), ...$urlParams]),
                'lists' => count($resolvedListIds),
                'delete_url' => $hasLocalData ? cp_route('acumbamail.form-config.destroy', ['form' => $form->handle(), ...$urlParams]) : null,
                'status' => $hasValues ? 'published' : 'draft',
            ];
        })->values();

        $viewData = [
            'formConfigs' => $formConfigs,
        ];

        if ($edition === 'pro') {
            $viewData = array_merge($viewData, [
                'locale' => $site,
                'localizations' => $this->getEnabledSites()->map(fn ($localization) => [
                    'handle' => $localization->handle(),
                    'name' => $localization->name(),
                    'active' => $localization->handle() === $site,
                    'url' => cp_route('acumbamail.index', ['site' => $localization->handle()]),
                ])->values()->all(),
            ]);
        }

        if ($request->wantsJson()) {
            return $viewData;
        }

        $configureUrl = $this->isMultiSite() ? cp_route('acumbamail.edit') : null;

        return view('statamic-acumbamail::index', array_merge($viewData, [
            'configureUrl' => $configureUrl,
        ]));
    }

    public function edit(Request $request, Form $form)
    {
        $this->authorize('view acumbamail');

        [$site, $edition] = $this->getAddonContext($request);

        $blueprint = $this->getBlueprint();
        $formConfig = FormConfig::find($form->handle(), $site);

        $hasOrigin = $edition === 'pro' && $formConfig && $formConfig->hasOrigin();

        if ($hasOrigin) {
            $originValues = $formConfig->origin()->values()->all();
            $displayValues = $formConfig->values()->all();

            $fields = $blueprint->fields()->addValues($displayValues)->preProcess();

            [$originValues, $originMeta] = $this->extractFromFields($originValues, $blueprint);
            $localizedFields = $formConfig->data()->keys()->all();
        } else {
            $fields = $blueprint->fields();

            if ($formConfig) {
                $fields = $fields->addValues($formConfig->data()->all());
            }

            $fields = $fields->preProcess();
        }

        $viewData = [
            'title' => $form->title(),
            'action' => cp_route('acumbamail.form-config.update', ['form' => $form->handle(), 'site' => $site]),
            'deleteUrl' => $formConfig?->deleteUrl(),
            'listingUrl' => cp_route('acumbamail.index', ['site' => $site]),
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'hasOrigin' => $hasOrigin,
            'originValues' => $originValues ?? null,
            'originMeta' => $originMeta ?? null,
            'localizedFields' => $localizedFields ?? [],
        ];

        if ($edition === 'pro') {
            $viewData = array_merge($viewData, [
                'locale' => $site,
                'localizations' => $this->getEnabledSites()->map(fn ($localization) => [
                    'handle' => $localization->handle(),
                    'name' => $localization->name(),
                    'active' => $localization->handle() === $site,
                    'origin' => ! AddonConfig::hasOrigin($localization->handle()),
                    'url' => cp_route('acumbamail.form-config.edit', ['form' => $form->handle(), 'site' => $localization->handle()]),
                ])->values()->all(),
            ]);
        }

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic-acumbamail::edit', $viewData);
    }

    public function update(Request $request, Form $form)
    {
        $this->authorize('edit acumbamail');

        [$site, $edition] = $this->getAddonContext($request);

        $blueprint = $this->getBlueprint();
        $fields = $blueprint->fields()->addValues($request->all());
        $fields->validate();

        $values = $fields->process()->values();

        $hasOrigin = $edition === 'pro' && AddonConfig::hasOrigin($site);

        if ($hasOrigin) {
            $values = $values->only($request->input('_localized', []));
        }

        $values = $values->all();

        if (! $formConfig = FormConfig::find($form->handle(), $site)) {
            $formConfig = FormConfig::make()->form($form)->locale($site);
        }

        $formConfig->data($values);

        $formConfig->save();

        if ($edition === 'pro') {
            FormConfig::ensureLocalizationsExist($form->handle());
        }

        return response()->json(['message' => __('Configuration saved')]);
    }

    public function destroy(Request $request, Form $form)
    {
        $this->authorize('edit acumbamail');

        [$site] = $this->getAddonContext($request);

        if (! $formConfig = FormConfig::find($form->handle(), $site)) {
            return $this->pageNotFound();
        }

        if ($formConfig->hasOrigin()) {
            $formConfig->data(collect())->save();
        } else {
            $formConfig->delete();
        }

        return response('', 204);
    }

    private function extractFromFields(array $values, BlueprintContract $blueprint): array
    {
        $fields = $blueprint
            ->fields()
            ->addValues($values)
            ->preProcess();

        return [$fields->values()->all(), $fields->meta()->all()];
    }

    /**
     * Get the site and edition based on the request.
     */
    private function getAddonContext(Request $request): array
    {
        $edition = Addon::get('lwekuiper/statamic-acumbamail')->edition();

        $site = $edition === 'pro'
            ? $request->site ?? Site::selected()->handle()
            : Site::default()->handle();

        return [$site, $edition];
    }

    /**
     * Get sites where Acumbamail is enabled.
     */
    private function getEnabledSites(): \Illuminate\Support\Collection
    {
        return Site::all()->filter(fn ($site) => AddonConfig::isEnabled($site->handle()));
    }

    private function isMultiSite(): bool
    {
        return Site::multiEnabled()
            && Addon::get('lwekuiper/statamic-acumbamail')->edition() === 'pro';
    }

    /**
     * Get the blueprint.
     */
    private function getBlueprint(): BlueprintContract
    {
        $edition = Addon::get('lwekuiper/statamic-acumbamail')->edition();

        $sections = [
            [
                'display' => 'Subscriber',
                'fields' => [
                    [
                        'handle' => 'email_field',
                        'field' => [
                            'display' => 'Email Field',
                            'instructions' => 'The form field that contains the email of the subscriber.',
                            'type' => 'statamic_form_fields',
                            'validate' => 'required',
                            'localizable' => true,
                            'width' => 50,
                        ],
                    ],
                    [
                        'handle' => 'consent_field',
                        'field' => [
                            'display' => 'Consent Field',
                            'instructions' => 'The form field that contains the consent of the subscriber.',
                            'type' => 'statamic_form_fields',
                            'localizable' => true,
                            'width' => 50,
                        ],
                    ],
                ],
            ],
            [
                'display' => 'Lists',
                'fields' => [
                    [
                        'handle' => 'list_ids',
                        'field' => [
                            'display' => 'Lists',
                            'instructions' => 'The Acumbamail lists subscribers are added to.',
                            'type' => 'acumbamail_list',
                            'validate' => 'required',
                            'localizable' => true,
                            'width' => 50,
                        ],
                    ],
                    [
                        'handle' => 'double_optin',
                        'field' => [
                            'display' => 'Double Opt-in',
                            'instructions' => 'Send a confirmation email before subscribing.',
                            'type' => 'toggle',
                            'default' => false,
                            'localizable' => true,
                            'width' => 50,
                        ],
                    ],
                ],
            ],
        ];

        if ($edition !== 'free') {
            $sections[] = [
                'display' => 'Field Mapping',
                'fields' => [
                    [
                        'handle' => 'merge_fields',
                        'field' => [
                            'display' => 'Merge Fields',
                            'instructions' => 'Add the form fields you want to map to Acumbamail fields.',
                            'type' => 'grid',
                            'mode' => 'table',
                            'listable' => 'hidden',
                            'fullscreen' => false,
                            'localizable' => true,
                            'width' => 100,
                            'add_row' => 'Add Merge Field',
                            'fields' => [
                                [
                                    'handle' => 'statamic_field',
                                    'field' => [
                                        'display' => 'Form Field',
                                        'type' => 'statamic_form_fields',
                                        'validate' => 'required',
                                    ],
                                ],
                                [
                                    'handle' => 'acumbamail_field',
                                    'field' => [
                                        'display' => 'Merge Field',
                                        'type' => 'acumbamail_merge_fields',
                                        'validate' => 'required',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }

        return Blueprint::make()->setContents([
            'tabs' => [
                'general' => [
                    'display' => 'General',
                    'sections' => $sections,
                ],
            ],
        ]);
    }
}
