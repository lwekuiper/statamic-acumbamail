<?php

namespace Lwekuiper\StatamicAcumbamail\Tests\Feature;

use Lwekuiper\StatamicAcumbamail\Facades\FormConfig;
use Lwekuiper\StatamicAcumbamail\Tests\FakesRoles;
use Lwekuiper\StatamicAcumbamail\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class UpdateFormConfigTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'view acumbamail']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $this->actingAs($user)
            ->patchJson(cp_route('acumbamail.form-config.update', $form->handle()), [
                'email_field' => 'email',
                'list_ids' => [1],
            ])
            ->assertForbidden();
    }

    #[Test]
    public function it_updates_a_form_config()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit acumbamail']]);
        $user = tap(User::make()->assignRole('test')->makeSuper())->save();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listIds([1])->consentField('consent');
        $formConfig->save();

        $this
            ->from('/here')
            ->actingAs($user)
            ->patchJson($formConfig->updateUrl(), [
                'email_field' => 'email',
                'list_ids' => [2],
                'consent_field' => 'consent',
                'double_optin' => true,
            ])
            ->assertSuccessful();

        $this->assertCount(1, FormConfig::all());
        $formConfig = FormConfig::find('test_form', 'default');
        $this->assertEquals([2], $formConfig->listIds());
    }
}
