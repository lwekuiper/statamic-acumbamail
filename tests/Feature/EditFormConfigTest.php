<?php

namespace Lwekuiper\StatamicAcumbamail\Tests\Feature;

use Lwekuiper\StatamicAcumbamail\Facades\FormConfig;
use Lwekuiper\StatamicAcumbamail\Tests\FakesRoles;
use Lwekuiper\StatamicAcumbamail\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class EditFormConfigTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $this->actingAs($user)
            ->getJson(cp_route('acumbamail.form-config.edit', $form->handle()))
            ->assertForbidden();
    }

    #[Test]
    public function it_shows_the_edit_form_config_page()
    {
        $this->setTestRoles(['test' => ['access cp', 'view acumbamail']]);
        $user = User::make()->assignRole('test')->save();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listIds([1])->consentField('consent')->doubleOptin(true);
        $formConfig->save();

        $this->actingAs($user)
            ->get($formConfig->editUrl())
            ->assertOk()
            ->assertViewHas('values', collect([
                'email_field' => 'email',
                'consent_field' => 'consent',
                'list_ids' => [1],
                'double_optin' => true,
                'merge_fields' => [],
            ]));
    }
}
